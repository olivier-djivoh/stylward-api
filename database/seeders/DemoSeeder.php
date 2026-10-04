<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ClothingModel;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Création des stylistes de démo...');

        $stylists = [
            [
                'first_name'  => 'Olivier',
                'last_name'   => 'Kouassi',
                'email'       => 'olivier@demo.com',
                'phone'       => '+229 97000001',
                'shop_name'   => 'Atelier Olivier',
                'shop_desc'   => 'Couture sur mesure pour hommes et femmes. Élégance et savoir-faire depuis 2010.',
                'shop_status' => 'active',
            ],
            [
                'first_name'  => 'Fatou',
                'last_name'   => 'Diallo',
                'email'       => 'fatou@demo.com',
                'phone'       => '+221 77000002',
                'shop_name'   => 'Créations Fatou',
                'shop_desc'   => 'Mode africaine moderne. Tenues de cérémonie et prêt-à-porter.',
                'shop_status' => 'active',
            ],
            [
                'first_name'  => 'Kofi',
                'last_name'   => 'Mensah',
                'email'       => 'kofi@demo.com',
                'phone'       => '+233 24000003',
                'shop_name'   => 'Kofi Style',
                'shop_desc'   => 'Costumes, chemises et accessoires pour gentlemen.',
                'shop_status' => 'active',
            ],
        ];

        $categories = Category::pluck('id', 'slug');

        foreach ($stylists as $index => $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'first_name'        => $data['first_name'],
                    'last_name'         => $data['last_name'],
                    'phone'             => $data['phone'],
                    'password'          => Hash::make('MotDePasse123!'),
                    'role'              => 'stylist',
                    'status'            => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $shop = Shop::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name'        => $data['shop_name'],
                    'slug'        => Str::slug($data['shop_name']),
                    'description' => $data['shop_desc'],
                    'phone'       => $data['phone'],
                    'whatsapp'    => $data['phone'],
                    'status'      => $data['shop_status'],
                ]
            );

            $this->createModelsForShop($shop, $categories, $index);
        }

        $this->command->info('✅ 3 stylistes, 3 boutiques, 15 modèles créés.');
        $this->command->info('');
        $this->command->info('Comptes de test (mot de passe: MotDePasse123!) :');
        $this->command->info('  - olivier@demo.com');
        $this->command->info('  - fatou@demo.com');
        $this->command->info('  - kofi@demo.com');
    }

    private function createModelsForShop(Shop $shop, $categories, int $shopIndex): void
    {
        $modelsByShop = [
            // Olivier - Atelier Olivier
            0 => [
                ['name' => 'Veste en lin beige',        'category' => 'vestes',        'price' => 45000, 'color' => 'Beige',       'status' => 'published'],
                ['name' => 'Costume trois pièces',      'category' => 'costumes',      'price' => 120000, 'color' => 'Bleu marine', 'status' => 'published'],
                ['name' => 'Chemise cintrée blanche',   'category' => 'chemises',      'price' => 18000, 'color' => 'Blanc',       'status' => 'published'],
                ['name' => 'Pantalon chino',            'category' => 'pantalons',     'price' => 22000, 'color' => 'Kaki',        'status' => 'published'],
                ['name' => 'Complet traditionnel',      'category' => 'vetements-traditionnels', 'price' => 85000, 'color' => 'Indigo', 'status' => 'draft'],
            ],
            // Fatou - Créations Fatou
            1 => [
                ['name' => 'Robe de soirée longue',     'category' => 'robes',         'price' => 75000, 'color' => 'Rouge',       'status' => 'published'],
                ['name' => 'Robe wax élégante',         'category' => 'robes',         'price' => 55000, 'color' => 'Multicolore', 'status' => 'published'],
                ['name' => 'Jupe crayon',               'category' => 'jupes',         'price' => 25000, 'color' => 'Noir',        'status' => 'published'],
                ['name' => 'Ensemble pagne moderne',    'category' => 'ensembles',     'price' => 65000, 'color' => 'Or',          'status' => 'published'],
                ['name' => 'Robe cocktail',             'category' => 'robes',         'price' => 60000, 'color' => 'Bordeaux',    'status' => 'draft'],
            ],
            // Kofi - Kofi Style
            2 => [
                ['name' => 'Costume slim noir',         'category' => 'costumes',      'price' => 135000, 'color' => 'Noir',       'status' => 'published'],
                ['name' => 'Veste en tweed',            'category' => 'vestes',        'price' => 65000,  'color' => 'Gris',       'status' => 'published'],
                ['name' => 'Chemise à carreaux',        'category' => 'chemises',      'price' => 20000,  'color' => 'Bleu',       'status' => 'published'],
                ['name' => 'Nœud papillon soie',        'category' => 'accessoires',   'price' => 8000,   'color' => 'Bordeaux',   'status' => 'published'],
                ['name' => 'Costume smoking',           'category' => 'costumes',      'price' => 180000, 'color' => 'Noir',       'status' => 'draft'],
            ],
        ];

        foreach ($modelsByShop[$shopIndex] as $i => $data) {
            $slug = Str::slug($data['name']);
            $baseSlug = $slug;
            $counter = 2;

            while (ClothingModel::where('shop_id', $shop->id)->where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            ClothingModel::create([
                'shop_id'      => $shop->id,
                'category_id'  => $categories[$data['category']] ?? null,
                'name'         => $data['name'],
                'slug'         => $slug,
                'description'  => "Création exclusive de {$shop->name}. Confection sur mesure, finitions soignées.",
                'price'        => $data['price'],
                'currency'     => 'XOF',
                'color'        => $data['color'],
                'reference'    => strtoupper(Str::random(6)),
                'status'       => $data['status'],
                'published_at' => $data['status'] === 'published' ? now() : null,
            ]);
        }
    }
}
