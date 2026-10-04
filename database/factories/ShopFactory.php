<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'user_id'     => User::factory(),
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->sentence(),
            'phone'       => fake()->phoneNumber(),
            'whatsapp'    => fake()->phoneNumber(),
            'status'      => 'active',
        ];
    }
}
