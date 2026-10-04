<?php

namespace Tests\Feature\Security;

use App\Models\ClothingModel;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function stylist_a_cannot_view_stylist_b_model(): void
    {
        $stylistA = User::factory()->create();
        $stylistB = User::factory()->create();

        Shop::factory()->create(['user_id' => $stylistA->id]);
        $shopB = Shop::factory()->create(['user_id' => $stylistB->id]);

        $modelB = ClothingModel::factory()->create(['shop_id' => $shopB->id]);

        Sanctum::actingAs($stylistA);

        $this->getJson("/api/v1/models/{$modelB->id}")
            ->assertNotFound();
    }

    #[Test]
    public function stylist_a_cannot_update_stylist_b_model(): void
    {
        $stylistA = User::factory()->create();
        $stylistB = User::factory()->create();

        $shopB = Shop::factory()->create(['user_id' => $stylistB->id]);
        $modelB = ClothingModel::factory()->create(['shop_id' => $shopB->id]);

        Sanctum::actingAs($stylistA);

        $this->putJson("/api/v1/models/{$modelB->id}", [
            'name' => 'Modele pirate',
        ])->assertNotFound();
    }

    #[Test]
    public function stylist_a_cannot_delete_stylist_b_model(): void
    {
        $stylistA = User::factory()->create();
        $stylistB = User::factory()->create();

        $shopB = Shop::factory()->create(['user_id' => $stylistB->id]);
        $modelB = ClothingModel::factory()->create(['shop_id' => $shopB->id]);

        Sanctum::actingAs($stylistA);

        $this->deleteJson("/api/v1/models/{$modelB->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('models', ['id' => $modelB->id]);
    }

    #[Test]
    public function stylist_a_only_sees_own_models_in_list(): void
    {
        $stylistA = User::factory()->create();
        $stylistB = User::factory()->create();

        $shopA = Shop::factory()->create(['user_id' => $stylistA->id]);
        $shopB = Shop::factory()->create(['user_id' => $stylistB->id]);

        ClothingModel::factory()->count(3)->create(['shop_id' => $shopA->id]);
        ClothingModel::factory()->count(5)->create(['shop_id' => $shopB->id]);

        Sanctum::actingAs($stylistA);

        $this->getJson('/api/v1/models')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function public_catalog_only_shows_published_models(): void
    {
        $stylist = User::factory()->create();
        $shop = Shop::factory()->create([
            'user_id' => $stylist->id,
            'status'  => 'active',
        ]);

        ClothingModel::factory()->count(2)->published()->create(['shop_id' => $shop->id]);
        ClothingModel::factory()->count(3)->create(['shop_id' => $shop->id]);

        $this->getJson("/api/v1/public/shops/{$shop->slug}/models")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function public_catalog_hides_suspended_shop(): void
    {
        $stylist = User::factory()->create();
        $shop = Shop::factory()->create([
            'user_id' => $stylist->id,
            'status'  => 'suspended',
        ]);

        ClothingModel::factory()->published()->create(['shop_id' => $shop->id]);

        $this->getJson("/api/v1/public/shops/{$shop->slug}/models")
            ->assertNotFound();
    }
}
