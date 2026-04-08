<?php

namespace Tests\Unit;

use App\Services\SectionContentValidator;
use PHPUnit\Framework\TestCase;

class SectionContentValidatorTest extends TestCase
{
    protected SectionContentValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new SectionContentValidator();
    }

    // ---- hero ---------------------------------------------------------------

    public function test_hero_requires_headline(): void
    {
        $errors = $this->validator->validate('hero', []);
        $this->assertContains('Hero headline is required.', $errors);
    }

    public function test_hero_passes_with_headline(): void
    {
        $this->assertEmpty($this->validator->validate('hero', ['headline' => 'Welcome']));
    }

    // ---- text ---------------------------------------------------------------

    public function test_text_requires_non_empty_body(): void
    {
        $this->assertNotEmpty($this->validator->validate('text', []));
        $this->assertNotEmpty($this->validator->validate('text', ['body' => '']));
        $this->assertNotEmpty($this->validator->validate('text', ['body' => '<p></p>']));
    }

    public function test_text_passes_with_body(): void
    {
        $this->assertEmpty($this->validator->validate('text', ['body' => '<p>Hello</p>']));
    }

    // ---- image_text ---------------------------------------------------------

    public function test_image_text_requires_headline_and_some_text(): void
    {
        $errors = $this->validator->validate('image_text', []);
        $this->assertContains('Headline is required.', $errors);
        $this->assertContains('Provide a text summary or rich-text body.', $errors);
    }

    public function test_image_text_passes_with_text_summary(): void
    {
        $this->assertEmpty($this->validator->validate('image_text', [
            'headline' => 'About us',
            'text' => 'A short summary.',
        ]));
    }

    public function test_image_text_passes_with_rich_body_only(): void
    {
        $this->assertEmpty($this->validator->validate('image_text', [
            'headline' => 'About us',
            'body' => '<p>Rich content here</p>',
        ]));
    }

    // ---- gallery ------------------------------------------------------------

    public function test_gallery_requires_at_least_one_image(): void
    {
        $errors = $this->validator->validate('gallery', ['images' => []]);
        $this->assertNotEmpty($errors);
    }

    public function test_gallery_passes_with_one_image(): void
    {
        $this->assertEmpty($this->validator->validate('gallery', [
            'images' => [['url' => 'https://example.com/a.jpg']],
        ]));
    }

    // ---- carousel -----------------------------------------------------------

    public function test_carousel_requires_at_least_one_slide(): void
    {
        $errors = $this->validator->validate('carousel', ['items' => []]);
        $this->assertNotEmpty($errors);
    }

    public function test_carousel_each_slide_needs_headline_and_image(): void
    {
        $errors = $this->validator->validate('carousel', [
            'items' => [['headline' => '', 'image' => null]],
        ]);
        $this->assertContains('Slide #1: headline is required.', $errors);
        $this->assertContains('Slide #1: image is required.', $errors);
    }

    public function test_carousel_cta_must_be_paired(): void
    {
        $errors = $this->validator->validate('carousel', [
            'items' => [[
                'headline' => 'Hi',
                'image' => ['url' => '...'],
                'cta_link' => 'https://example.com',
                'cta_text' => '', // missing label
            ]],
        ]);
        $this->assertContains('Slide #1: CTA needs both a label and a URL.', $errors);
    }

    public function test_carousel_passes_when_complete(): void
    {
        $this->assertEmpty($this->validator->validate('carousel', [
            'items' => [[
                'headline' => 'Slide 1',
                'image' => ['url' => '...'],
                'cta_text' => 'Go',
                'cta_link' => 'https://example.com',
            ]],
        ]));
    }

    // ---- accordion ----------------------------------------------------------

    public function test_accordion_each_item_requires_title_and_content(): void
    {
        $errors = $this->validator->validate('accordion', [
            'items' => [['title' => '', 'content' => '']],
        ]);
        $this->assertContains('Accordion item #1: title is required.', $errors);
        $this->assertContains('Accordion item #1: content is required.', $errors);
    }

    public function test_accordion_passes_with_filled_item(): void
    {
        $this->assertEmpty($this->validator->validate('accordion', [
            'items' => [['title' => 'Q1', 'content' => '<p>A1</p>']],
        ]));
    }

    // ---- tabs ---------------------------------------------------------------

    public function test_tabs_each_item_requires_title_and_content(): void
    {
        $errors = $this->validator->validate('tabs', [
            'items' => [['title' => 'Tab 1', 'content' => '']],
        ]);
        $this->assertContains('Tab #1: content is required.', $errors);
    }

    // ---- cta ----------------------------------------------------------------

    public function test_cta_requires_headline_and_one_button_label(): void
    {
        $errors = $this->validator->validate('cta', []);
        $this->assertContains('CTA headline is required.', $errors);
        $this->assertContains('Add at least one CTA button label.', $errors);
    }

    public function test_cta_passes_with_primary_label(): void
    {
        $this->assertEmpty($this->validator->validate('cta', [
            'headline' => 'Get going',
            'primary_label' => 'Start',
        ]));
    }

    // ---- video --------------------------------------------------------------

    public function test_video_requires_headline_and_source(): void
    {
        $errors = $this->validator->validate('video', []);
        $this->assertContains('Video headline is required.', $errors);
        $this->assertContains('Provide either an embed URL or upload a video asset.', $errors);
    }

    public function test_video_passes_with_embed_url(): void
    {
        $this->assertEmpty($this->validator->validate('video', [
            'headline' => 'Watch this',
            'embed_url' => 'https://youtu.be/abc',
        ]));
    }

    // ---- feature_grid -------------------------------------------------------

    public function test_feature_grid_each_item_requires_title(): void
    {
        $errors = $this->validator->validate('feature_grid', [
            'items' => [['title' => '', 'description' => 'desc']],
        ]);
        $this->assertContains('Feature #1: title is required.', $errors);
    }

    // ---- cards --------------------------------------------------------------

    public function test_cards_each_item_requires_title(): void
    {
        $errors = $this->validator->validate('cards', [
            'items' => [['title' => '']],
        ]);
        $this->assertContains('Card #1: title is required.', $errors);
    }

    // ---- testimonials -------------------------------------------------------

    public function test_testimonials_each_item_requires_quote_and_name(): void
    {
        $errors = $this->validator->validate('testimonials', [
            'items' => [['quote' => '', 'name' => '']],
        ]);
        $this->assertContains('Testimonial #1: quote is required.', $errors);
        $this->assertContains('Testimonial #1: name is required.', $errors);
    }

    // ---- stats --------------------------------------------------------------

    public function test_stats_each_item_requires_label_and_numeric_value(): void
    {
        $errors = $this->validator->validate('stats', [
            'items' => [['label' => '', 'value' => 'not-a-number']],
        ]);
        $this->assertContains('Stat #1: label is required.', $errors);
        $this->assertContains('Stat #1: value must be a number.', $errors);
    }

    public function test_stats_passes_with_zero_value(): void
    {
        $this->assertEmpty($this->validator->validate('stats', [
            'items' => [['label' => 'Customers', 'value' => 0]],
        ]));
    }

    // ---- promo_banner -------------------------------------------------------

    public function test_promo_banner_requires_message(): void
    {
        $errors = $this->validator->validate('promo_banner', []);
        $this->assertContains('Promo message is required.', $errors);
    }

    public function test_promo_banner_link_requires_label(): void
    {
        $errors = $this->validator->validate('promo_banner', [
            'message' => 'Sale on now',
            'link' => 'https://example.com',
            'link_label' => '',
        ]);
        $this->assertContains('Set a link label or remove the link URL.', $errors);
    }

    public function test_promo_banner_passes_minimal(): void
    {
        $this->assertEmpty($this->validator->validate('promo_banner', [
            'message' => 'Welcome',
        ]));
    }

    // ---- unknown type -------------------------------------------------------

    public function test_unknown_type_passes_silently(): void
    {
        $this->assertEmpty($this->validator->validate('made_up_type', []));
    }

    public function test_null_content_is_treated_as_empty(): void
    {
        $errors = $this->validator->validate('hero', null);
        $this->assertContains('Hero headline is required.', $errors);
    }
}
