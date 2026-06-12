<?php

namespace App\Http\Controllers\Admin;

use App\Actions\EmailTemplates\CreateEmailTemplateAction;
use App\Actions\EmailTemplates\ResetEmailTemplateAction;
use App\Actions\EmailTemplates\UpdateEmailTemplateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmailTemplates\EmailTemplateFilterRequest;
use App\Http\Requests\EmailTemplates\StoreEmailTemplateRequest;
use App\Http\Requests\EmailTemplates\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Services\EmailTemplates\EmailTemplateStore;
use App\Services\EmailTemplates\EmailTemplateVariableRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function index(EmailTemplateFilterRequest $request, EmailTemplateStore $store): View
    {
        $filters = [
            'locale_id' => (string) $request->query('locale_id', 'all'),
            'template_key' => (string) $request->query('template_key', 'all'),
            'status' => (string) $request->query('status', 'all'),
            'missing' => (string) $request->query('missing', 'all'),
        ];

        return view('dashboard.email-templates.index', [
            'adminUser' => $request->user(),
            'templates' => $store->search([...$request->validated(), ...$filters]),
            'summary' => $store->summary(),
            'filters' => $filters,
            'locales' => $store->locales(),
            'templateKeys' => $store->templateKeys(),
            'statuses' => $store->statuses(),
            'missingQueue' => $store->missingQueue()->take(12),
            'canCreateTemplate' => $request->user()?->can('admin.email-templates.create') ?? false,
            'canUpdateTemplate' => $request->user()?->can('admin.email-templates.update') ?? false,
        ]);
    }

    public function create(Request $request, EmailTemplateStore $store, EmailTemplateVariableRegistry $variables): View
    {
        $request->user()?->can('admin.email-templates.create') || abort(403);

        return view('dashboard.email-templates.create', [
            'adminUser' => $request->user(),
            'template' => null,
            'editor' => $this->editorData($store, $variables),
        ]);
    }

    public function store(StoreEmailTemplateRequest $request, CreateEmailTemplateAction $create): RedirectResponse
    {
        $template = $create->handle($request->user(), $request->validated());

        return redirect()
            ->route('admin.email-templates.edit', $template)
            ->with('status', 'Email template created.');
    }

    public function edit(Request $request, EmailTemplate $emailTemplate, EmailTemplateStore $store, EmailTemplateVariableRegistry $variables): View
    {
        $request->user()?->can('admin.email-templates.update') || abort(403);

        return view('dashboard.email-templates.edit', [
            'adminUser' => $request->user(),
            'template' => $emailTemplate->load(['locale', 'updater']),
            'editor' => $this->editorData($store, $variables),
        ]);
    }

    public function update(UpdateEmailTemplateRequest $request, EmailTemplate $emailTemplate, UpdateEmailTemplateAction $update): RedirectResponse
    {
        $update->handle($request->user(), $emailTemplate, $request->validated());

        return redirect()
            ->route('admin.email-templates.edit', $emailTemplate)
            ->with('status', 'Email template updated.');
    }

    public function reset(Request $request, EmailTemplate $emailTemplate, ResetEmailTemplateAction $reset): RedirectResponse
    {
        $request->user()?->can('admin.email-templates.reset') || abort(403);
        $result = $reset->readiness($request->user(), $emailTemplate);

        return redirect()
            ->route('admin.email-templates.edit', $emailTemplate)
            ->with('status', $result['message']);
    }

    /** @return array<string, mixed> */
    private function editorData(EmailTemplateStore $store, EmailTemplateVariableRegistry $variables): array
    {
        return [
            'locales' => $store->locales(),
            'templateKeys' => $store->templateKeys(),
            'statuses' => $store->statuses(),
            'variables' => $variables->variables(),
            'required' => $variables->requiredByKey(),
        ];
    }
}
