<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCnpsDeclarationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth middleware gère déjà l'accès via la route
    }

    public function rules(): array
    {
        $hasCnps = filter_var($this->input('has_cnps_number'), FILTER_VALIDATE_BOOLEAN);

        return [
            // Champs optionnels (peuvent venir du contexte session/props)
            'user_type'             => ['nullable', 'string', 'in:particulier,entreprise'],
            'service_key'           => ['nullable', 'string', 'max:100'],

            // Infos salarié
            'has_cnps_number'       => ['required', 'in:true,false,1,0'],
            'employee_cnps_number'  => [
                $hasCnps ? 'required' : 'nullable',
                'string',
                'max:12',
                'regex:/^\d{12}$/', // Exactement 12 chiffres
            ],
            'employee_last_name'    => ['required', 'string', 'max:100'],
            'employee_first_name'   => ['required', 'string', 'max:100'],
            'employee_nif'          => ['nullable', 'string', 'max:50'],
            'hire_date'             => ['required', 'date', 'before_or_equal:today'],

            // Infos employeur
            'employer_name'         => ['required', 'string', 'max:200'],
            'employer_cnps_number'  => ['required', 'string', 'max:20'],
            'employer_registration' => ['nullable', 'string', 'max:100'],

            // Détails contrat
            'contract_type'         => ['required', 'string', 'in:cdi,cdd,interim,stage,apprentissage,essai'],
            'monthly_gross_salary'  => ['required', 'numeric', 'min:1'],
            'professional_category' => ['required', 'string', 'in:ouvrier,technicien,agent_maitrise,cadre,dirigeant'],
            'declaration_type'      => ['required', 'string', 'in:normale,rectificative,complementaire'],

            // Fichiers CNI — clés plates (pas documents.cni_recto)
            'cni_recto'             => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // 5 Mo max
            'cni_verso'             => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_cnps_number.regex'    => 'Le numéro CNPS doit contenir exactement 12 chiffres.',
            'hire_date.before_or_equal'     => "La date d'embauche ne peut pas être dans le futur.",
            'monthly_gross_salary.min'      => 'Le salaire brut doit être supérieur à 0.',
            'cni_recto.required'            => 'Le recto de la CNI est obligatoire.',
            'cni_verso.required'            => 'Le verso de la CNI est obligatoire.',
            'cni_recto.mimes'               => 'Le recto de la CNI doit être un fichier JPG, PNG ou PDF.',
            'cni_verso.mimes'               => 'Le verso de la CNI doit être un fichier JPG, PNG ou PDF.',
            'cni_recto.max'                 => 'Le recto de la CNI ne doit pas dépasser 5 Mo.',
            'cni_verso.max'                 => 'Le verso de la CNI ne doit pas dépasser 5 Mo.',
            'contract_type.in'              => 'Type de contrat invalide.',
            'professional_category.in'      => 'Catégorie professionnelle invalide.',
            'declaration_type.in'           => 'Type de déclaration invalide.',
        ];
    }

    public function attributes(): array
    {
        return [
            'employee_last_name'    => 'nom du salarié',
            'employee_first_name'   => 'prénom du salarié',
            'employer_name'         => "nom de l'entreprise",
            'employer_cnps_number'  => "matricule CNPS de l'employeur",
            'hire_date'             => "date d'embauche",
            'contract_type'         => 'type de contrat',
            'monthly_gross_salary'  => 'salaire brut mensuel',
            'professional_category' => 'catégorie professionnelle',
        ];
    }
}
