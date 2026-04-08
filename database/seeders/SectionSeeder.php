<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds a curated demo homepage. Each section is written to draft, dark and
 * live so the public site (/) immediately renders the full demo after a fresh
 * install, without requiring an admin to click Publish.
 *
 * Safe to re-run: only seeds when the sections table is empty so existing
 * content is never overwritten.
 */
class SectionSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('sections')->count() > 0) {
            $this->command?->info('SectionSeeder skipped — sections table already has rows.');
            return;
        }

        $now = Carbon::now();

        $sections = [
            // 1. Promo banner at the very top
            [
                'type' => 'promo_banner',
                'label' => 'Top promo banner',
                'show_in_nav' => false,
                'nav_label' => null,
                'content' => [
                    'message' => '🎉  Spring sale is live — save 30% on annual plans.',
                    'link' => '#cta',
                    'link_label' => 'Claim offer',
                    'background_color' => '#4f46e5',
                    'dismissible' => true,
                ],
            ],

            // 2. Hero
            [
                'type' => 'hero',
                'label' => 'Main hero',
                'show_in_nav' => false,
                'nav_label' => null,
                'content' => [
                    'headline' => 'Build a website that scales with your team',
                    'text' => 'A modern page builder for content teams who want to ship faster without waiting on developers.',
                    'cta_text' => 'Start free trial',
                    'cta_link' => '#cta',
                    'bg_image' => [
                        'url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1600&q=80',
                        'alt_text' => 'Modern office workspace',
                    ],
                ],
            ],

            // 3. Feature grid
            [
                'type' => 'feature_grid',
                'label' => 'Key features',
                'show_in_nav' => true,
                'nav_label' => 'Features',
                'content' => [
                    'eyebrow' => 'Why teams choose us',
                    'headline' => 'Everything you need to launch fast',
                    'subheadline' => 'A fully featured admin and page builder, ready out of the box.',
                    'items' => [
                        [
                            'title' => 'Drag & drop builder',
                            'description' => 'Reorder sections visually. No code, no developer time.',
                            'icon' => null,
                        ],
                        [
                            'title' => 'Inline editing',
                            'description' => 'Click any text or image on the rendered page to edit it.',
                            'icon' => null,
                        ],
                        [
                            'title' => 'Dark / Live publishing',
                            'description' => 'Preview every change in a private dark environment before going live.',
                            'icon' => null,
                        ],
                    ],
                ],
            ],

            // 4. Image + text
            [
                'type' => 'image_text',
                'label' => 'About us',
                'show_in_nav' => true,
                'nav_label' => 'About',
                'content' => [
                    'headline' => 'Built by content people, for content people',
                    'text' => 'We were tired of waiting on engineers for every typo fix. So we built the CMS we always wanted: powerful enough for marketing, simple enough that anyone can use it.',
                    'body' => '<p>The page builder ships with 14 section types out of the box, plus a versioned draft → dark → live workflow that keeps you safe. No more "deploy to fix a typo" emails at 2am.</p>',
                    'image_position' => 'right',
                    'image' => [
                        'url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80',
                        'alt_text' => 'Team collaborating in a meeting',
                    ],
                ],
            ],

            // 5. Stats counter
            [
                'type' => 'stats',
                'label' => 'Trust stats',
                'show_in_nav' => false,
                'nav_label' => null,
                'content' => [
                    'headline' => 'Trusted by teams who ship',
                    'subheadline' => 'Numbers that matter to your stakeholders.',
                    'items' => [
                        ['label' => 'Sites built',     'value' => 1200, 'prefix' => '', 'suffix' => '+'],
                        ['label' => 'Active editors', 'value' => 4500, 'prefix' => '', 'suffix' => '+'],
                        ['label' => 'Uptime',          'value' => 99,   'prefix' => '', 'suffix' => '.9%'],
                        ['label' => 'Avg. publish time','value' => 12,  'prefix' => '', 'suffix' => 's'],
                    ],
                ],
            ],

            // 6. Testimonials
            [
                'type' => 'testimonials',
                'label' => 'What customers say',
                'show_in_nav' => true,
                'nav_label' => 'Testimonials',
                'content' => [
                    'headline' => 'Loved by content teams everywhere',
                    'subheadline' => 'Here\'s what our customers have to say.',
                    'items' => [
                        [
                            'quote' => 'We replaced three different tools with this one. Our marketing team ships landing pages without ever opening a Jira ticket.',
                            'name' => 'Sarah Chen',
                            'role' => 'Head of Marketing, Northwind Co.',
                            'avatar' => [
                                'url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=200&q=80',
                            ],
                        ],
                        [
                            'quote' => 'The dark preview workflow alone is worth the price. We catch problems before customers ever see them.',
                            'name' => 'Marcus Rivera',
                            'role' => 'Editor in Chief, The Daily Brief',
                            'avatar' => [
                                'url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80',
                            ],
                        ],
                        [
                            'quote' => 'Dropped our time-to-publish from 2 days to 5 minutes. Our editors finally feel empowered.',
                            'name' => 'Priya Sharma',
                            'role' => 'Director of Content, Lumen Labs',
                            'avatar' => [
                                'url' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=200&q=80',
                            ],
                        ],
                        [
                            'quote' => 'Onboarding new editors used to take a week of hand-holding. Now they\'re productive on day one. The inline editing just clicks.',
                            'name' => 'Diego Alvarez',
                            'role' => 'CMO, Anvil Retail',
                            'avatar' => [
                                'url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80',
                            ],
                        ],
                        [
                            'quote' => 'The audit log has saved us twice during compliance reviews. Knowing exactly who changed what, when, is priceless.',
                            'name' => 'Yuki Tanaka',
                            'role' => 'Product Lead, Kiku Studio',
                            'avatar' => [
                                'url' => 'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?auto=format&fit=crop&w=200&q=80',
                            ],
                        ],
                        [
                            'quote' => 'We schedule all our announcement posts in advance now. Publishing is finally predictable instead of a mad Friday afternoon scramble.',
                            'name' => 'Emma Schmidt',
                            'role' => 'Head of Digital, Nordic Press',
                            'avatar' => [
                                'url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=200&q=80',
                            ],
                        ],
                    ],
                ],
            ],

            // 7. CTA band
            [
                'type' => 'cta',
                'label' => 'Primary CTA',
                'show_in_nav' => true,
                'nav_label' => 'Get started',
                'content' => [
                    'headline' => 'Ready to ditch the deploy-to-edit cycle?',
                    'subheadline' => 'Start your free 14-day trial. No credit card required.',
                    'primary_label' => 'Start free trial',
                    'primary_link' => 'mailto:hello@example.com',
                    'secondary_label' => 'Book a demo',
                    'secondary_link' => 'mailto:hello@example.com',
                ],
            ],

            // 8. FAQ accordion
            [
                'type' => 'accordion',
                'label' => 'FAQ',
                'show_in_nav' => true,
                'nav_label' => 'FAQ',
                'content' => [
                    'headline' => 'Frequently asked questions',
                    'items' => [
                        [
                            'title' => 'Do I need a developer to use this?',
                            'content' => '<p>No. The whole point is that content editors can build, edit, and publish pages without writing code or waiting on engineering.</p>',
                        ],
                        [
                            'title' => 'How does the dark / live workflow work?',
                            'content' => '<p>You edit drafts in the page builder. When you publish to <strong>Dark</strong>, your changes appear on a private preview link. When everyone\'s happy, publish to <strong>Live</strong> and the public site updates instantly.</p>',
                        ],
                        [
                            'title' => 'Can I undo a bad publish?',
                            'content' => '<p>Yes — every publish creates a full snapshot of the page. The publish history page lets you roll back to any previous version.</p>',
                        ],
                        [
                            'title' => 'What about images and media?',
                            'content' => '<p>The asset manager handles uploads, auto-compresses images to webp, and lets you organise files into folders and tag them. Click any image in the page builder to swap it out instantly.</p>',
                        ],
                    ],
                ],
            ],

            // 9. Footer text
            [
                'type' => 'text',
                'label' => 'Footer prose',
                'show_in_nav' => false,
                'nav_label' => null,
                'content' => [
                    'body' => '<p class="text-center">Have questions? <a href="mailto:hello@example.com">Email us</a> — we typically reply within a few hours.</p>',
                ],
            ],
        ];

        foreach ($sections as $i => $section) {
            $contentJson = json_encode($section['content']);
            DB::table('sections')->insert([
                'type' => $section['type'],
                'label' => $section['label'],
                'show_in_nav' => $section['show_in_nav'] ?? false,
                'nav_label' => $section['nav_label'] ?? null,
                'order' => $i + 1,
                'enabled' => true,
                'status' => 'live',
                'draft_content' => $contentJson,
                'dark_preview_content' => $contentJson,
                'live_published_content' => $contentJson,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Record one synthetic publish history entry so the Publish History
        // page isn't empty on first boot.
        $snapshot = [];
        foreach ($sections as $i => $section) {
            $snapshot[] = [
                'section_id' => $i + 1,
                'content' => $section['content'],
                'order' => $i + 1,
            ];
        }

        DB::table('publish_histories')->insert([
            'environment' => 'live',
            'published_by' => null,
            'snapshot' => json_encode($snapshot),
            'release_notes' => 'Initial demo content seeded by SectionSeeder.',
            'scheduled_at' => null,
            'created_at' => $now,
        ]);

        $this->command?->info('SectionSeeder: 9 demo sections published to Live.');
    }
}
