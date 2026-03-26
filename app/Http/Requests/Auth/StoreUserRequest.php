<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Tout le monde peut s'inscrire
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_company' => ['nullable'],
            'last_name' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'first_name' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'country_id' => ['required', 'exists:countries,id'],
            'city_id' => [ 'required', 'exists:cities,id'],
            'commune_id' => ['nullable', 'exists:communes,id'],
            'phone' => [
                'required',
                'string',
                'max:20',
                'unique:users,phone',
                'regex:/^(\+225|0)[0-9]{8,10}$/', // Format Côte d'Ivoire adaptable
            ],
            // 'email' => [
            //     'required',
            //     'string',
            //     'lowercase',
            //     'email',
            //     'max:255',
            //     'unique:users,email'
            // ],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * Messages d'erreur personnalisés (optionnel mais recommandé)
     */
    public function messages(): array
    {
        return [
            'last_name.regex' => 'Le nom ne doit contenir que des lettres, espaces, tirets ou apostrophes.',
            'first_name.regex' => 'Le prénom ne doit contenir que des lettres, espaces, tirets ou apostrophes.',
            'phone.regex' => 'Le numéro de téléphone doit être au format valide (ex: 0707070707 ou +2250707070707).',
            'phone.unique' => 'Ce numéro de téléphone est déjà associé à un compte.',
            // 'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ];
    }

    /**
     * Préparer les données avant validation
     */
    protected function prepareForValidation(): void
    {
        // Normaliser le téléphone: supprimer espaces, tirets, points
        if ($this->phone) {
            $this->merge([
                'phone' => preg_replace('/[\s\-\.\(\)]/', '', $this->phone),
            ]);
        }
    }
}
