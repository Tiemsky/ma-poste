<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Commune;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommuneSeeder extends Seeder
{
    public function run(): void
    {
        $abidjan = City::where('slug', 'abidjan')->firstOrFail();

        $communes = [
            'Abobo',
            'Adjamé',
            'Attécoubé',
            'Cocody',
            'Koumassi',
            'Marcory',
            'Plateau',
            'Port-Bouët',
            'Treichville',
            'Yopougon',
        ];

        $payload = [];

        foreach ($communes as $commune) {
            $payload[] = [
                'name' => $commune,
                'slug' => Str::slug($commune),
                'city_id' => $abidjan->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Commune::upsert(
            $payload,
            ['slug', 'city_id'],
            ['name']
        );
    }
}
