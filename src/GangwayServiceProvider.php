<?php

namespace Gangway\Laravel;

use Gangway\Laravel\Console\PingCommand;
use Gangway\Laravel\Http\Controllers\WebhookController;
use Gangway\Laravel\Http\Middleware\VerifyGangwayWebhook;
use Gangway\Laravel\View\Components\Widget;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class GangwayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/gangway.php', 'gangway');

        $this->app->singleton(Client::class, function ($app): Client {
            return new Client($app['config']->get('gangway', []));
        });

        $this->app->singleton(GangwayManager::class, function ($app): GangwayManager {
            return new GangwayManager($app->make(Client::class));
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'gangway');

        $this->publishes([
            __DIR__.'/../config/gangway.php' => config_path('gangway.php'),
        ], 'gangway-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/gangway'),
        ], 'gangway-views');

        Blade::component('gangway-widget', Widget::class);
        Blade::directive('gangwayWidget', function (string $expression): string {
            return "<?php echo view('gangway::widget', ['token' => {$expression}, 'attributes' => new \\Illuminate\\View\\ComponentAttributeBag()])->render(); ?>";
        });

        $this->app['router']->aliasMiddleware('gangway.webhook', VerifyGangwayWebhook::class);

        if ($this->app->runningInConsole()) {
            $this->commands([PingCommand::class]);
        }

        if (config('gangway.webhook.enabled')) {
            Route::middleware((array) config('gangway.webhook.middleware', ['api', 'gangway.webhook']))
                ->post(config('gangway.webhook.path', 'webhooks/gangway'), WebhookController::class)
                ->name('gangway.webhook');
        }
    }
}
