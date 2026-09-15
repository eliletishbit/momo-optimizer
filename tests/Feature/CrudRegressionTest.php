<?php

namespace Tests\Feature;

use App\Models\Method;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrudRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_method_with_required_fields(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'country_code' => 'BJ',
        ]);

        $response = $this->actingAs($admin)->post('/admin/methods', [
            'name' => 'MTN Mobile Money',
            'code' => 'mtn',
            'category' => 'mobile_money',
            'country_code' => 'BJ',
            'logo_url' => 'https://example.com/mtn.png',
        ]);

        $response->assertRedirect('/admin/methods');
        $this->assertDatabaseHas('methods', [
            'code' => 'mtn',
            'category' => 'mobile_money',
            'country_code' => 'BJ',
        ]);
    }

    public function test_admin_can_create_fee_with_percentage(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $method = Method::create([
            'name' => 'MTN',
            'code' => 'mtn',
            'category' => 'mobile_money',
            'country_code' => 'BJ',
            'logo_url' => 'https://example.com/mtn.png',
        ]);

        $response = $this->actingAs($admin)->post('/admin/fees', [
            'type' => 'receipt',
            'method_id' => $method->id,
            'min_amount' => 0,
            'max_amount' => 10000,
            'fee_amount' => 0,
            'fee_percentage' => 2.5,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('receipt_fees', [
            'method_id' => $method->id,
            'country_code' => 'BJ',
            'fee_type' => 'percentage',
            'fee_amount' => '2.5',
        ]);
    }

    public function test_user_can_save_preferred_method_with_type(): void
    {
        $user = User::factory()->create([
            'country_code' => 'BJ',
        ]);

        $method = Method::create([
            'name' => 'Moov',
            'code' => 'moov',
            'category' => 'mobile_money',
            'country_code' => 'BJ',
            'logo_url' => 'https://example.com/moov.png',
        ]);

        $response = $this->actingAs($user)->post('/settings', [
            'method_id' => $method->id,
            'is_preferred_sending' => true,
            'is_preferred_receipt' => true,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('user_methods', [
            'user_id' => $user->id,
            'method_id' => $method->id,
            'type' => 'both',
        ]);
    }

    public function test_free_trial_expired_user_cannot_use_calculator(): void
    {
        $user = User::factory()->create([
            'subscription' => 'free',
            'subscription_expires_at' => now()->subDay(),
            'country_code' => 'BJ',
        ]);

        $this->actingAs($user)
            ->get('/calculator')
            ->assertRedirect(route('pricing'));
    }

    public function test_user_without_two_methods_cannot_use_calculator(): void
    {
        $user = User::factory()->create([
            'subscription' => 'premium',
            'subscription_expires_at' => now()->addMonth(),
            'country_code' => 'BJ',
        ]);

        $this->actingAs($user)
            ->get('/calculator')
            ->assertRedirect(route('settings'));
    }
}
