<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
      return [
        'id'            => $this->id,
        'key'           => $this->key,
        'role'          => $this->role,
        'last_name'     => $this->last_name,
        'first_name'    => $this->first_name,
        'country'       => $this->country?->name,
        'commune'       => $this->commune?->name,
        'city'          => $this->city?->name,
        'phone'         => $this->phone,
        'email'         => $this->email,
        'account_type'  => $this->account_type
      ];
    }
}
