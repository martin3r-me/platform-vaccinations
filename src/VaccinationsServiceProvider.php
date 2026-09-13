<?php

namespace Platform\Vaccinations;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Livewire;
use Platform\Core\PlatformCore;
use Platform\Core\Routing\ModuleRouter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class VaccinationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/vaccinations.php', 'vaccinations');
    }

    public function boot(): void
    {
        // Morph-Alias — erbrachte Leistungen (encounter Service) binden per 'vaccination'.
        Relation::morphMap([
            'vaccination' => \Platform\Vaccinations\Models\Vaccination::class,
        ]);

        // Modul registrieren
        if (
            config()->has('vaccinations.routing') &&
            config()->has('vaccinations.navigation') &&
            Schema::hasTable('modules')
        ) {
            PlatformCore::registerModule([
                'key'        => 'vaccinations',
                'title'      => 'Impfungen',
                'group'      => 'catalog',
                'routing'    => config('vaccinations.routing'),
                'guard'      => config('vaccinations.guard'),
                'navigation' => config('vaccinations.navigation'),
                'sidebar'    => config('vaccinations.sidebar'),
            ]);
        }

        if (PlatformCore::getModule('vaccinations')) {
            ModuleRouter::group('vaccinations', function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            });
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../config/vaccinations.php' => config_path('vaccinations.php'),
        ], 'config');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'vaccinations');

        $this->registerLivewireComponents();
    }

    /**
     * Registriert alle Livewire-Komponenten automatisch.
     * src/Livewire/Index.php -> alias vaccinations.index
     */
    protected function registerLivewireComponents(): void
    {
        $basePath = __DIR__ . '/Livewire';
        $baseNamespace = 'Platform\\Vaccinations\\Livewire';
        $prefix = 'vaccinations';

        if (!is_dir($basePath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath));

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $classPath = str_replace(['/', '.php'], ['\\', ''], $relativePath);
            $class = $baseNamespace . '\\' . $classPath;

            if (!class_exists($class)) {
                continue;
            }

            $alias = $prefix . '.' . str_replace('\\', '.', strtolower($classPath));
            Livewire::component($alias, $class);
        }
    }
}
