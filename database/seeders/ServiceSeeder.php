<?php

namespace Database\Seeders;

use App\Models\Administration;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Services CNPS
        $cnps = Administration::where('slug', 'cnps')->firstOrFail();
        $this->seedServices($cnps->id, [
            ['name' => 'Vérification cotisation', 'slug' => 'verification-cotisation', 'description' => 'Vérifier les cotisations CNPS', 'status' => 'disable'],
            ['name' => 'Déclaration employés', 'slug' => 'declaration-employes', 'description' => 'Déclarer les employés à la CNPS', 'status' => 'active'],
        ]);

        // 2. Services GUICHET UNIQUE
        $guichet = Administration::where('slug', 'guichet')->firstOrFail();
        $this->seedServices($guichet->id, [
            ['name' => 'Douane (Marchandises)', 'slug' => 'douane-marchandises', 'description' => 'Déclaration douanière d\'import/export', 'status' => 'active'],
            ['name' => 'Déclaration Juridique', 'slug' => 'declaration-juridique', 'description' => 'Déclaration de patrimoine et statut', 'status' => 'disable'],
            ['name' => 'Déclaration Fiscale', 'slug' => 'declaration-fiscale', 'description' => 'Déclaration des biens fiscaux', 'status' => 'disable'],
        ]);
    }

    private function seedServices($adminId, array $services)
    {
        $payload = [];
        foreach ($services as $service) {
            $payload[] = [
                'key' => (string) Str::uuid(),
                'name' => $service['name'],
                'slug' => $service['slug'],
                'description' => $service['description'],
                'status' =>  $service['status'],
                'administration_id' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Service::upsert($payload, ['slug', 'administration_id'], ['name', 'description', 'status']);
    }
}
