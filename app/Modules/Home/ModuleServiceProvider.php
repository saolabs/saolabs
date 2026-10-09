<?php

namespace App\Modules\Home;

use App\Modules\Home\Http\Controllers\Web\HomeController;
use App\Support\Site;
use Saola\Core\Providers\ModuleServiceProvider as CoreModuleServiceProvider;
use Saola\Core\System;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    public function routes(): void
    {
        System::context('web')->module(['slug' => 'home', 'prefix' => '/', 'priority' => 1])
            ->controller(HomeController::class)
            ->as('home')
            ->group(function ($module) {
                foreach (config('site.pages') as $slug => $view) {
                    foreach (array_keys(config('site.locales')) as $locale) {
                        $module->get(Site::url($slug, $locale), 'show')
                            ->name($view.'.'.$locale)->viewModule($view);
                    }
                }
            });
    }
}
