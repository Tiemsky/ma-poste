<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Commune;
use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    protected $model = User::class;

    public function definition()
    {
        // Récupérer aléatoirement un pays, ville et commune (si disponible)
        $countryName = "Côte d’Ivoire";
        $country = Country::where('slug', Str::slug($countryName))->firstOrFail();
        $city = null;
        $commune = null;

        if ($country) {
            $city = City::where('country_id', $country->id)->inRandomOrder()->first();

            // Si la ville a des communes, on prend une commune au hasard
            if ($city && $city->has_communes) {
                $commune = Commune::where('city_id', $city->id)->inRandomOrder()->first();
            }
        }


        return [
            'key' => Str::uuid(), // clé unique
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => null,
            // 'email' => $this->faker->unique()->safeEmail,
            'role' => null, // valeur par défaut
            'country_id' => $country?->id,
            'city_id' => $city?->id,
            'commune_id' => $commune?->id,
            'account_type' => 'particulier',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // mot de passe par défaut
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

       public function withPhone(string $phone): static
        {
        return $this->state(fn(array $attributes) => [
            'phone' => $phone,
        ]);
        }
       public function withRole(string $role): static
        {
        return $this->state(fn(array $attributes) => [
            'role' => $role,
        ]);
        }
}
