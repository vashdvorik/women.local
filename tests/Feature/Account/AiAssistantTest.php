<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\BotUser;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_assistant_uses_safe_catalog_data_and_returns_a_valid_member_link(): void
    {
        $user = BotUser::factory()->approved()->create(['description' => 'I need marketing support.']);
        $member = BotUser::factory()->approved()->create([
            'full_name' => 'Anna Expert',
            'description' => 'Marketing strategy',
            'expectation' => 'I can offer a consultation. Contact @private_name or +373 60000000.',
        ]);

        SiteSetting::setOpenRouterProviderConfig(['base_url' => 'https://router.test/api/v1', 'timeout' => 15, 'api_key' => 'test-key']);
        SiteSetting::setAgentFeatureConfig(['provider' => 'openrouter', 'model' => 'test/model', 'temperature' => 0.2, 'max_tokens' => 500, 'timeout' => 15]);

        Http::fake([
            'https://router.test/api/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => json_encode([
                    'reply' => 'Anna can help with marketing.',
                    'recommendation' => ['kind' => 'member', 'id' => $member->id, 'reason' => 'Marketing expertise'],
                    'profile_proposal' => null,
                ])]]],
            ]),
        ]);

        $response = $this->withSession([
            'account_telegram_id' => $user->telegram_id,
            '_account_expires' => now()->addDay()->timestamp,
        ])->postJson(route('account.assistant.message'), ['message' => 'Find a marketing expert']);

        $response->assertOk()
            ->assertJsonPath('recommendation.label', 'Anna Expert')
            ->assertJsonPath('recommendation.url', route('account.people.show', $member));

        Http::assertSent(function ($request): bool {
            $body = json_encode($request->data());
            return ! str_contains((string) $body, '@private_name')
                && ! str_contains((string) $body, '60000000');
        });
    }
}
