<?php

namespace Database\Factories;

use App\Models\ClothingModel;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClothingModelFactory extends Factory
{
    protected $model = ClothingModel::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'shop_id'     => Shop::factory(),
            'category_id' => null,
            'name'        => ucfirst($name),
            'slug'        => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->sentence(),
            'price'       => fake()->randomFloat(2, 5000, 200000),
            'currency'    => 'XOF',
            'color'       => fake()->colorName(),
            'reference'   => strtoupper(Str::random(6)),
            'status'      => 'draft',
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status'       => 'published',
            'published_at' => now(),
        ]);
    }
}
