# UI and UX Standards

This guide defines the visual and interaction standards for the Temp Mail SaaS admin and public UI.

Read this together with `docs/PROJECT_BLUEPRINT.md`.

## Product Feel

The product should feel like a premium 2026 SaaS operations platform, not a generic Laravel admin panel.

Use module names that describe work:

- Operations Overview
- Locale Launch Center
- Blog Studio
- Page Studio
- SEO Growth Center
- Mailbox Operations
- Security Defense Center
- Activity & Audit Logs
- Theme Launch Center
- Appearance Studio
- Typography Center

## Admin UI Principles

- Calm, focused, work-oriented.
- Dense enough for repeated admin use.
- Clear hierarchy.
- Strong empty states.
- Polished error/success states.
- No default Laravel look.
- No marketing hero pages inside admin.
- No decorative gradient blobs/orbs.
- No card-inside-card nesting.
- No oversized hero typography inside dashboards.

## Layout Standards

Admin screens should usually use:

- fixed or sticky left sidebar
- page header
- main content area
- cards only for repeated items, tools, panels, and modals
- full-width section bands or unframed layouts where appropriate

Avoid:

- floating landing-page layouts in admin
- decorative cards around every section
- raw static tables when a workflow card or queue is better

## Menu UX

The sidebar must be grouped by product responsibility:

- Workspace
- Markets
- Content
- Mail Infrastructure
- Growth
- People
- Brand
- Trust
- System

Menu items must be permission-aware.

Normal users must not see admin navigation.

## Ctrl+K Command Palette

Admin shell should support a lightweight command palette:

- open with Ctrl+K / Cmd+K
- close with Esc
- search modules
- search allowed actions
- recent commands
- grouped results
- keyboard navigation
- permission-aware results
- mobile fallback icon

Dangerous actions must not execute directly from Ctrl+K.

Examples:

- Go to Operations Overview
- Go to Mailbox Operations
- Go to Locale Launch Center
- Go to Blog Studio
- Go to SEO Growth Center
- Check for updates
- Create backup

## Forms

Forms must include:

- proper labels
- required indicators where needed
- validation errors under fields
- global error summary for multi-field failures
- `aria-invalid`
- `aria-describedby`
- visible focus states
- old input preservation
- disabled/loading state during submit

Submit buttons should:

- prevent double submit
- show a spinner or working state
- keep accessible status text

## Tables vs Cards

Use tables for:

- many records
- dense comparison
- large datasets
- audit logs when scanning matters

Use cards/queues for:

- comments moderation
- locale readiness
- theme selection
- update status
- security status
- operational decisions

For 30 locales, prefer card/readiness layouts over a traditional long language table.

## Status and Lifecycle UI

Use consistent status labels:

- Draft
- Active
- Hidden
- Published
- Scheduled
- Trashed
- Archived
- Locked
- Expired

Use badges consistently.

Dangerous actions must have confirmation.

Restore should be available where trash exists.

## Accessibility

Every interactive UI must support:

- keyboard navigation
- visible focus state
- labels
- `aria-invalid`
- `aria-describedby`
- `aria-live`
- `role="alert"` for errors
- `role="status"` for async updates
- unique ids

Do not rely on color alone for status.

## Alpine.js Usage

Use Alpine.js only for small interactions:

- dropdowns
- tabs
- modals
- filter panel toggle
- select all
- dirty state warning
- unsaved changes warning
- password show/hide
- command palette
- polling dashboard metrics

Do not turn Alpine into a full application framework.

No Livewire.

## Dashboard Live Updates

Operations Overview may feel live, but MVP must stay shared-hosting friendly:

- Blade initial render
- Alpine fetch polling
- default polling interval: 30 seconds
- manual refresh
- live/paused state
- stale data warning
- critical alert strip
- cached JSON endpoint
- no WebSockets in MVP

## Public Theme Rules

Themes affect public website only.

Admin layout is stable and not theme-controlled.

Themes:

- Horizon
- Atlas
- Legacy

Only one theme can be active.

Appearance controls safe visual tokens only.

Typography controls font stacks and RTL/LTR coverage.

## Content UX Rules

Blog, pages, sections, and email templates are language-specific content records.

They are not translation records.

Translation Center is only for predefined UI/source keys.

Public content editors should feel like modern publishing workflows, not textarea dumps.

## Media UX Rules

Media Library owns uploaded assets.

Media must connect to:

- blog posts
- pages
- sections
- SEO/OG images
- avatars
- email templates

Use media picker flows instead of raw file path inputs where possible.

## SEO UX Rules

SEO must feel like a Search Growth Center, not a title/description form.

Include:

- target-based SEO records
- language-specific SEO
- Google preview
- social preview
- schema readiness
- sitemap/robots controls
- duplicate warnings
- missing metadata queue

## Security UX Rules

Security must feel like a Security Defense Center.

Secret fields must be masked.

Provider tests must be available where relevant.

Fail states must be clear and recoverable.

Do not allow a bad security setting to lock admins out.

## Color and Typography

Avoid one-note palettes.

Avoid interfaces dominated by:

- purple gradients
- beige/tan palettes
- dark blue/slate-only themes
- brown/orange-heavy themes

Use accessible contrast.

Do not scale font size with viewport width.

Letter spacing should usually be `0`.

## Buttons and Icons

Use icon buttons where icons are familiar.

Use text buttons for clear commands.

Buttons must have stable dimensions.

Button text must not overflow.

Use tooltips for unfamiliar icons.

## Final Rule

Every admin screen must answer:

- What is happening?
- What needs attention?
- What can I do next?
- Is this safe to publish/change/delete?

If the screen only displays fields, it is not finished.
