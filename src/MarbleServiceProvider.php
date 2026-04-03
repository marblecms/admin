<?php

namespace Marble\Admin;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Marble\Admin\Models\Language;
use Marble\Admin\MarbleRouter;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Models\MarbleSetting;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\User;
use Marble\Admin\Models\UserGroup;
use Marble\Admin\Policies\BlueprintPolicy;
use Marble\Admin\Policies\ItemPolicy;
use Marble\Admin\Policies\UserGroupPolicy;
use Marble\Admin\Policies\UserPolicy;

class MarbleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/Config/marble.php', 'marble');

        // Register the FieldTypeRegistry as singleton
        $this->app->singleton(FieldTypeRegistry::class, function () {
            return new FieldTypeRegistry();
        });

        // Register the MarbleManager as singleton
        $this->app->singleton('marble', function ($app) {
            return new MarbleManager($app->make(FieldTypeRegistry::class));
        });

        // Register the portal auth guard
        $this->app['auth']->extend('marble-portal', function ($app, $name, array $config) {
            return $app['auth']->createSessionDriver($name, $config);
        });

        $this->app['auth']->provider('marble-portal-provider', function ($app, array $config) {
            return new \Illuminate\Auth\EloquentUserProvider($app['hash'], $config['model']);
        });
    }

    public function boot(): void
    {
        $this->mergeDbSettings();
        $this->registerFieldTypes();
        $this->registerMigrations();
        $this->registerRouteMacros(); // must be before registerRoutes (auto_routing uses Route::marble())
        $this->registerRoutes();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerPublishables();
        $this->registerModelEvents();
        $this->registerPolicies();
        $this->registerCommands();
        $this->registerComponents();
        $this->registerPortalGuard();
        $this->app['router']->pushMiddlewareToGroup('web', \Marble\Admin\Http\Middleware\DetectMarbleSite::class);
        $this->app['router']->pushMiddlewareToGroup('web', \Marble\Admin\Http\Middleware\HandleMarbleRedirects::class);
        $this->app['router']->pushMiddlewareToGroup('web', \Marble\Admin\Http\Middleware\InjectMarbleDebugbar::class);

        // Flush request-scoped caches after each request (Octane compatibility)
        $this->app->terminating(function () {
            Language::flushCache();
        });
    }

    protected function mergeDbSettings(): void
    {
        try {
            $map = [
                'frontend_url'       => 'marble.frontend_url',
                'primary_locale'     => 'marble.primary_locale',
                'uri_locale_prefix'  => 'marble.uri_locale_prefix',
                'autosave'           => 'marble.autosave',
                'autosave_interval'  => 'marble.autosave_interval',
                'lock_ttl'           => 'marble.lock_ttl',
                'cache_ttl'          => 'marble.cache_ttl',
            ];

            $settings = MarbleSetting::allKeyed();

            foreach ($map as $dbKey => $configKey) {
                if (array_key_exists($dbKey, $settings)) {
                    $value = $settings[$dbKey];
                    // Cast booleans
                    if (in_array($dbKey, ['uri_locale_prefix', 'autosave'])) {
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    }
                    // Cast integers
                    if (in_array($dbKey, ['autosave_interval', 'lock_ttl', 'cache_ttl'])) {
                        $value = (int) $value;
                    }
                    config([$configKey => $value]);
                }
            }
        } catch (\Exception $e) {
            // Table may not exist yet during install
        }
    }

    protected function registerFieldTypes(): void
    {
        $registry = $this->app->make(FieldTypeRegistry::class);

        $builtInTypes = [
            FieldTypes\Textfield::class,
            FieldTypes\Textblock::class,
            FieldTypes\Htmlblock::class,
            FieldTypes\Selectbox::class,
            FieldTypes\Checkbox::class,
            FieldTypes\Date::class,
            FieldTypes\Datetime::class,
            FieldTypes\Time::class,
            FieldTypes\Image::class,
            FieldTypes\Images::class,
            FieldTypes\ObjectRelation::class,
            FieldTypes\ObjectRelationList::class,
            FieldTypes\KeyValueStore::class,
            FieldTypes\Repeater::class,
            FieldTypes\File::class,
            FieldTypes\Files::class,
        ];

        foreach ($builtInTypes as $typeClass) {
            $registry->register(new $typeClass());
        }
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }

    protected function registerRoutes(): void
    {
        $prefix = config('marble.route_prefix', 'admin');

        // Admin routes (authenticated)
        Route::middleware(['web', \Marble\Admin\Http\Middleware\MarbleAuthenticate::class, \Marble\Admin\Http\Middleware\SetMarbleGuard::class])
            ->prefix($prefix)
            ->as('marble.')
            ->group(__DIR__ . '/Http/routes.php');

        // Auth routes (login/logout, no auth required)
        Route::middleware(['web'])
            ->prefix($prefix)
            ->as('marble.')
            ->group(__DIR__ . '/Http/auth_routes.php');

        // Field type AJAX routes
        Route::middleware(['web', \Marble\Admin\Http\Middleware\MarbleAuthenticate::class, \Marble\Admin\Http\Middleware\SetMarbleGuard::class])
            ->prefix($prefix . '/api')
            ->as('marble.')
            ->group(function () {
                $this->app->make(FieldTypeRegistry::class)->registerRoutes();
            });
        
        // Frontend form submission (public, no auth)
        Route::middleware(['web'])
            ->post('/marble-form/{item}', [\Marble\Admin\Http\Controllers\FormController::class, 'submit'])
            ->name('marble.form.submit');

        // Draft preview (public, token-gated)
        Route::middleware(['web'])
            ->get('/marble-preview/{token}', [\Marble\Admin\Http\Controllers\PreviewController::class, 'show'])
            ->name('marble.preview');

        // Headless JSON API
        Route::middleware([\Marble\Admin\Http\Middleware\MarbleApiAuth::class])
            ->prefix('api/marble')
            ->as('marble.api.')
            ->group(function () {
                Route::get('items/{blueprint}', [\Marble\Admin\Http\Controllers\Api\ItemApiController::class, 'items'])->name('items');
                Route::get('item/{id}', [\Marble\Admin\Http\Controllers\Api\ItemApiController::class, 'show'])->name('item');
                Route::get('item/{id}/children', [\Marble\Admin\Http\Controllers\Api\ItemApiController::class, 'children'])->name('item.children');
                Route::get('resolve', [\Marble\Admin\Http\Controllers\Api\ItemApiController::class, 'resolve'])->name('resolve');
            });

        // Image/file serving routes — must be registered BEFORE any catch-all
        Route::middleware(['web'])
            ->group(function () {
                Route::get('/image/{width}/{height}/{filename}', [\Marble\Admin\Http\Controllers\ImageController::class, 'showResized'])
                    ->where(['width' => '[0-9]+', 'height' => '[0-9]+', 'filename' => '.*'])
                    ->name('marble.image.resized');
                Route::get('/image/{filename}', [\Marble\Admin\Http\Controllers\ImageController::class, 'show'])
                    ->where('filename', '[^/]+')
                    ->name('marble.image');
                Route::get('/file/{filename}', [\Marble\Admin\Http\Controllers\FileController::class, 'show'])
                    ->where('filename', '[^/]+')
                    ->name('marble.file');
            });

        // Auto-routing: catch-all frontend route (opt-in via config)
        if (config('marble.auto_routing', false)) {
            Route::middleware(['web'])
                ->group(function () {
                    Route::marble();
                });
        }
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'marble');
    }

    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/Resources/lang', 'marble');
    }

    protected function registerPublishables(): void
    {
        if ($this->app->runningInConsole()) {
            // Config
            $this->publishes([
                __DIR__ . '/Config/marble.php' => config_path('marble.php'),
            ], 'marble-config');

            // Assets
            $this->publishes([
                __DIR__ . '/Resources/assets' => public_path('vendor/marble'),
            ], 'marble-assets');

            // Views (for customization)
            $this->publishes([
                __DIR__ . '/Resources/views' => resource_path('views/vendor/marble'),
            ], 'marble-views');
        }
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Item::class, ItemPolicy::class);
        Gate::policy(Blueprint::class, BlueprintPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(UserGroup::class, UserGroupPolicy::class);
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Marble\Admin\Console\InstallCommand::class,
                \Marble\Admin\Console\SyncFieldTypesCommand::class,
                \Marble\Admin\Console\SitemapCommand::class,
                \Marble\Admin\Console\ExportCommand::class,
                \Marble\Admin\Console\SchedulePublishCommand::class,
                \Marble\Admin\Console\WorkflowDeadlineCommand::class,
                \Marble\Admin\Console\MakeBlueprintCommand::class,
                \Marble\Admin\Console\DoctorCommand::class,
                \Marble\Admin\Console\PruneCommand::class,
            ]);
        }
    }

    protected function registerComponents(): void
    {
        Blade::componentNamespace('Marble\\Admin\\Components', 'marble');
    }

    protected function registerRouteMacros(): void
    {
        Route::macro('marble', function (string $uri = '{marbleSlug?}', ?callable $handler = null) {
            return Route::get($uri, function (string $marbleSlug = '') use ($handler) {
                // Set site language as default if configured
                $site = \Marble\Admin\Models\Site::current();
                if ($site && $site->default_language_id) {
                    app('marble')->setLanguageById($site->default_language_id);
                }

                $item = MarbleRouter::resolve('/' . $marbleSlug);

                if (!$item || !$item->isPublished()) {
                    abort(404);
                }

                if ($handler) {
                    return $handler($item);
                }

                $view = \Marble\Admin\Facades\Marble::viewFor($item);

                return view($view, compact('item'));
            })->where('marbleSlug', '.*');
        });
    }

    protected function registerModelEvents(): void
    {
        // Auto-invalidate cache when items are saved or deleted
        Item::saved(function (Item $item) {
            $this->app->make('marble')->invalidateItem($item);

            // Fix materialized path after create (id not known during creating)
            if ($item->wasRecentlyCreated) {
                $item->fixPathAfterCreate();
            }
        });

        Item::deleted(function (Item $item) {
            $this->app->make('marble')->invalidateItem($item);
        });
    }

    protected function registerPortalGuard(): void
    {
        // Add the portal guard and provider to the auth config at runtime
        config([
            'auth.guards.portal' => [
                'driver'   => 'session',
                'provider' => 'portal_users',
            ],
            'auth.providers.portal_users' => [
                'driver' => 'eloquent',
                'model'  => \Marble\Admin\Models\PortalUser::class,
            ],
        ]);

        // Portal auth routes (public)
        Route::middleware(['web'])
            ->prefix('portal')
            ->as('marble.portal.')
            ->group(__DIR__ . '/Http/portal_routes.php');

        // Register middleware alias
        $this->app['router']->aliasMiddleware('marble.portal.auth', \Marble\Admin\Http\Middleware\RequirePortalAuth::class);
    }
}
