import { describe, it, expect } from 'vitest';
import { validateSectionContent } from '../sectionValidation';

/**
 * Mirrors tests/Unit/SectionContentValidatorTest.php so the JS validator
 * stays in sync with the PHP one. If a rule is added to one, it must be
 * added to the other AND a corresponding test added in both files.
 */

describe('validateSectionContent', () => {
    // ---- hero -----------------------------------------------------------

    describe('hero', () => {
        it('requires a headline', () => {
            const errors = validateSectionContent('hero', {});
            expect(errors).toContain('Hero headline is required.');
        });

        it('passes when headline is set', () => {
            expect(validateSectionContent('hero', { headline: 'Welcome' })).toEqual([]);
        });
    });

    // ---- text -----------------------------------------------------------

    describe('text', () => {
        it('requires non-empty body', () => {
            expect(validateSectionContent('text', {}).length).toBeGreaterThan(0);
            expect(validateSectionContent('text', { body: '' }).length).toBeGreaterThan(0);
            expect(validateSectionContent('text', { body: '<p></p>' }).length).toBeGreaterThan(0);
        });

        it('passes with body content', () => {
            expect(validateSectionContent('text', { body: '<p>Hello</p>' })).toEqual([]);
        });
    });

    // ---- image_text -----------------------------------------------------

    describe('image_text', () => {
        it('requires headline and some text', () => {
            const errors = validateSectionContent('image_text', {});
            expect(errors).toContain('Headline is required.');
            expect(errors).toContain('Provide a text summary or rich-text body.');
        });

        it('passes with text summary only', () => {
            expect(validateSectionContent('image_text', {
                headline: 'About us',
                text: 'A short summary.',
            })).toEqual([]);
        });

        it('passes with rich body only', () => {
            expect(validateSectionContent('image_text', {
                headline: 'About us',
                body: '<p>Rich content here</p>',
            })).toEqual([]);
        });
    });

    // ---- gallery --------------------------------------------------------

    describe('gallery', () => {
        it('requires at least one image', () => {
            expect(validateSectionContent('gallery', { images: [] }).length).toBeGreaterThan(0);
        });

        it('passes with one image', () => {
            expect(validateSectionContent('gallery', {
                images: [{ url: 'https://example.com/a.jpg' }],
            })).toEqual([]);
        });
    });

    // ---- carousel -------------------------------------------------------

    describe('carousel', () => {
        it('requires at least one slide', () => {
            expect(validateSectionContent('carousel', { items: [] }).length).toBeGreaterThan(0);
        });

        it('each slide needs a headline and image', () => {
            const errors = validateSectionContent('carousel', {
                items: [{ headline: '', image: null }],
            });
            expect(errors).toContain('Slide #1: headline is required.');
            expect(errors).toContain('Slide #1: image is required.');
        });

        it('CTA must be paired (label + URL)', () => {
            const errors = validateSectionContent('carousel', {
                items: [{
                    headline: 'Hi',
                    image: { url: '...' },
                    cta_link: 'https://example.com',
                    cta_text: '',
                }],
            });
            expect(errors).toContain('Slide #1: CTA needs both a label and a URL.');
        });

        it('passes when complete', () => {
            expect(validateSectionContent('carousel', {
                items: [{
                    headline: 'Slide 1',
                    image: { url: '...' },
                    cta_text: 'Go',
                    cta_link: 'https://example.com',
                }],
            })).toEqual([]);
        });
    });

    // ---- accordion ------------------------------------------------------

    describe('accordion', () => {
        it('each item requires title and content', () => {
            const errors = validateSectionContent('accordion', {
                items: [{ title: '', content: '' }],
            });
            expect(errors).toContain('Accordion item #1: title is required.');
            expect(errors).toContain('Accordion item #1: content is required.');
        });

        it('passes with filled item', () => {
            expect(validateSectionContent('accordion', {
                items: [{ title: 'Q1', content: '<p>A1</p>' }],
            })).toEqual([]);
        });
    });

    // ---- tabs -----------------------------------------------------------

    describe('tabs', () => {
        it('each item requires title and content', () => {
            const errors = validateSectionContent('tabs', {
                items: [{ title: 'Tab 1', content: '' }],
            });
            expect(errors).toContain('Tab #1: content is required.');
        });
    });

    // ---- cta ------------------------------------------------------------

    describe('cta', () => {
        it('requires headline and one button label', () => {
            const errors = validateSectionContent('cta', {});
            expect(errors).toContain('CTA headline is required.');
            expect(errors).toContain('Add at least one CTA button label.');
        });

        it('passes with primary label only', () => {
            expect(validateSectionContent('cta', {
                headline: 'Get going',
                primary_label: 'Start',
            })).toEqual([]);
        });
    });

    // ---- video ----------------------------------------------------------

    describe('video', () => {
        it('requires headline and source', () => {
            const errors = validateSectionContent('video', {});
            expect(errors).toContain('Video headline is required.');
            expect(errors).toContain('Provide either an embed URL or upload a video asset.');
        });

        it('passes with embed URL', () => {
            expect(validateSectionContent('video', {
                headline: 'Watch this',
                embed_url: 'https://youtu.be/abc',
            })).toEqual([]);
        });
    });

    // ---- feature_grid ---------------------------------------------------

    describe('feature_grid', () => {
        it('each item requires title', () => {
            const errors = validateSectionContent('feature_grid', {
                items: [{ title: '', description: 'desc' }],
            });
            expect(errors).toContain('Feature #1: title is required.');
        });
    });

    // ---- cards ----------------------------------------------------------

    describe('cards', () => {
        it('each item requires title', () => {
            const errors = validateSectionContent('cards', {
                items: [{ title: '' }],
            });
            expect(errors).toContain('Card #1: title is required.');
        });
    });

    // ---- testimonials ---------------------------------------------------

    describe('testimonials', () => {
        it('each item requires quote and name', () => {
            const errors = validateSectionContent('testimonials', {
                items: [{ quote: '', name: '' }],
            });
            expect(errors).toContain('Testimonial #1: quote is required.');
            expect(errors).toContain('Testimonial #1: name is required.');
        });
    });

    // ---- stats ----------------------------------------------------------

    describe('stats', () => {
        it('each item requires label and numeric value', () => {
            const errors = validateSectionContent('stats', {
                items: [{ label: '', value: 'not-a-number' }],
            });
            expect(errors).toContain('Stat #1: label is required.');
            expect(errors).toContain('Stat #1: value must be a number.');
        });

        it('passes with zero value', () => {
            expect(validateSectionContent('stats', {
                items: [{ label: 'Customers', value: 0 }],
            })).toEqual([]);
        });
    });

    // ---- promo_banner ---------------------------------------------------

    describe('promo_banner', () => {
        it('requires message', () => {
            const errors = validateSectionContent('promo_banner', {});
            expect(errors).toContain('Promo message is required.');
        });

        it('link requires label', () => {
            const errors = validateSectionContent('promo_banner', {
                message: 'Sale on now',
                link: 'https://example.com',
                link_label: '',
            });
            expect(errors).toContain('Set a link label or remove the link URL.');
        });

        it('passes minimal', () => {
            expect(validateSectionContent('promo_banner', {
                message: 'Welcome',
            })).toEqual([]);
        });
    });

    // ---- edge cases -----------------------------------------------------

    describe('edge cases', () => {
        it('unknown type passes silently', () => {
            expect(validateSectionContent('made_up_type', {})).toEqual([]);
        });

        it('null content is treated as empty', () => {
            const errors = validateSectionContent('hero', null);
            expect(errors).toContain('Hero headline is required.');
        });

        it('undefined content is treated as empty', () => {
            const errors = validateSectionContent('hero', undefined);
            expect(errors).toContain('Hero headline is required.');
        });
    });
});
