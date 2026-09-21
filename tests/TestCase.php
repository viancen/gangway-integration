<?php

namespace Gangway\Laravel\Tests;

use Gangway\Laravel\GangwayServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [GangwayServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('gangway.base_url', 'https://gangway.test');
        $app['config']->set('gangway.api_key', 'gw_test_key');
        $app['config']->set('gangway.webhook_secret', 'whsec_test');
        $app['config']->set('gangway.idempotency.auto', true);
        $app['config']->set('gangway.retry.times', 0);
        $app['config']->set('gangway.webhook.enabled', false);
    }
}
