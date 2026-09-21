# Gangway Laravel

Composer-pakket waarmee een Laravel-website als **operator** tegen de Gangway Operator API v1 praat. Dezelfde catalogus, beschikbaarheid, boekingen, tickets, check-in, finance en webhooks als het Gangway-dashboard — server-side, met scoped API-keys.

Geen OAuth. Authenticatie is een operator-key (`gw_…`) uit **Gangway → Dashboard → Integrations**.

## Installatie

Zodra het pakket op Packagist staat:

```bash
composer require gangway/laravel
```

Tot die tijd, vanuit een Laravel-app via deze GitHub-repo:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/viancen/gangway-integration"
        }
    ]
}
```

```bash
composer require gangway/laravel:dev-main
```

Laravel 11, 12 en 13 worden automatisch ontdekt. Publiceer daarna de config:

```bash
php artisan vendor:publish --tag=gangway-config
```

Zet in `.env`:

```env
GANGWAY_BASE_URL=https://your-gangway-instance.example
GANGWAY_API_KEY=gw_...
GANGWAY_WEBHOOK_SECRET=whsec_...
```

Controleer de verbinding:

```bash
php artisan gangway:ping
```

## Operator-API

```php
use Gangway\Laravel\Facades\Gangway;

$me = Gangway::me();
$operator = $me['operator']['name'];

foreach (Gangway::products()->all(['type' => 'tour']) as $product) {
    // $product['name'], $product['pricing']['base_price'], …
}

$slots = Gangway::availability()->tours([
    'product_id' => 41,
    'from' => now()->toDateString(),
    'to' => now()->addDays(14)->toDateString(),
    'party_size' => 4,
]);

$booking = Gangway::bookings()->create([
    'event_id' => 8842,
    'quantity' => 4,
    'booker_name' => 'Sanne de Vries',
    'booker_email' => 'sanne@example.com',
    'external_booking_reference' => 'SITE-55231',
    'send_confirmation' => true,
]);

Gangway::tickets()->checkIn($booking['tickets'][0]['id']);
```

Schrijfacties sturen automatisch een `Idempotency-Key`. Geef er zelf een mee als dezelfde actie herhaald mag worden zonder dubbele boeking:

```php
Gangway::bookings()->create($payload, idempotencyKey: 'checkout-'.$order->id);
```

Een andere operator of instance:

```php
Gangway::using('gw_other_key', 'https://other.gangway.app')->products()->all();
```

### Resources

| Client | Gangway scopes |
|---|---|
| `Gangway::products()` / `events()` / `programs()` | `catalog.read` / `catalog.write` |
| `Gangway::availability()` | `availability.read` of `catalog.read` |
| `Gangway::bookings()` | `bookings.read` / `bookings.write` |
| `Gangway::tickets()` | `tickets.read` / `tickets.write` / `checkins.write` |
| `Gangway::equipment()` | `equipment.read` / `equipment.write` |
| `Gangway::invoices()` | `finance.read` |
| `Gangway::customers()` | `customers.read` |
| `Gangway::waivers()` | `waivers.read` |
| `Gangway::webhooks()` | `webhooks.manage` |
| `Gangway::widgets()` | key voor de widgetlijst; publieke widget-data heeft geen key nodig |

Paginatie volgt het Gangway-envelope (`data`, `meta`, `links`). Alle pagina’s ophalen:

```php
foreach (Gangway::bookings()->all(['updated_since' => now()->subDay()])->autoPaging() as $booking) {
    //
}
```

Fouten uit de API komen binnen als typed exceptions (`AuthenticationException`, `InsufficientScopeException`, `ValidationException`, `UnprocessableException`, `NotFoundException`, `RateLimitException`, …). Elke exception heeft `errorType`, `requestId` en `details`.

## Booking-widget op je eigen site

Zelfde inbedding als de WordPress-plugin: een token uit Gangway, `widget.js` laadt de iframe-checkout.

```blade
<x-gangway-widget token="your-widget-token" class="my-8" />
```

of

```blade
@gangwayWidget('your-widget-token')
```

Headless, met je eigen UI:

```php
$catalog = Gangway::widgets()->data($token);
$result = Gangway::widgets()->book($token, [
    'event_id' => $eventId,
    'quantity' => 2,
    'booker_name' => $request->name,
    'booker_email' => $request->email,
    'booker_email_confirmation' => $request->email,
]);

if ($checkout = $result['checkout_url'] ?? null) {
    return redirect()->away($checkout);
}
```

## Webhooks

Gangway tekent deliveries met `HMAC-SHA256(secret, "{timestamp}.{body}")` in de header `Gangway-Signature: t=…,v1=…`.

Eigen route:

```php
Route::post('/webhooks/gangway', function () {
    //
})->middleware('gangway.webhook');
```

Of laat het pakket de route registreren:

```env
GANGWAY_WEBHOOK_ROUTE_ENABLED=true
GANGWAY_WEBHOOK_PATH=webhooks/gangway
```

Daarna luisteren:

```php
use Gangway\Laravel\Enums\WebhookEvent;
use Gangway\Laravel\Events\WebhookReceived;

Event::listen(WebhookReceived::class, function (WebhookReceived $event) {
    if ($event->webhook->is(WebhookEvent::BookingCreated)) {
        $booking = $event->webhook->object();
    }
});
```

Handmatig verifiëren:

```php
$webhook = Gangway::verifier()->verifyRequest($request);
```

## Wat dit pakket niet is

| Mechanisme | Gebruik |
|---|---|
| Operator API (`gw_…`) | Dit pakket |
| Publieke widget-token | `<x-gangway-widget>` / `Gangway::widgets()` |
| Dashboard-login | Sanctum-sessie in Gangway zelf, niet voor websites |
| Stripe Connect | Alleen voor operators die betalingen in Gangway aanzetten |

Live Stripe-checkout zit in de widget-flow. API-boekingen worden administratief vastgelegd; de website is zelf verantwoordelijk voor eventuele eigen betaalstroom.

Volledige endpoint-referentie: `{GANGWAY_BASE_URL}/docs/api` en `{GANGWAY_BASE_URL}/api/v1/openapi.json`.

## Requirements

- PHP 8.2+
- Laravel 11, 12 of 13
- Een Gangway-operator met een API-key

## Licentie

MIT
