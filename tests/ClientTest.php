<?php

namespace Gangway\Laravel\Tests;

use Gangway\Laravel\Exceptions\AuthenticationException;
use Gangway\Laravel\Exceptions\ConfigurationException;
use Gangway\Laravel\Exceptions\NotFoundException;
use Gangway\Laravel\Exceptions\UnprocessableException;
use Gangway\Laravel\Exceptions\ValidationException;
use Gangway\Laravel\Facades\Gangway;
use Illuminate\Support\Facades\Http;

class ClientTest extends TestCase
{
    public function test_ping_reads_the_discovery_envelope(): void
    {
        Http::fake([
            'https://gangway.test/api/v1/ping' => Http::response([
                'data' => [
                    'object' => 'ping',
                    'ok' => true,
                    'api_version' => '1.0',
                    'platform_version' => '2026.09.1',
                ],
            ], 200, ['Gangway-Request-Id' => 'req_abc', 'Gangway-Operator' => 'harbour-tours']),
        ]);

        $pong = Gangway::ping();

        $this->assertSame('ping', $pong->object());
        $this->assertTrue($pong->ok);
        $this->assertSame('1.0', $pong['api_version']);
        $this->assertSame('req_abc', Gangway::lastMeta()?->requestId);
        $this->assertSame('harbour-tours', Gangway::lastMeta()?->operator);
    }

    public function test_products_list_is_paginated_and_iterable(): void
    {
        Http::fake([
            'https://gangway.test/api/v1/products*' => Http::response([
                'data' => [
                    ['object' => 'product', 'id' => 41, 'name' => 'Sunset Cruise', 'type' => 'tour'],
                ],
                'meta' => [
                    'total' => 1,
                    'per_page' => 50,
                    'current_page' => 1,
                    'last_page' => 1,
                    'has_more' => false,
                ],
                'links' => ['self' => '...', 'next' => null, 'prev' => null],
            ]),
        ]);

        $page = Gangway::products()->all(['type' => 'tour']);

        $this->assertSame(1, $page->total());
        $this->assertFalse($page->hasMore());
        $this->assertSame('Sunset Cruise', $page->items()[0]->name);
        $this->assertSame(41, iterator_to_array($page)[0]->id());
    }

    public function test_create_booking_sends_bearer_and_idempotency_headers(): void
    {
        Http::fake([
            'https://gangway.test/api/v1/bookings' => Http::response([
                'data' => [
                    'object' => 'booking',
                    'id' => '7c9e6679-7425-40de-944b-e07fc1f90ae7',
                    'reference' => 'GW-1001',
                    'status' => 'confirmed',
                ],
            ], 201),
        ]);

        $booking = Gangway::bookings()->create([
            'event_id' => 8842,
            'quantity' => 2,
            'booker_name' => 'Sanne de Vries',
            'booker_email' => 'sanne@example.com',
        ], 'booking-sanne-1');

        $this->assertSame('GW-1001', $booking->reference);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://gangway.test/api/v1/bookings'
                && $request->hasHeader('Authorization', 'Bearer gw_test_key')
                && $request->hasHeader('Idempotency-Key', 'booking-sanne-1')
                && $request['event_id'] === 8842;
        });
    }

    public function test_ticket_lookup_uses_query_string(): void
    {
        Http::fake([
            'https://gangway.test/api/v1/tickets/lookup*' => Http::response([
                'data' => ['object' => 'ticket', 'id' => 't_1', 'qr_code' => 'ABC'],
            ]),
        ]);

        $ticket = Gangway::tickets()->lookup('ABC');

        $this->assertSame('ABC', $ticket->qr_code);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'qr_code=ABC'));
    }

    public function test_maps_api_error_types_to_exceptions(): void
    {
        Http::fake([
            'https://gangway.test/api/v1/bookings/missing' => Http::response([
                'message' => 'No booking found with identifier missing.',
                'error' => [
                    'type' => 'resource_not_found',
                    'message' => 'No booking found with identifier missing.',
                    'status' => 404,
                    'request_id' => 'req_404',
                ],
            ], 404),
        ]);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No booking found with identifier missing.');

        Gangway::bookings()->get('missing');
    }

    public function test_validation_and_unprocessable_errors(): void
    {
        Http::fake([
            'https://gangway.test/api/v1/bookings' => Http::response([
                'message' => 'The quantity field is required.',
                'error' => [
                    'type' => 'validation_failed',
                    'message' => 'The quantity field is required.',
                    'status' => 422,
                    'details' => ['quantity' => ['required']],
                ],
            ], 422),
        ]);

        try {
            Gangway::bookings()->create(['event_id' => 1, 'booker_name' => 'A', 'booker_email' => 'a@b.c']);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $exception) {
            $this->assertSame(['quantity' => ['required']], $exception->details);
        }
    }

    public function test_unprocessable_capacity_error(): void
    {
        Http::fake([
            'https://gangway.test/api/v1/bookings' => Http::response([
                'message' => 'Onvoldoende capaciteit.',
                'error' => [
                    'type' => 'unprocessable_entity',
                    'message' => 'Onvoldoende capaciteit.',
                    'status' => 422,
                ],
            ], 422),
        ]);

        $this->expectException(UnprocessableException::class);

        Gangway::bookings()->create([
            'event_id' => 1,
            'quantity' => 99,
            'booker_name' => 'A',
            'booker_email' => 'a@b.c',
        ]);
    }

    public function test_unauthorized_key_is_mapped(): void
    {
        Http::fake([
            'https://gangway.test/api/v1/me' => Http::response([
                'message' => 'Invalid API key.',
                'error' => ['type' => 'unauthorized', 'message' => 'Invalid API key.', 'status' => 401],
            ], 401),
        ]);

        $this->expectException(AuthenticationException::class);

        Gangway::me();
    }

    public function test_using_overrides_credentials_for_one_client(): void
    {
        Http::fake([
            'https://other.test/api/v1/ping' => Http::response([
                'data' => ['object' => 'ping', 'ok' => true],
            ]),
        ]);

        $pong = Gangway::using('gw_other', 'https://other.test')->ping();

        $this->assertTrue($pong->ok);

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer gw_other')
            && $request->url() === 'https://other.test/api/v1/ping');
    }

    public function test_missing_api_key_fails_fast(): void
    {
        config(['gangway.api_key' => null]);
        $this->app->forgetInstance(\Gangway\Laravel\Client::class);
        $this->app->forgetInstance(\Gangway\Laravel\GangwayManager::class);

        $this->expectException(ConfigurationException::class);

        Gangway::ping();
    }

    public function test_invoice_pdf_returns_binary(): void
    {
        Http::fake([
            'https://gangway.test/api/v1/invoices/12/pdf' => Http::response(
                '%PDF-1.4',
                200,
                ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="invoice-12.pdf"'],
            ),
        ]);

        $pdf = Gangway::invoices()->pdf(12);

        $this->assertTrue($pdf->isPdf());
        $this->assertSame('invoice-12.pdf', $pdf->filename);
        $this->assertSame('%PDF-1.4', $pdf->contents);
    }

    public function test_widget_list_uses_wordpress_endpoint(): void
    {
        Http::fake([
            'https://gangway.test/api/wordpress/widgets' => Http::response([
                'widgets' => [
                    ['name' => 'Homepage', 'token' => 'tok_1', 'shortcode' => '[gangway token="tok_1"]', 'active' => true],
                ],
                'app_url' => 'https://gangway.test',
            ]),
        ]);

        $result = Gangway::widgets()->all();

        $this->assertSame('tok_1', $result['widgets'][0]['token']);
        $this->assertSame('https://gangway.test', $result['app_url']);
    }
}
