<?php

namespace App\Modules\Home\Http\Controllers\Web;

use App\Http\Controllers\WebController;
use App\Support\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class HomeController extends WebController
{
    protected $module = 'home';

    public function show(): View|JsonResponse
    {
        [$locale, $slug] = Site::resolve(request()->path());
        $view = config('site.pages')[$slug] ?? null;
        abort_unless($view, 404);
        app()->setLocale($locale);

        return $this->responseModule([], $view);
    }
}
