# Temp Mail SaaS Project Blueprint

This document is the source of truth for the Temp Mail SaaS project.

Before coding any module, read this file first. If a future request conflicts with this blueprint, stop and report the conflict before coding.

## Product Identity

- Product: Temp Mail SaaS
- UI language: English only
- Admin style: premium 2026 SaaS operations panel
- Target: shared-hosting friendly Laravel SaaS
- Official update server: `https://www.doic.net/update`

## Primary Stack

- Laravel 13
- PHP 8.5.6
- Blade Components
- Alpine.js
- Tailwind CSS
- Vite
- No Livewire
- No CDN Tailwind
- No raw standalone HTML pages

## Supporting Guides

Read these guides when working on related areas:

- `docs/guides/BLADE_COMPONENTS.md`
- `docs/guides/MODULE_PROMPTS.md`
- `docs/guides/UI_UX_STANDARDS.md`

## Architecture Rules

- Controllers must stay thin.
- Business logic belongs in Services, Actions, Stores, and Resolvers.
- Validation belongs in Form Requests.
- Authorization belongs in Policies, Gates, and role/permission checks.
- Views must use Laravel Blade.
- Reusable or complex UI must use Blade Components.
- Use named routes and `route()` helpers.
- Use `@vite` for assets.
- Use accessible validation errors.
- Audit critical actions.

## Forbidden Patterns

Never introduce:

- raw standalone HTML screens
- Livewire
- CDN Tailwind
- hardcoded asset hashes
- hardcoded `127.0.0.1` URLs
- hardcoded `/build/assets/...`
- business logic in Blade
- database queries inside Blade components
- services called directly from Blade components
- `post_translations`
- `page_translations`
- `section_translations`
- arbitrary Blade/PHP execution inside email templates
- secret values in logs
- plaintext API keys
- public backup files
- theme-controlled admin layout

## Admin Menu Map

### Workspace

- Operations Overview
- Mailbox Operations
- Product Analytics

### Markets

- Locale Launch Center
- Translation Center

### Content

- Page Studio
- Blog Studio
- Taxonomy
- Sections Studio
- Media Library
- Comment Moderation
- SEO Growth Center

### Mail Infrastructure

- Domains
- IMAP/SMTP
- Mailbox Rules
- Blocked Lists

### Growth

- Plans & Memberships
- API Access
- Integrations

### People

- People & Identity
- Roles & Permissions
- Author Profiles

### Brand

- Theme Launch Center
- Appearance Studio
- Typography Center

### Trust

- Security Defense Center
- Abuse Reports
- Activity & Audit Logs

### System

- Update Center
- Notifications
- Email Templates
- Backups & Health
- Settings

## Locked Module Decisions

### Install / First-run Setup

- Must be shared-hosting friendly.
- Must create or repair `.env` when needed.
- Must test database connection before admin creation.
- Must create installer lock after successful setup.
- Must not show default Laravel screens.

### Locale Launch Center

Does:

- manage locales/markets
- manage active/passive state
- manage default locale
- manage direction LTR/RTL
- show readiness and launch status

Does not:

- manage homepage text
- manage translations directly
- act like a simple language settings table

### Translation Center

Does:

- manage predefined UI/source keys
- use English as canonical source
- allow per-locale values for source keys

Does not:

- manage blog posts
- manage pages
- manage sections
- replace real content records

### Blog Studio

Does:

- language-specific posts
- language-specific categories
- language-specific tags
- featured images from Media Library
- draft, publish, preview, trash, restore
- WordPress-like publishing workflow

Does not:

- create `post_translations`
- create translation relationships between posts
- manage homepage sections

### Page Studio

Does:

- language-specific pages
- legal pages
- preview
- draft, publish, trash, restore
- media integration

Does not:

- create `page_translations`
- manage global SEO directly
- manage homepage sections directly

### Sections Studio

Does:

- language-specific sections
- CTA, FAQ, blog teaser, trust blocks
- drag/drop order
- active/passive items
- trash/restore where needed

Does not:

- translate sections
- manage Header/Footer as editable sections

Header and Footer are theme-owned fixed partials.

### Media Library

Owns:

- uploaded images
- featured images
- page media
- post media
- section media
- SEO/OG images
- avatars

### SEO Growth Center

Does:

- target-specific SEO records
- language-specific SEO metadata
- schema
- sitemap controls
- robots controls
- OG/Twitter previews

Does not:

- own page/blog content

### Theme Launch Center

Does:

- manage Horizon, Atlas, and Legacy
- preview themes
- activate one theme

Rules:

- only one active theme
- themes affect public website only
- admin layout is never theme-controlled
- themes are not deleted

### Appearance Studio

Does:

- safe visual tokens
- brand color
- accent color
- radius
- shadow
- motion
- reset to theme defaults
- draft/publish

Does not:

- edit layout
- allow arbitrary custom CSS

### Typography Center

Does:

- global fonts
- theme fonts
- language-specific font overrides
- RTL/LTR font coverage
- Google Fonts registry
- self-hosted font readiness

### Security Defense Center

Does:

- Cloudflare Turnstile
- Google reCAPTCHA
- Akismet for comments/contact
- rate limits
- admin access security
- abuse monitoring

### Email Templates

Does:

- language-specific system email templates
- safe placeholders
- preview
- send test
- reset default

Does not:

- act as a newsletter builder
- allow arbitrary PHP/Blade execution

## Lifecycle Rules

Use these states where applicable:

- draft
- active
- hidden
- published
- scheduled
- trashed
- archived

Trash/restore is required for:

- blog posts
- pages
- comments
- media
- mailbox records where applicable

Permanent delete:

- owner/admin only
- audit logged
- never the default action

## Security Rules

- Normal users cannot access admin.
- Roles and plans are separate.
- Owner cannot be deleted.
- Last owner/admin cannot be deleted.
- Users cannot remove their own critical access accidentally.
- Suspended users cannot login.
- Secrets must be encrypted or masked.
- Passwords, tokens, API keys, and SMTP passwords must never be logged.
- API secrets are shown only once.
- Backups must not be public.
- `.env` must not be overwritten by updates.

## MVP vs Later

### MVP

- Installer
- Admin shell/sidebar
- Ctrl+K command palette
- Roles/permissions
- Settings
- Audit logs
- Backups & Health
- Update Center
- Locale Launch Center
- Media Library
- Page Studio
- Blog Studio
- Sections Studio
- SEO Growth Center
- Email Templates
- Notifications
- Security Defense Center
- Mailbox Operations
- Plans & Memberships
- API Access
- Product Analytics
- Themes
- Appearance
- Typography
- Integrations registry

### Later

- WebSockets
- full developer portal
- OAuth app marketplace
- full payment processor automation
- full newsletter builder
- complex automatic restore
- AI writing tools
- A/B testing
- staged rollout updates
- enterprise automation workflows

## Definition Of Done

Every module part must report:

- implemented scope
- intentionally deferred scope
- changed files
- blueprint compliance
- tests/build result
- any conflict with this blueprint

A module part is not done unless:

- routes are named
- controllers are thin
- validation uses Form Requests
- permissions use Policies/Gates where needed
- business logic is in Services/Actions
- Blade components are used appropriately
- no raw standalone HTML exists
- no hardcoded URLs/assets exist
- validation errors are accessible
- critical actions are audit logged
- tests are added or updated where meaningful

## Commit Strategy

One prompt should equal one focused commit.

Good commit examples:

- `Add project blueprint`
- `Build installer foundation`
- `Add admin shell and command palette`
- `Add blog studio foundation`
- `Add blog publishing workflow`
- `Add media library foundation`
- `Add SEO growth center foundation`

Avoid giant commits that mix unrelated modules.
