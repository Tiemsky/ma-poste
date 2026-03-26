<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdministrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
      return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'is_active' => $this->status === 'active',

            // === LOGO avec URL complète ===
            'logo' => $this->whenNotNull($this->logo),
            'logo_url' => $this->getLogoUrl(),
            'logo_path' => $this->logo ? "storage/app/public/{$this->logo}" : null,

            // === LOCALISATION ===
            'country' => new CountryResource($this->whenLoaded('country')),
            'city' => new CityResource($this->whenLoaded('city')),
            'commune' => new CommuneResource($this->whenLoaded('commune')),

            // === LOCALISATION (IDs bruts) ===
            'country_id' => $this->country_id,
            'city_id' => $this->city_id,
            'commune_id' => $this->commune_id,

            // === SERVICES ASSOCIÉS ===
            'services_count' => $this->whenCounted('services'),
            'services' => ServiceResource::collection($this->whenLoaded('services')),

            // === MÉTADONNÉES ===
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
      ];
    }


     /**
     * Génère l'URL complète du logo
     * Gère les cas: logo présent, logo absent, fichier inexistant
     */
    private function getLogoUrl(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        // Chemin complet du fichier
        $fullPath = storage_path("app/public/{$this->logo}");

        // Vérifier si le fichier existe physiquement
        if (!file_exists($fullPath)) {
            // Logger l'erreur pour debugging
            Log::warning('Logo file not found', [
                'administration_id' => $this->id,
                'logo_path' => $this->logo,
                'full_path' => $fullPath
            ]);
            return null;
        }

        // Retourner l'URL publique
        return Storage::disk('public')->url($this->logo);
    }

    /**
     * Vérifie si le logo existe physiquement
     */
    public function hasValidLogo(): bool
    {
        if (!$this->logo) {
            return false;
        }

        return Storage::disk('public')->exists($this->logo);
    }


}
