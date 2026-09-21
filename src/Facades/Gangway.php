<?php

namespace Gangway\Laravel\Facades;

use Gangway\Laravel\GangwayManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Gangway\Laravel\GangwayManager using(?string $apiKey = null, ?string $baseUrl = null)
 * @method static \Gangway\Laravel\Client client()
 * @method static \Gangway\Laravel\Responses\HttpMeta|null lastMeta()
 * @method static \Gangway\Laravel\Responses\ApiObject ping()
 * @method static \Gangway\Laravel\Responses\ApiObject me()
 * @method static \Gangway\Laravel\Responses\ApiObject capabilities()
 * @method static \Gangway\Laravel\Resources\Discovery discovery()
 * @method static \Gangway\Laravel\Resources\Products products()
 * @method static \Gangway\Laravel\Resources\Events events()
 * @method static \Gangway\Laravel\Resources\Programs programs()
 * @method static \Gangway\Laravel\Resources\Availability availability()
 * @method static \Gangway\Laravel\Resources\Bookings bookings()
 * @method static \Gangway\Laravel\Resources\Tickets tickets()
 * @method static \Gangway\Laravel\Resources\Equipment equipment()
 * @method static \Gangway\Laravel\Resources\Invoices invoices()
 * @method static \Gangway\Laravel\Resources\Customers customers()
 * @method static \Gangway\Laravel\Resources\Waivers waivers()
 * @method static \Gangway\Laravel\Resources\Webhooks webhooks()
 * @method static \Gangway\Laravel\Resources\Widgets widgets()
 * @method static \Gangway\Laravel\Webhooks\WebhookVerifier verifier(?string $secret = null)
 * @method static string widgetScriptUrl()
 * @method static string widgetEmbedUrl(string $token)
 *
 * @see GangwayManager
 */
class Gangway extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GangwayManager::class;
    }
}
