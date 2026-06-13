<?php

namespace App\Http\Controllers;

use App\Actions\PublicSite\CreatePublicMailboxAction;
use App\Actions\PublicSite\RefreshPublicInboxAction;
use App\Http\Requests\PublicSite\CreatePublicMailboxRequest;
use App\Http\Requests\PublicSite\RefreshPublicInboxRequest;
use App\Http\Requests\PublicSite\ViewPublicMailboxRequest;
use App\Models\Mailbox;
use App\Models\MailboxMessage;
use App\Services\PublicSite\PublicMailboxViewDataService;
use App\Services\PublicSite\PublicThemeRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicMailboxController extends Controller
{
    public function store(CreatePublicMailboxRequest $request, CreatePublicMailboxAction $action): RedirectResponse
    {
        $result = $action->handle($request);

        return redirect()->to($result['url'])
            ->with('status', 'Mailbox created. Your private inbox is ready.');
    }

    public function show(
        ViewPublicMailboxRequest $request,
        string $locale,
        Mailbox $mailbox,
        PublicMailboxViewDataService $viewData,
        PublicThemeRenderer $renderer,
    ): View {
        return $renderer->mailbox($viewData->show(
            $mailbox,
            $request->attributes->get('public_locale'),
            $request->attributes->get('public_theme'),
            $request->attributes->get('public_access_token'),
        ));
    }

    public function message(
        ViewPublicMailboxRequest $request,
        string $locale,
        Mailbox $mailbox,
        MailboxMessage $message,
        PublicMailboxViewDataService $viewData,
        PublicThemeRenderer $renderer,
    ): View {
        return $renderer->mailbox($viewData->show(
            $mailbox,
            $request->attributes->get('public_locale'),
            $request->attributes->get('public_theme'),
            $request->attributes->get('public_access_token'),
            $message,
        ));
    }

    public function refresh(RefreshPublicInboxRequest $request, string $locale, Mailbox $mailbox, RefreshPublicInboxAction $action): RedirectResponse
    {
        $action->handle($mailbox);

        return redirect()->to($request->input('return_to', url()->previous()))
            ->with('status', 'Inbox refreshed.');
    }
}
