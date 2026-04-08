/**
 * Per-section content validator. Mirrors app/Services/SectionContentValidator.php
 * so the user sees the same errors before the request hits the server.
 *
 * Returns an array of human-readable error strings (empty == valid).
 */

const isBlank = (v) => v == null || (typeof v === 'string' && v.trim() === '');

const stripTags = (html) => (html || '').replace(/<[^>]*>/g, '');
const isBlankRich = (html) => stripTags(html).trim() === '';

export function validateSectionContent(type, content) {
    const c = content || {};
    const errors = [];

    const req = (field, label) => {
        if (isBlank(c[field])) errors.push(`${label} is required.`);
    };

    const reqItems = (label, min = 1) => {
        const items = c.items || [];
        if (!Array.isArray(items) || items.length < min) {
            errors.push(`Add at least ${min} ${label}.`);
            return false;
        }
        return true;
    };

    switch (type) {
        case 'hero':
            req('headline', 'Hero headline');
            break;

        case 'text':
            if (isBlankRich(c.body)) errors.push('Text body cannot be empty.');
            break;

        case 'image_text':
            req('headline', 'Headline');
            if (isBlank(c.text) && isBlankRich(c.body)) {
                errors.push('Provide a text summary or rich-text body.');
            }
            break;

        case 'gallery':
            if (!Array.isArray(c.images) || c.images.length < 1) {
                errors.push('Add at least 1 image to the gallery.');
            }
            break;

        case 'carousel':
            if (reqItems('slide')) {
                c.items.forEach((item, i) => {
                    const n = i + 1;
                    if (isBlank(item.headline)) errors.push(`Slide #${n}: headline is required.`);
                    if (!item.image) errors.push(`Slide #${n}: image is required.`);
                    const hasLink = !isBlank(item.cta_link);
                    const hasLabel = !isBlank(item.cta_text);
                    if (hasLink !== hasLabel) {
                        errors.push(`Slide #${n}: CTA needs both a label and a URL.`);
                    }
                });
            }
            break;

        case 'accordion':
            if (reqItems('item')) {
                c.items.forEach((item, i) => {
                    const n = i + 1;
                    if (isBlank(item.title)) errors.push(`Accordion item #${n}: title is required.`);
                    if (isBlankRich(item.content)) errors.push(`Accordion item #${n}: content is required.`);
                });
            }
            break;

        case 'tabs':
            if (reqItems('tab')) {
                c.items.forEach((item, i) => {
                    const n = i + 1;
                    if (isBlank(item.title)) errors.push(`Tab #${n}: title is required.`);
                    if (isBlankRich(item.content)) errors.push(`Tab #${n}: content is required.`);
                });
            }
            break;

        case 'cta':
            req('headline', 'CTA headline');
            if (isBlank(c.primary_label) && isBlank(c.secondary_label)) {
                errors.push('Add at least one CTA button label.');
            }
            break;

        case 'video':
            req('headline', 'Video headline');
            if (isBlank(c.embed_url) && !c.video) {
                errors.push('Provide either an embed URL or upload a video asset.');
            }
            break;

        case 'feature_grid':
            if (reqItems('feature')) {
                c.items.forEach((item, i) => {
                    if (isBlank(item.title)) errors.push(`Feature #${i + 1}: title is required.`);
                });
            }
            break;

        case 'cards':
            if (reqItems('card')) {
                c.items.forEach((item, i) => {
                    if (isBlank(item.title)) errors.push(`Card #${i + 1}: title is required.`);
                });
            }
            break;

        case 'testimonials':
            if (reqItems('testimonial')) {
                c.items.forEach((item, i) => {
                    const n = i + 1;
                    if (isBlank(item.quote)) errors.push(`Testimonial #${n}: quote is required.`);
                    if (isBlank(item.name)) errors.push(`Testimonial #${n}: name is required.`);
                });
            }
            break;

        case 'stats':
            if (reqItems('stat')) {
                c.items.forEach((item, i) => {
                    const n = i + 1;
                    if (isBlank(item.label)) errors.push(`Stat #${n}: label is required.`);
                    if (item.value == null || item.value === '' || isNaN(Number(item.value))) {
                        errors.push(`Stat #${n}: value must be a number.`);
                    }
                });
            }
            break;

        case 'promo_banner':
            req('message', 'Promo message');
            if (!isBlank(c.link) && isBlank(c.link_label)) {
                errors.push('Set a link label or remove the link URL.');
            }
            break;
    }

    return errors;
}
