<?php

namespace Database\Seeders;

use App\Models\Administration;
use App\Models\Country;
use App\Models\City;
use App\Models\Commune;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdministrationSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('slug', Str::slug("Côte d’Ivoire"))->firstOrFail();

        $items = [
            ['name' => 'CNPS', 'logo' => 'cnps.jpeg', 'description' => 'Caisse Nationale de Prévoyance Sociale', 'city' => 'abidjan', 'commune' => 'Cocody'],
            ['name' => 'GUICHET', 'logo' => 'goods.png', 'description' => 'Guichet Unique des declaration de biens', 'city' => 'abidjan', 'commune' => null],
        ];

        $disk = Storage::disk('public');

        // S'assurer que le dossier de destination existe
        if (!$disk->exists('administrations')) {
            $disk->makeDirectory('administrations');
        }

        foreach ($items as $item) {
            $city = City::where('slug', $item['city'])->first();
            $commune = $item['commune'] ? Commune::where('name', $item['commune'])->first() : null;

            $storedPath = null;

            if (!empty($item['logo'])) {
                // Chemin ABSOLU vers ton dossier de base dans database/
                $sourcePath = database_path("seeders/data/logos/{$item['logo']}");
                $targetPath = "administrations/{$item['logo']}";

                if (file_exists($sourcePath)) {
                    // Utilisation de put() avec stream pour éviter les problèmes de mémoire
                    $disk->put($targetPath, fopen($sourcePath, 'r+'));
                    $storedPath = $targetPath;
                } else {
                    $this->command->error("Source introuvable : {$sourcePath}");
                }
            }

            Administration::updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'key' => (string) Str::uuid(),
                    'name' => $item['name'],
                    'logo' => $storedPath,
                    'description' => $item['description'],
                    'country_id' => $country->id,
                    'city_id' => $city?->id,
                    'commune_id' => $commune?->id,
                    'status' => 'active',
                ]
            );
        }
    }
}










