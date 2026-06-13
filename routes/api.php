<?php

use App\Http\Controllers\Api\V1\DomainController;
use App\Http\Controllers\Api\V1\MailboxController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\UsageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['api.key', 'api.usage'])
    ->name('api.v1.')
    ->group(function (): void {
        Route::get('mailboxes', [MailboxController::class, 'index'])
            ->middleware('api.scope:mailbox:read')
            ->name('mailboxes.index');
        Route::post('mailboxes', [MailboxController::class, 'store'])
            ->middleware('api.scope:mailbox:create')
            ->name('mailboxes.store');
        Route::get('mailboxes/{mailbox}', [MailboxController::class, 'show'])
            ->middleware('api.scope:mailbox:read')
            ->name('mailboxes.show');
        Route::delete('mailboxes/{mailbox}', [MailboxController::class, 'destroy'])
            ->middleware('api.scope:mailbox:delete')
            ->name('mailboxes.destroy');
        Route::get('mailboxes/{mailbox}/messages', [MessageController::class, 'index'])
            ->middleware('api.scope:message:read')
            ->name('mailboxes.messages.index');
        Route::get('mailboxes/{mailbox}/messages/{message}', [MessageController::class, 'show'])
            ->middleware('api.scope:message:read')
            ->name('mailboxes.messages.show');
        Route::get('domains', [DomainController::class, 'index'])
            ->middleware('api.scope:domain:read')
            ->name('domains.index');
        Route::get('usage', [UsageController::class, 'show'])
            ->middleware('api.scope:usage:read')
            ->name('usage.show');
    });
