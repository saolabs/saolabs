<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Saola\Core\System;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register contexts and discover module providers.
     */
    public function register(): void
    {
        System::context('web', [
            'as' => 'web',
            'display_name' => 'Web',
            'slug' => 'web',
            'middleware' => ['web'],
            'prefix' => '/',
        ]);

        // Auto-discover and register module providers under app/Modules.
        $this->registerModuleProviders();
    }

    public function boot(): void
    {
        // Context routes are registered by each module's ModuleServiceProvider::routes()
    }

    protected const INTERNAL_DIRS = [
        'Http', 'Models', 'Repositories', 'Services', 'Masks',
        'Commands', 'Jobs', 'Events', 'Listeners', 'Notifications',
        'Policies', 'Rules', 'Observers', 'Resources', 'Requests',
        'Providers', 'Controllers', 'Middleware', 'Views',
    ];

    /**
     * Recursively register module service providers.
     */
    protected function registerModuleProviders(): void
    {
        $modulesPath = app_path('Modules');

        if (!is_dir($modulesPath)) {
            return;
        }

        $this->registerModuleProvidersInPath($modulesPath, 'App\\Modules');
    }

    protected function registerModuleProvidersInPath(string $path, string $namespace): void
    {
        foreach (glob($path . '/*', GLOB_ONLYDIR) as $moduleDir) {
            $moduleName = basename($moduleDir);

            if ($this->isInternalDirectory($moduleName)) {
                continue;
            }

            $moduleNamespace = $namespace . '\\' . $moduleName;
            $providerClass = $moduleNamespace . '\\ModuleServiceProvider';

            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }

            $this->registerModuleProvidersInPath($moduleDir, $moduleNamespace);
        }
    }

    protected function isInternalDirectory(string $directoryName): bool
    {
        return in_array($directoryName, static::INTERNAL_DIRS, true);
    }
}
