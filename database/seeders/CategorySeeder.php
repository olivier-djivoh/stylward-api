<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Vestes',
            'Costumes',
            'Chemises',
            'Robes',
            'Pantalons',
            'Jupes',
            'Ensembles',
            'Vêtements traditionnels',
            'Accessoires',
        ];

        foreach ($categories as $index => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name'       => $name,
                    'status'     => 'active',
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
