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

    /** @param array<string, mixed> $data */
    public function blogIndex(array $data): View
    {
        return $this->render($data, 'blog.index');
    }

    /** @param array<string, mixed> $data */
    public function blogShow(array $data): View
    {
        return $this->render($data, 'blog.show');
    }

    /** @param array<string, mixed> $data */
    public function blogCategory(array $data): View
    {
        return $this->render($data, 'blog.category');
    }

    /** @param array<string, mixed> $data */
    public function blogTag(array $data): View
    {
        return $this->render($data, 'blog.tag');
    }

    /** @param array<string, mixed> $data */
    public function blogAuthor(array $data): View
    {
        return $this->render($data, 'blog.author');
    }

    /** @param array<string, mixed> $data */
    public function page(array $data): View
    {
        return $this->render($data, 'pages.show');
    }

    /** @param array<string, mixed> $data */
    private function render(array $data, string $view): View
    {
        $path = $data['theme']['public_path'].'.'.$view;

        abort_unless(ViewFacade::exists($path), 404);

        return view($path, $data);
    }
}
