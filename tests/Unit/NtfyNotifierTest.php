<?php

namespace Tests\Unit;

use App\Models\Caller;
use App\Services\NtfyNotifier;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NtfyNotifierTest extends TestCase
{
    private NtfyNotifier $notifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notifier = new NtfyNotifier;
    }

    public function test_notify_registration_sends_http_request_when_url_configured(): void
    {
        config(['services.ntfy.url' => 'https://ntfy.sh/test-topic']);
        Http::fake();

        $caller = new Caller([
            'name' => 'Ahmed',
            'cpr' => '123456789',
            'phone' => '+97366112233',
        ]);

        $this->notifier->notifyRegistration($caller);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://ntfy.sh/test-topic'
                && str_contains((string) $request->body(), 'Ahmed')
                && str_contains((string) $request->body(), '123******');
        });
    }

    public function test_notify_registration_does_nothing_when_url_not_configured(): void
    {
        config(['services.ntfy.url' => null]);
        Http::fake();

        $caller = new Caller([
            'name' => 'Ahmed',
            'cpr' => '123456789',
            'phone' => '+97366112233',
        ]);

        $this->notifier->notifyRegistration($caller);

        Http::assertNothingSent();
    }

    public function test_notify_winner_sends_request_with_masked_cpr(): void
    {
        config(['services.ntfy.url' => 'https://ntfy.sh/test-topic']);
        Http::fake();

        $caller = new Caller([
            'name' => 'Fatima',
            'cpr' => '987654321',
            'phone' => '+97366998877',
        ]);

        $this->notifier->notifyWinner($caller);

        Http::assertSent(function ($request) {
            return str_contains((string) $request->body(), 'Fatima')
                && str_contains((string) $request->body(), '987******')
                && $request->hasHeader('Title', 'Winner Selected');
        });
    }

    public function test_mask_cpr_masks_all_but_first_three_digits(): void
    {
        $reflection = new \ReflectionClass($this->notifier);
        $method = $reflection->getMethod('maskCpr');

        $this->assertEquals('123******', $method->invoke($this->notifier, '123456789'));
    }

    public function test_mask_cpr_returns_na_for_null(): void
    {
        $reflection = new \ReflectionClass($this->notifier);
        $method = $reflection->getMethod('maskCpr');

        $this->assertEquals('N/A', $method->invoke($this->notifier, null));
    }

    public function test_mask_cpr_masks_short_strings_entirely(): void
    {
        $reflection = new \ReflectionClass($this->notifier);
        $method = $reflection->getMethod('maskCpr');

        $this->assertEquals('***', $method->invoke($this->notifier, '123'));
        $this->assertEquals('**', $method->invoke($this->notifier, '12'));
    }

    public function test_mask_phone_keeps_last_four_digits(): void
    {
        $reflection = new \ReflectionClass($this->notifier);
        $method = $reflection->getMethod('maskPhone');

        $result = $method->invoke($this->notifier, '+97366112233');
        $this->assertStringEndsWith('2233', $result);
        $this->assertStringContainsString('*', $result);
    }

    public function test_mask_phone_returns_na_for_null(): void
    {
        $reflection = new \ReflectionClass($this->notifier);
        $method = $reflection->getMethod('maskPhone');

        $this->assertEquals('N/A', $method->invoke($this->notifier, null));
    }

    public function test_mask_phone_masks_short_numbers_entirely(): void
    {
        $reflection = new \ReflectionClass($this->notifier);
        $method = $reflection->getMethod('maskPhone');

        $result = $method->invoke($this->notifier, '1234');
        $this->assertEquals('****', $result);
    }
}
