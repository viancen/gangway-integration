<?php

namespace Gangway\Laravel\Tests;

use Gangway\Laravel\Enums\WebhookEvent;
use Gangway\Laravel\Events\WebhookReceived;
use Gangway\Laravel\Exceptions\WebhookSignatureException;
use Gangway\Laravel\Facades\Gangway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class WebhookVerifierTest extends TestCase
{
    public function test_valid_signature_is_accepted(): void
    {
        $body = json_encode([
            'id' => 'evt_test',
            'type' => 'booking.created',
            'data' => ['object' => ['object' => 'booking', 'id' => 'b1']],
        ], JSON_THROW_ON_ERROR);

        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'whsec_test');

        $webhook = Gangway::verifier()->verify(
            $body,
            "t={$timestamp},v1={$signature}",
            'booking.created',
            'del_1',
        );

        $this->assertTrue($webhook->is(WebhookEvent::BookingCreated));
        $this->assertSame('del_1', $webhook->deliveryId);
        $this->assertSame('b1', $webhook->object()?->id());
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $this->expectException(WebhookSignatureException::class);

        Gangway::verifier()->verify(
            '{"type":"booking.created"}',
            't='.time().',v1=deadbeef',
        );
    }

    public function test_stale_timestamp_is_rejected(): void
    {
        $body = '{"type":"webhook.test"}';
        $timestamp = time() - 3600;
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'whsec_test');

        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessage('tolerance');

        Gangway::verifier()->verify($body, "t={$timestamp},v1={$signature}");
    }

    public function test_middleware_attaches_verified_webhook(): void
    {
        $body = json_encode(['id' => 'evt_1', 'type' => 'webhook.test', 'data' => []], JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'whsec_test');

        $request = Request::create('/webhooks/gangway', 'POST', [], [], [], [
            'HTTP_GANGWAY_SIGNATURE' => "t={$timestamp},v1={$signature}",
            'HTTP_GANGWAY_EVENT' => 'webhook.test',
            'HTTP_GANGWAY_DELIVERY' => 'del_9',
            'CONTENT_TYPE' => 'application/json',
        ], $body);

        $middleware = new \Gangway\Laravel\Http\Middleware\VerifyGangwayWebhook;
        $middleware->handle($request, function (Request $next) {
            $this->assertSame('del_9', $next->attributes->get('gangway_webhook')->deliveryId);

            return response('ok');
        });
    }

    public function test_controller_dispatches_laravel_event(): void
    {
        Event::fake([WebhookReceived::class]);

        $body = json_encode(['id' => 'evt_1', 'type' => 'invoice.issued', 'data' => ['object' => ['id' => 9]]], JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'whsec_test');

        $request = Request::create('/webhooks/gangway', 'POST', server: [
            'HTTP_GANGWAY_SIGNATURE' => "t={$timestamp},v1={$signature}",
            'HTTP_GANGWAY_EVENT' => 'invoice.issued',
            'HTTP_GANGWAY_DELIVERY' => 'del_inv',
        ], content: $body);

        $request->attributes->set('gangway_webhook', Gangway::verifier()->verifyRequest($request));

        $response = (new \Gangway\Laravel\Http\Controllers\WebhookController)($request);

        $this->assertTrue($response->getData(true)['received']);

        Event::assertDispatched(WebhookReceived::class, function (WebhookReceived $event) {
            return $event->type() === 'invoice.issued';
        });
    }
}
