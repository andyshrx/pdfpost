<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class TokensTest extends TestCase
{
    use RefreshDatabase;

    public function test_tokens_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/tokens')->assertOk();
    }

    public function test_tokens_page_requires_auth(): void
    {
        $this->get('/tokens')->assertRedirect('/login');
    }

    public function test_token_can_be_created(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Volt::test('tokens.index')
            ->set('name', 'my ci pipeline')
            ->set('abilities', ['render'])
            ->call('createToken');

        $response->assertHasNoErrors();

        $this->assertNotNull($response->get('plainTextToken'));
        $this->assertSame(1, $user->tokens()->count());

        $token = $user->tokens()->first();
        $this->assertSame('my ci pipeline', $token->name);
        $this->assertSame(['render'], $token->abilities);
    }

    public function test_token_needs_at_least_one_ability(): void
    {
        $this->actingAs(User::factory()->create());

        Volt::test('tokens.index')
            ->set('name', 'useless token')
            ->set('abilities', [])
            ->call('createToken')
            ->assertHasErrors(['abilities']);
    }

    public function test_token_can_be_revoked(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('old one', ['render']);

        $this->actingAs($user);

        Volt::test('tokens.index')->call('revoke', $token->accessToken->id);

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_you_cannot_revoke_someone_elses_token(): void
    {
        $owner = User::factory()->create();
        $token = $owner->createToken('not yours', ['render']);

        $this->actingAs(User::factory()->create());

        Volt::test('tokens.index')->call('revoke', $token->accessToken->id);

        $this->assertSame(1, $owner->tokens()->count());
    }
}
