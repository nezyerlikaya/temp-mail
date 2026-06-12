<?php

namespace App\Services\Seo;

use App\Models\SeoRecord;
use App\Models\SeoTemplate;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Collection;

class SeoTemplateService
{
    public function __construct(
        private readonly SeoTemplateVariableRegistry $variables,
        private readonly AuditLogger $audit,
    ) {}

    /** @return Collection<int, SeoTemplate> */
    public function templates(): Collection
    {
        return SeoTemplate::query()
            ->with('locale')
            ->orderBy('target_type')
            ->orderByRaw('locale_id is not null')
            ->get();
    }

    /** @param array<string, mixed> $payload */
    public function save(User $actor, array $payload): SeoTemplate
    {
        $template = SeoTemplate::query()->updateOrCreate([
            'target_type' => $payload['target_type'],
            'locale_id' => $payload['locale_id'] ?? null,
        ], [
            'name' => $payload['name'],
            'meta_title_template' => $payload['meta_title_template'] ?? null,
            'meta_description_template' => $payload['meta_description_template'] ?? null,
            'og_title_template' => $payload['og_title_template'] ?? null,
            'og_description_template' => $payload['og_description_template'] ?? null,
            'schema_type' => $payload['schema_type'] ?? null,
            'schema_json_template' => $payload['schema_json_template'] ?? null,
            'is_active' => (bool) ($payload['is_active'] ?? false),
            'updated_by' => $actor->id,
        ]);

        $this->audit->record('seo.template_saved', $actor, null, [
            'seo_template_id' => $template->id,
            'target_type' => $template->target_type,
            'locale_id' => $template->locale_id,
        ], ['module' => 'seo', 'action' => 'Save SEO template', 'target' => $template]);

        return $template;
    }

    /** @return array<string, mixed> */
    public function defaultsFor(SeoRecord $record, array $values): array
    {
        $template = SeoTemplate::query()
            ->where('target_type', $record->target_type)
            ->where('is_active', true)
            ->where(fn ($query) => $query->where('locale_id', $record->locale_id)->orWhereNull('locale_id'))
            ->orderByDesc('locale_id')
            ->first();

        if (! $template) {
            return [];
        }

        return [
            'meta_title' => $record->meta_title ?: $this->variables->render($template->meta_title_template, $values),
            'meta_description' => $record->meta_description ?: $this->variables->render($template->meta_description_template, $values),
            'og_title' => $record->og_title ?: $this->variables->render($template->og_title_template, $values),
            'og_description' => $record->og_description ?: $this->variables->render($template->og_description_template, $values),
            'schema_type' => $record->schema_type ?: $template->schema_type,
            'schema_json' => $record->schema_json ?: $template->schema_json_template,
        ];
    }

    /** @return array<int, string> */
    public function invalidVariables(array $payload): array
    {
        return collect([
            $payload['meta_title_template'] ?? null,
            $payload['meta_description_template'] ?? null,
            $payload['og_title_template'] ?? null,
            $payload['og_description_template'] ?? null,
        ])->flatMap(fn (?string $template): array => $this->variables->invalidVariables($template))->unique()->values()->all();
    }
}
