<?php

namespace App\Http\Controllers\Api;

use App\Models\Section;
use App\Models\Setting;
use Illuminate\Http\Request;

class PublicContentController extends BaseController
{
    /**
     * Public settings that are safe to expose to unauthenticated clients.
     * Only keys listed here are returned via /api/public/* endpoints.
     */
    protected array $publicSettingKeys = [
        'site_title',
        'site_tagline',
        'meta_description',
        'primary_color',
        'footer_about',
        'footer_contact_email',
        'footer_contact_phone',
        'footer_contact_address',
    ];

    protected function publicSettings(): array
    {
        return Setting::whereIn('key', $this->publicSettingKeys)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Build the public navigation menu from sections that have opted in
     * via show_in_nav. Each item contains the anchor (matching the type-based
     * id used by FrontendRenderer) and the display label.
     *
     * @param  \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection  $sections
     */
    protected function buildNav($sections): array
    {
        $seen = [];
        $items = [];
        foreach ($sections as $section) {
            $type = $section->type;
            $seen[$type] = ($seen[$type] ?? 0) + 1;
            $anchor = $seen[$type] === 1 ? $type : "{$type}-{$seen[$type]}";

            if (!$section->show_in_nav) continue;

            $items[] = [
                'anchor' => $anchor,
                'label' => $section->nav_label ?: $section->label,
            ];
        }
        return $items;
    }

    public function live()
    {
        // Fetch enabled sections with status 'live'
        $sections = Section::where('enabled', true)
            ->where('status', 'live')
            ->whereNotNull('live_published_content')
            ->orderBy('order', 'asc')
            ->get(['id', 'type', 'label', 'show_in_nav', 'nav_label', 'live_published_content']);

        // Build nav before stripping the metadata fields off the sections.
        $nav = $this->buildNav($sections);

        // Normalize property name for frontend renderer
        $sections->transform(function ($section) {
            $section->content = $section->live_published_content;
            unset($section->live_published_content, $section->show_in_nav, $section->nav_label);
            return $section;
        });

        return $this->successResponse([
            'sections' => $sections,
            'settings' => $this->publicSettings(),
            'nav' => $nav,
        ], 'Live content fetched.');
    }

    public function preview(Request $request)
    {
        // Simple preview token authentication. Fail closed if the token is
        // not configured so we never accept an empty or default value.
        $expected = config('app.preview_token');
        $provided = $request->query('token');

        if (empty($expected) || !is_string($provided) || !hash_equals($expected, $provided)) {
            return $this->errorResponse('Invalid or missing preview token.', 403);
        }

        // Fetch all enabled sections
        $sections = Section::where('enabled', true)
            ->orderBy('order', 'asc')
            ->get(['id', 'type', 'label', 'show_in_nav', 'nav_label', 'dark_preview_content', 'live_published_content']);

        // Build nav before stripping the metadata fields off the sections.
        $nav = $this->buildNav($sections);

        // Use dark_preview_content if available, fallback to live
        $sections->transform(function ($section) {
            $section->content = $section->dark_preview_content ?? $section->live_published_content;
            unset($section->dark_preview_content, $section->live_published_content, $section->show_in_nav, $section->nav_label);
            return $section;
        });

        return $this->successResponse([
            'sections' => $sections,
            'settings' => $this->publicSettings(),
            'nav' => $nav,
        ], 'Preview content fetched.')
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    /**
     * Standalone public settings endpoint. Used by the admin SPA login
     * screen (no auth) and the admin layout brand before the user is loaded.
     */
    public function settings()
    {
        return $this->successResponse($this->publicSettings(), 'Settings fetched.');
    }
}
