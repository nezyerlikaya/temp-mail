# Blade Components Guide

This guide controls how Blade views and Blade Components are used in the Temp Mail SaaS project.

Read this together with `docs/PROJECT_BLUEPRINT.md`.

## Core Rule

Blade views compose pages.

Blade components encapsulate reusable, repeated, or clearly independent UI pieces.

Services and Actions own business logic.

## Page Views

Page-level screens must live in:

```text
resources/views/dashboard/{module}/index.blade.php
resources/views/dashboard/{module}/create.blade.php
resources/views/dashboard/{module}/edit.blade.php
```

Page views may compose multiple components.

Example:

```blade
<x-admin.layout>
    <x-admin.page-header title="Blog Studio" />

    <x-admin.card>
        <x-blog.post-editor :post="$post" />
    </x-admin.card>
</x-admin.layout>
```

## Use Blade Components For

Create Blade Components for:

- admin layout
- sidebar
- page header
- cards
- tables
- pagination
- badges
- alerts
- modals
- tabs
- form fields
- toggles
- password fields
- media picker
- status badges
- command palette
- repeated rows
- complex previews
- editors
- empty states

## Do Not Create Components For

Do not create components for:

- one-off small wrappers
- single-use decorative divs
- business decisions
- database queries
- service calls
- controller-like logic

## Component Creation Rule

Create a component only if at least one is true:

- it is reused in multiple places
- it is complex enough to deserve isolation
- it has a clear independent UI responsibility

Do not turn every small `div` into a component.

## Component Responsibilities

Components must:

- receive prepared data
- render UI
- expose simple props
- keep accessibility attributes close to markup
- stay presentation-focused

Components must not:

- query the database
- call services/actions
- contain business rules
- mutate application state directly
- know about unrelated modules

## Component Size Rule

If a component exceeds about 150-200 lines, consider splitting it by responsibility.

If a page view exceeds about 250-300 lines, extract repeated or complex UI into components.

These are guidelines, not blind rules. Readability wins.

## Component Prop Rules

Use simple, consistent prop names.

Good:

```blade
<x-admin.status-badge :status="$post->status" />
<x-blog.post-row :post="$post" />
<x-media.picker :selected="$featuredImage" />
<x-seo.serp-preview :record="$seoRecord" />
```

Avoid:

```blade
<x-blog.post-row
    :post="$post"
    :settings="$settings"
    :permissions="$permissions"
    :all-users="$users"
    :all-categories="$categories"
/>
```

Do not pass large unrelated data bundles into components.

## Naming Rules

- component files use `kebab-case`
- component namespaces match module names
- PHP/database fields use `snake_case`
- JavaScript variables use `camelCase`

Examples:

```text
resources/views/components/admin/page-header.blade.php
resources/views/components/blog/post-row.blade.php
resources/views/components/media/picker.blade.php
resources/views/components/seo/serp-preview.blade.php
```

## Global Admin Components

Use:

```text
resources/views/components/admin/
```

For:

- layout
- sidebar
- topbar/header
- card
- page header
- form field
- table shell
- status badge
- empty state
- modal
- command palette

## Module Components

Use:

```text
resources/views/components/{module}/
```

Examples:

```text
resources/views/components/blog/post-row.blade.php
resources/views/components/blog/post-editor.blade.php
resources/views/components/mailbox/message-preview.blade.php
resources/views/components/seo/serp-preview.blade.php
```

## Accessibility Rules

Components that render forms or interactive UI must support:

- visible focus states
- labels
- `aria-invalid`
- `aria-describedby`
- `role="alert"` for validation errors
- `role="status"` or `aria-live` for async states
- keyboard access

## Final Rule

Keep Blade components small, reusable, and presentation-focused.

Do not create component clutter.

Do not use Blade components as mini controllers.
