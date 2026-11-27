<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Client;
use App\Models\Reservation;
use App\Services\MetaConversionsService;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Server-side Purchase events for the Meta Conversions API. Models are built
 * in memory: nothing here touches the database.
 */
class MetaConversionsTest extends TestCase
{
    private MetaConversionsService $meta;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.meta.pixel_id' => '1234567890',
            'services.meta.access_token' => 'test-token',
            'services.meta.test_event_code' => null,
            'services.meta.graph_version' => 'v25.0',
        ]);

        $this->meta = app(MetaConversionsService::class);
    }

    public function test_purchase_event_hashes_and_normalizes_customer_data(): void
    {
        $event = $this->meta->reservationPurchaseEvent($this->reservation(), $this->request(), [
            'event_id' => 'evt-123',
            'source_url' => 'https://chanaacar.com/cars/dacia-logan',
            'fbp' => 'fb.1.1700000000000.111',
            'fbc' => 'fb.1.1700000000000.AbCd',
        ]);

        $this->assertSame('Purchase', $event['event_name']);
        $this->assertSame('evt-123', $event['event_id']);
        $this->assertSame('website', $event['action_source']);
        $this->assertSame('https://chanaacar.com/cars/dacia-logan', $event['event_source_url']);

        $user = $event['user_data'];
        $this->assertSame([hash('sha256', 'karim.benali@example.com')], $user['em']);
        $this->assertSame([hash('sha256', '212612345678')], $user['ph']);
        $this->assertSame([hash('sha256', 'karim')], $user['fn']);
        $this->assertSame([hash('sha256', 'benali')], $user['ln']);
        $this->assertSame('41.250.10.20', $user['client_ip_address']);
        $this->assertSame('Mozilla/5.0 Test', $user['client_user_agent']);
        $this->assertSame('fb.1.1700000000000.111', $user['fbp']);
        $this->assertSame('fb.1.1700000000000.AbCd', $user['fbc']);

        $this->assertSame([
            'currency' => 'MAD',
            'value' => 1850.5,
            'content_ids' => ['7'],
            'content_type' => 'product',
            'content_name' => 'Dacia Logan',
            'order_id' => 'CHN-2026-0042',
        ], $event['custom_data']);
    }

    public function test_missing_customer_fields_are_left_out_rather_than_sent_empty(): void
    {
        $reservation = $this->reservation(['email' => null, 'full_name' => 'Karim']);

        $event = $this->meta->reservationPurchaseEvent($reservation, $this->request());

        $this->assertArrayNotHasKey('em', $event['user_data']);
        $this->assertArrayNotHasKey('ln', $event['user_data']);
        $this->assertArrayNotHasKey('fbp', $event['user_data']);
        // No browser event ID → a stable one derived from the reservation.
        $this->assertSame('reservation-CHN-2026-0042', $event['event_id']);
    }

    /**
     * @dataProvider phoneNumbers
     */
    public function test_phone_numbers_get_a_country_code(string $input, string $expected): void
    {
        $event = $this->meta->reservationPurchaseEvent($this->reservation(['phone' => $input]), $this->request());

        $this->assertSame([hash('sha256', $expected)], $event['user_data']['ph']);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function phoneNumbers(): array
    {
        return [
            'moroccan national' => ['06 12 34 56 78', '212612345678'],
            'international plus' => ['+212 6 12 34 56 78', '212612345678'],
            'international 00' => ['00212612345678', '212612345678'],
            'trunk zero kept' => ['+212 (0)6 12 34 56 78', '212612345678'],
            'no leading zero' => ['612345678', '212612345678'],
            'foreign number' => ['+33 6 12 34 56 78', '33612345678'],
        ];
    }

    public function test_send_posts_to_the_pixel_with_the_token_in_the_body(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        config(['services.meta.test_event_code' => 'TEST123']);

        $event = $this->meta->reservationPurchaseEvent($this->reservation(), $this->request(), ['event_id' => 'evt-123']);
        $this->meta->send($event);

        Http::assertSent(function (HttpRequest $request) {
            $data = json_decode($request['data'], true);

            return $request->url() === 'https://graph.facebook.com/v25.0/1234567890/events'
                && $request['access_token'] === 'test-token'
                && $request['test_event_code'] === 'TEST123'
                && $data[0]['event_id'] === 'evt-123'
                && ! str_contains($request->url(), 'test-token');
        });
    }

    public function test_nothing_is_sent_without_credentials(): void
    {
        Http::fake();
        config(['services.meta.access_token' => null]);

        $this->assertFalse($this->meta->enabled());
        $this->meta->send(['event_name' => 'Purchase']);

        Http::assertNothingSent();
    }

    public function test_a_meta_error_never_throws(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid token']], 400)]);

        $this->meta->send(['event_name' => 'Purchase']);

        Http::assertSentCount(1);
    }

    /**
     * @param  array<string, mixed>  $clientOverrides
     */
    private function reservation(array $clientOverrides = []): Reservation
    {
        $client = new Client(array_merge([
            'full_name' => '  Karim  Ben Ali Benali ',
            'phone' => '06 12 34 56 78',
            'email' => ' Karim.Benali@Example.com ',
        ], $clientOverrides));

        $car = new Car(['brand' => 'Dacia', 'name' => 'Logan']);
        $car->id = 7;

        $reservation = new Reservation([
            'reservation_number' => 'CHN-2026-0042',
            'car_id' => 7,
            'total_price' => '1850.50',
        ]);
        $reservation->setRelation('client', $client);
        $reservation->setRelation('car', $car);

        return $reservation;
    }

    /** A booking as it reaches Laravel through the Nuxt proxy. */
    private function request(): Request
    {
        return Request::create('/api/public/reservations', 'POST', server: [
            'REMOTE_ADDR' => '172.18.0.5', // the Nuxt container
            'HTTP_X_REAL_IP' => '41.250.10.20',
            'HTTP_X_FORWARDED_FOR' => '41.250.10.20',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 Test',
            'HTTP_REFERER' => 'https://chanaacar.com/cars/dacia-logan',
        ]);
    }
}
