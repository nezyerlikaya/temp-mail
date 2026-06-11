# Module Prompt Guide

This guide defines how to request work without causing scope drift between prompts.

Read this together with `docs/PROJECT_BLUEPRINT.md`.

## Core Rule

Each prompt should implement one focused part of one module.

Do not ask for an entire large module in one prompt unless the module is very small.

Prompt equals implementation step.

Implementation step equals focused commit.

## Why This Exists

Large modules can drift when split into several prompts.

Example risk:

- prompt 1 says Blog posts are language-specific
- prompt 3 accidentally asks for `post_translations`

To prevent this, every prompt must follow the locked decisions in `docs/PROJECT_BLUEPRINT.md`.

## Required Prompt Header

Use this at the start of every coding prompt:

```text
Senior sir, first read docs/PROJECT_BLUEPRINT.md and follow it exactly.

Also read the relevant guide files:
- docs/guides/BLADE_COMPONENTS.md
- docs/guides/UI_UX_STANDARDS.md

Build [Module Name] Part [X] of [Y]: [Part Name].

This project has locked architecture.
Do not reinterpret previous decisions.
Do not change module boundaries.
Do not introduce conflicting data models.
Do not create raw standalone HTML pages.
Do not hardcode URLs.
Do not put business logic in Blade.
```

## Required Prompt Scope

Every prompt must include:

```text
Scope for this part:
- ...

Out of scope:
- ...

At the end, report:
- implemented scope
- intentionally deferred scope
- changed files
- blueprint compliance
- tests/build results
- any blueprint conflict
```

## Module Splitting Rules

Small modules can be one prompt:

- Settings
- Notifications MVP
- Audit Logs MVP
- Theme Launch Center
- Appearance Studio
- Typography Center
- Integrations registry

Large modules must be split:

- Blog Studio
- Page Studio
- Media Library
- SEO Growth Center
- Mailbox Operations
- People & Identity
- Security Defense Center
- API Access

## Example: Blog Studio Split

### Part 1: Foundation

- migrations
- models
- language-specific relationships
- status fields
- slug fields
- media reference fields
- routes
- base controllers
- services/actions
- policies
- base tests

Out of scope:

- full editor UI
- SEO UI
- comments UI

### Part 2: Post Editor

- editor UI
- title, slug, excerpt, content
- featured image picker placeholder
- draft/publish
- preview
- validation errors
- Blade components

Out of scope:

- taxonomy management screens
- scheduled publishing
- comment moderation

### Part 3: Taxonomy

- categories
- tags
- post-category/tag relationships
- language-specific taxonomy
- filters/search
- taxonomy tests

Out of scope:

- SEO editor
- media library internals

### Part 4: Publishing Workflow

- trash/restore
- scheduled publish readiness
- status transitions
- audit log
- SEO/media integration hooks
- final tests

## Example: Safe Blog Prompt

```text
Senior sir, first read docs/PROJECT_BLUEPRINT.md and follow it exactly.

Also read:
- docs/guides/BLADE_COMPONENTS.md
- docs/guides/UI_UX_STANDARDS.md

Build Blog Studio Part 1 of 4: Foundation.

Follow the Blog Studio locked decisions exactly:
- Blog posts are language-specific records.
- No post_translations.
- No translation relationships.
- Categories and tags are language-specific.
- Media Library owns featured images.

Scope for this part:
- migrations/models for posts, categories, tags
- language relationships
- status fields
- slug fields
- media reference fields
- routes
- base controllers
- services/actions
- policies
- base views/components skeleton
- tests for foundation

Out of scope:
- full editor UI
- SEO UI
- comments UI

At the end, report:
- implemented scope
- intentionally deferred scope
- changed files
- blueprint compliance
- tests/build results
- any blueprint conflict
```

## Conflict Rule

If a prompt conflicts with `docs/PROJECT_BLUEPRINT.md`, do not guess.

Stop and report:

- the conflict
- the blueprint rule
- the safer path

## Commit Message Rule

Use focused commit messages:

- `Add project blueprint`
- `Build admin shell foundation`
- `Add mailbox operations foundation`
- `Add blog studio foundation`
- `Add blog publishing workflow`

Avoid:

- `Update everything`
- `Fix stuff`
- `Add dashboard and blog and SEO and users`

## Final Rule

The prompt must not be bigger than the commit.

If the prompt contains unrelated modules, split it.
