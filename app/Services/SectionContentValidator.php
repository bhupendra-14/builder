<?php

namespace App\Services;

/**
 * Validates a section's content payload according to its type.
 *
 * Each section type has its own minimum required shape; this is the single
 * source of truth on the backend. Returns an array of human-readable errors
 * (empty array == valid).
 */
class SectionContentValidator
{
    public function validate(string $type, ?array $content): array
    {
        $content = $content ?? [];
        $errors = [];

        $req = function (string $field, string $label) use ($content, &$errors) {
            $val = $content[$field] ?? null;
            if (!is_string($val) || trim($val) === '') {
                $errors[] = "{$label} is required.";
            }
        };

        $reqItems = function (string $minLabel, int $min = 1) use ($content, &$errors) {
            $items = $content['items'] ?? [];
            if (!is_array($items) || count($items) < $min) {
                $errors[] = "Add at least {$min} {$minLabel}.";
                return false;
            }
            return true;
        };

        switch ($type) {
            case 'hero':
                $req('headline', 'Hero headline');
                break;

            case 'text':
                if (empty($content['body']) || !is_string($content['body']) || trim(strip_tags($content['body'])) === '') {
                    $errors[] = 'Text body cannot be empty.';
                }
                break;

            case 'image_text':
                $req('headline', 'Headline');
                $hasText = !empty($content['text']) || !empty($content['body']);
                if (!$hasText) {
                    $errors[] = 'Provide a text summary or rich-text body.';
                }
                break;

            case 'gallery':
                $images = $content['images'] ?? [];
                if (!is_array($images) || count($images) < 1) {
                    $errors[] = 'Add at least 1 image to the gallery.';
                }
                break;

            case 'carousel':
                if ($reqItems('slide', 1)) {
                    foreach ($content['items'] as $i => $item) {
                        $n = $i + 1;
                        if (empty($item['headline']) || trim($item['headline']) === '') {
                            $errors[] = "Slide #{$n}: headline is required.";
                        }
                        if (empty($item['image'])) {
                            $errors[] = "Slide #{$n}: image is required.";
                        }
                        // If a CTA link is set, the label is required (and vice versa)
                        $hasLink = !empty($item['cta_link']);
                        $hasLabel = !empty($item['cta_text']);
                        if ($hasLink !== $hasLabel) {
                            $errors[] = "Slide #{$n}: CTA needs both a label and a URL.";
                        }
                    }
                }
                break;

            case 'accordion':
                if ($reqItems('item', 1)) {
                    foreach ($content['items'] as $i => $item) {
                        $n = $i + 1;
                        if (empty($item['title']) || trim($item['title']) === '') {
                            $errors[] = "Accordion item #{$n}: title is required.";
                        }
                        if (empty($item['content']) || trim(strip_tags((string) $item['content'])) === '') {
                            $errors[] = "Accordion item #{$n}: content is required.";
                        }
                    }
                }
                break;

            case 'tabs':
                if ($reqItems('tab', 1)) {
                    foreach ($content['items'] as $i => $item) {
                        $n = $i + 1;
                        if (empty($item['title']) || trim($item['title']) === '') {
                            $errors[] = "Tab #{$n}: title is required.";
                        }
                        if (empty($item['content']) || trim(strip_tags((string) $item['content'])) === '') {
                            $errors[] = "Tab #{$n}: content is required.";
                        }
                    }
                }
                break;

            case 'cta':
                $req('headline', 'CTA headline');
                $hasButton = !empty($content['primary_label']) || !empty($content['secondary_label']);
                if (!$hasButton) {
                    $errors[] = 'Add at least one CTA button label.';
                }
                break;

            case 'video':
                $req('headline', 'Video headline');
                $hasSource = !empty($content['embed_url']) || !empty($content['video']);
                if (!$hasSource) {
                    $errors[] = 'Provide either an embed URL or upload a video asset.';
                }
                break;

            case 'feature_grid':
                if ($reqItems('feature', 1)) {
                    foreach ($content['items'] as $i => $item) {
                        $n = $i + 1;
                        if (empty($item['title']) || trim($item['title']) === '') {
                            $errors[] = "Feature #{$n}: title is required.";
                        }
                    }
                }
                break;

            case 'cards':
                if ($reqItems('card', 1)) {
                    foreach ($content['items'] as $i => $item) {
                        $n = $i + 1;
                        if (empty($item['title']) || trim($item['title']) === '') {
                            $errors[] = "Card #{$n}: title is required.";
                        }
                    }
                }
                break;

            case 'testimonials':
                if ($reqItems('testimonial', 1)) {
                    foreach ($content['items'] as $i => $item) {
                        $n = $i + 1;
                        if (empty($item['quote']) || trim($item['quote']) === '') {
                            $errors[] = "Testimonial #{$n}: quote is required.";
                        }
                        if (empty($item['name']) || trim($item['name']) === '') {
                            $errors[] = "Testimonial #{$n}: name is required.";
                        }
                    }
                }
                break;

            case 'stats':
                if ($reqItems('stat', 1)) {
                    foreach ($content['items'] as $i => $item) {
                        $n = $i + 1;
                        if (empty($item['label']) || trim($item['label']) === '') {
                            $errors[] = "Stat #{$n}: label is required.";
                        }
                        if (!isset($item['value']) || !is_numeric($item['value'])) {
                            $errors[] = "Stat #{$n}: value must be a number.";
                        }
                    }
                }
                break;

            case 'promo_banner':
                $req('message', 'Promo message');
                if (!empty($content['link']) && empty($content['link_label'])) {
                    $errors[] = 'Set a link label or remove the link URL.';
                }
                break;
        }

        return $errors;
    }
}
