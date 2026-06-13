<?php

namespace App\Services\PublicSite;

use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class PublicThemeRenderer
{
    /** @param array<string, mixed> $data */
    public function home(array $data): View
    {
        $view = $data['theme']['public_path'].'.home';

        abort_unless(ViewFacade::exists($view), 404);

        return view($view, $data);
    }

    /** @param array<string, mixed> $data */
    public function mailbox(array $data): View
    {
        $view = $data['theme']['public_path'].'.mailbox';

        abort_unless(ViewFacade::exists($view), 404);

        return view($view, $data);
    }
}
