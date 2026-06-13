<?php

namespace App\Services\PublicSite;

use App\Services\Installer\InstallState;
use Illuminate\Http\RedirectResponse;

class PublicEntryRedirector
{
    public function __construct(
        private readonly InstallState $installState,
        private readonly PublicLocaleResolver $locales,
    ) {}

    public function response(): RedirectResponse
    {
        if (! $this->installState->isInstalled()) {
            return redirect()->route('install.readiness');
        }

        $locale = $this->locales->default();

        return $locale
            ? redirect()->route('public.home', ['locale' => $locale->locale])
            : redirect()->route('login');
    }
}
