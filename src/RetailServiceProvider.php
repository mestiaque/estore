<?php
namespace ME\Retail;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use ME\Retail\Http\Middleware\EnsureRetailFeature;
use ME\Retail\Support\RetailModules;

class RetailServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app['router']->aliasMiddleware('retail.feature', EnsureRetailFeature::class);

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'retail');
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'retail');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->publishes([__DIR__ . '/public' => public_path('/')], 'retail-assets');

        // @feature('barcode') … @endfeature — renders only while that feature is switched on.
        Blade::if('feature', fn (string $feature) => RetailModules::enabled($feature));

        // Sidebar entries are a numeric array — must array_merge, not mergeConfigFrom
        if (file_exists($sidebar = __DIR__ . '/Config/sidebar.php')) {
            Config::set('sidebar', array_merge(
                config('sidebar', []),
                RetailModules::filterSidebar(require $sidebar)
            ));
        }
    }

    public function register(): void
    {
        if (file_exists(__DIR__ . '/Config/config.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/config.php', 'retail');
        }

        if (file_exists(__DIR__ . '/Config/features.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/features.php', 'retail_features');
        }

        if (file_exists(__DIR__ . '/Config/settings.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/settings.php', 'retail_settings');
        }

        if (file_exists(__DIR__ . '/Config/business_types.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/business_types.php', 'retail_business_types');
        }

        if (file_exists(__DIR__ . '/Config/permission.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/permission.php', 'permissions');
        }
    }
}
