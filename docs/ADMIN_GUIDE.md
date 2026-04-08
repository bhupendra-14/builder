# Admin Usage Guide

This guide is for administrators and editors using the Website Backend to manage the single-page website.

---

## 1. Signing in

1. Open `/login` in your browser.
2. Enter your email and password.
3. You will be redirected to the dashboard at `/admin`.

**Forgot your password?** Click "Forgot password" on the login screen, enter your email, and follow the link sent to your inbox.

### Roles

| Role | What they can do |
| --- | --- |
| **Admin** | Everything — manage users, settings, content, assets, and publish to both Dark and Live |
| **Editor** | Manage content and assets, preview via Dark, but cannot publish to Live or manage users |

Your available menu items depend on your role.

---

## 2. Dashboard

The dashboard shows:
- Total counts of users, sections, and assets
- The 10 most recent audit log entries (who did what, when)

Use it as a quick health check when you sign in.

---

## 3. Page Builder

Navigate to **Page Builder** from the sidebar. This screen lists every section on your website in the order they appear to visitors.

### Adding a section

1. Click **Add New Section**.
2. Give the section a **label** (internal name, not shown to visitors).
3. Pick a **type** from the dropdown. The available types are:

| Type | Use for |
| --- | --- |
| Hero Header | Big above-the-fold banner with headline, text, background image, and a CTA button |
| Rich Text | Long-form prose or HTML content |
| Image + Text | Split layout with an image on the left or right |
| Gallery Grid | A grid of images |
| Tabs | Tabbed content panels |
| Accordion / FAQ | Collapsible Q&A or step-by-step content |
| Call to Action | A coloured band with a headline and one or two buttons |
| Video | YouTube/Vimeo embed or uploaded video with poster image |
| Feature Grid | 2- or 3-column grid of icons + short descriptions |
| Cards | Card deck with image, title, description, and link |
| Testimonials | Customer quotes with name, role, and avatar |
| Stats Counter | Animated counters for numbers like "1,000+ customers" |
| Promo Banner | Thin dismissible bar at the top of the page |

4. Click **Save**.

The section is created as a **draft** and is not visible to anyone until you publish.

### Reordering

Drag the handle (☰ icon) on the left of each section to reorder. Order is saved automatically when you drop.

### Duplicating

Click **Duplicate** on any section. A copy is inserted right after the original with "(Copy)" appended to the label. The copy starts as a draft regardless of the original's status.

### Enabling / disabling

Click the **Enabled / Disabled** toggle to control whether the section appears on the public site. Disabled sections are kept but hidden. When you next publish to Live, their content will be cleared from the live site.

### Deleting

Click **Delete**. This is a soft delete — the section is hidden and can be restored from the database if needed. Version history is preserved for soft-deleted sections.

---

## 4. Editing section content

Click **Edit Content** on any section to open the builder workspace.

The workspace has three parts:

- **Left sidebar** — form fields specific to the section type
- **Main preview** — a live render of your changes
- **Top bar** — Save Draft button and Version History

### Saving

Click **Save Draft** to persist your changes. Every save creates a version history entry (up to 20 per section — the oldest is pruned beyond that).

Saving does **not** publish — the content is only written to the `draft` environment.

### Version history

Click **Version History** in the top bar to open the versions panel. You can click **Restore** on any version to roll back the draft to that snapshot. Restoring does not publish; it just overwrites the current draft.

### Picking images and media

Any field that accepts an image or video opens the Asset Picker. Search or browse, click an asset, then click **Select**.

---

## 5. Asset Manager

Navigate to **Media** from the sidebar.

### Uploading

Drag files into the uploader or click to browse. Supported formats: `jpeg, jpg, png, gif, webp, svg, pdf, doc, docx`. Maximum 10 MB per file.

### Browsing and filtering

Use the **search box** to find files by name or title, and the **tag filter** to list only assets with a specific tag.

### Deleting

Click the red **X** button on any asset thumbnail. Deletion is soft — the file record can be restored but the file itself is removed from disk.

### Metadata

Open an asset to edit its **title**, **alt text**, and **tags**. Good alt text is important for accessibility and SEO.

---

## 6. Publishing workflow

The system has two publishing environments:

### Dark (Preview)

The **Dark** environment is an internal preview. It is not indexed by search engines and is reachable only via a dark link that includes a secret preview token. Use Dark to review content before it goes to the public.

### Live

The **Live** environment is what the public sees on your website.

### How to publish

1. Make your changes in the Page Builder / Builder Workspace. Save each section.
2. Go to the **Publish** screen.
3. Choose **Publish to Dark** and (optionally) add release notes. This promotes every enabled section's draft into the Dark environment.
4. Share the Dark link with your reviewers. Walk through the preview.
5. When everyone is happy, go back to **Publish** and choose **Publish to Live**. This promotes the Dark content to the public website.

**Important:** Publishing to Live also clears the live content of any section that you have disabled or deleted since the last Live publish. That's how you "unpublish" a section.

### Scheduled publishing

When submitting a publish, you can optionally set a **scheduled time**. The system will create a pending entry and run the publish automatically at that time via a background job. Scheduled publishes are visible on the Publish History screen with a **pending** status until they run.

> **Server requirement:** scheduled publishing relies on `php artisan schedule:run` being invoked every minute (via cron on Linux or Task Scheduler on Windows). Ask your developer to confirm this is configured.

### Publish history

Every publish (Dark or Live, scheduled or immediate) is recorded with:
- Who published it
- When it was published (or scheduled)
- Release notes
- A full snapshot of what was published

You can review past publishes and roll back if needed.

---

## 7. User management (Admin only)

Navigate to **Users**. You can:
- Invite new users by creating an account with name, email, role, and initial password
- Update an existing user's details, role, or password
- Deactivate a user (by toggling **active**)
- Delete a user (soft delete)

You cannot delete your own account — this is a safety check.

---

## 8. Settings

Navigate to **Settings** to manage site-wide configuration like site name, SEO defaults, and social links.

---

## 9. Audit log

Navigate to **Audit**. Every significant action (content edit, publish, user change, asset delete, etc.) is recorded here with:
- Who did it
- When
- What changed (old and new values)
- IP address and user agent

Use this to trace "who changed the homepage banner last Tuesday?" style questions.

---

## 10. Quick reference

| I want to... | Go to |
| --- | --- |
| Change hero text on the homepage | Page Builder → Edit Content on the Hero section |
| Put a new image on a section | Page Builder → Edit Content → click the image field → Asset Picker |
| Reorder the homepage sections | Page Builder → drag the ☰ handles |
| Preview my changes before they go live | Publish → Publish to Dark → open the dark link |
| Put changes live | Publish → Publish to Live |
| Undo my last edit on one section | Builder Workspace → Version History → Restore |
| Undo an entire Live publish | Publish History → find the previous Live entry → Rollback |
| Hide a section without deleting it | Page Builder → toggle Enabled off → Publish to Live |
| Completely remove a section from the site | Page Builder → Delete → Publish to Live |

---

## 11. Troubleshooting

**"I saved my changes but they're not on the live site."**
Saving only writes to draft. You must publish to Dark (optional) and then Live.

**"The dark preview link isn't working."**
The preview token may be missing or wrong. Ask your developer to confirm `PREVIEW_TOKEN` is set in the server environment, then request a fresh preview link.

**"I scheduled a publish but nothing happened."**
Scheduled publishes require the `schedule:run` cron to be running on the server. Ask your developer to check.

**"I deleted a section by accident."**
Delete is a soft delete. Contact your developer to restore it — the row still exists in the database and can be recovered.

**"The page builder isn't loading my section."**
Refresh the page. If it still fails, check the browser console for errors and share them with your developer.
