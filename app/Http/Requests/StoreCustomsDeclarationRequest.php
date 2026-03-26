<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomsDeclarationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id'          => ['required','integer'],
            // Identité
            'declarant_type'      => ['required', 'in:individual,company'],
            'declarant_name'      => ['required', 'string', 'max:200'],
            'declarant_id_number' => ['nullable', 'string', 'max:50'],
            'cni_recto'           => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'cni_verso'           => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],

            // Transport
            'transport_mode'      => ['required', 'in:air,sea,road'],
            'departure_country'   => ['required', 'string', 'max:100'],
            'arrival_country'     => ['required', 'string', 'max:100'],
            'transport_company'   => ['required', 'string', 'max:200'],
            'tracking_number'     => ['nullable', 'string', 'max:100'],

            // Articles (JSON string validé puis décodé)
            'items_json'          => ['required', 'json'],

            // Facture
            'invoice'             => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validation approfondie des articles
            if (!$this->filled('items_json')) return;

            $items = json_decode($this->input('items_json'), true);

            if (!is_array($items) || count($items) === 0) {
                $validator->errors()->add('items_json', 'Au moins un article est requis.');
                return;
            }

            foreach ($items as $i => $item) {
                $n = $i + 1;
                if (empty($item['name']))           $validator->errors()->add("items_json", "Article $n : désignation requise.");
                if (empty($item['category']))        $validator->errors()->add("items_json", "Article $n : catégorie requise.");
                if (empty($item['origin_country']))  $validator->errors()->add("items_json", "Article $n : pays d'origine requis.");
                if (($item['quantity'] ?? 0) < 1)   $validator->errors()->add("items_json", "Article $n : quantité doit être ≥ 1.");
                if (($item['unit_value'] ?? 0) <= 0) $validator->errors()->add("items_json", "Article $n : valeur unitaire doit être > 0.");
            }
        });
    }

    public function messages(): array
    {
        return [
            'cni_recto.required'   => 'Le recto de la CNI est obligatoire.',
            'cni_verso.required'   => 'Le verso de la CNI est obligatoire.',
            'invoice.required'     => 'La facture commerciale est obligatoire.',
            'items_json.required'  => 'La liste des articles est requise.',
            'items_json.json'      => 'Format des articles invalide.',
            'transport_mode.in'    => 'Mode de transport invalide.',
        ];
    }
}
