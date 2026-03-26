<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCnpsDeclarationRequest;
use App\Models\CnpsRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CnpsRequestController extends Controller
{
    public function submit(StoreCnpsDeclarationRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $folderPath = "cnps_documents/user_{$user->id}";

        // BUG #3 FIX: Upload des fichiers AVANT la transaction.
        // Justification : Storage::putFile est idempotent côté disque,
        // mais un rollback DB ne peut pas "déstocker" un fichier déjà écrit.
        // Stratégie correcte :
        //   1. Uploader les fichiers d'abord (hors transaction)
        //   2. Ouvrir la transaction DB
        //   3. Si la DB échoue → supprimer les fichiers uploadés dans le catch
        //   4. Si tout réussit → les chemins sont persistés en DB

        $cniRectoPath = $request->file('cni_recto')->store($folderPath, 'public');
        $cniVersoPath = $request->file('cni_verso')->store($folderPath, 'public');

        try {
            $cnpsRequest = DB::transaction(function () use ($validated, $user, $cniRectoPath, $cniVersoPath) {
                return CnpsRequest::create([
                    'user_id'               => $user->id,
                    'user_type'             => $validated['user_type'] ?? ($user->type ?? 'particulier'),
                    'service_key'           => $validated['service_key'] ?? 'declaration_embauche',
                    'has_cnps_number'       => filter_var($validated['has_cnps_number'], FILTER_VALIDATE_BOOLEAN),
                    'employee_cnps_number'  => $validated['employee_cnps_number'] ?? null,
                    'employee_last_name'    => $validated['employee_last_name'],
                    'employee_first_name'   => $validated['employee_first_name'],
                    'employee_nif'          => $validated['employee_nif'] ?? null,
                    'hire_date'             => $validated['hire_date'],
                    'employer_name'         => $validated['employer_name'],
                    'employer_registration' => $validated['employer_registration'] ?? null,
                    'employer_cnps_number'  => $validated['employer_cnps_number'],
                    'contract_type'         => $validated['contract_type'],
                    'monthly_gross_salary'  => $validated['monthly_gross_salary'],
                    'professional_category' => $validated['professional_category'],
                    'declaration_type'      => $validated['declaration_type'],
                    'cni_recto_path'        => $cniRectoPath,
                    'cni_verso_path'        => $cniVersoPath,
                ]);
            });
        } catch (\Throwable $e) {
            // Nettoyage des fichiers orphelins si la DB a planté
            Storage::disk('public')->delete([$cniRectoPath, $cniVersoPath]);
            throw $e; // Laisse le handler Laravel gérer l'erreur (500 ou log)
        }

        // BUG #4 FIX: redirect() est HORS de la transaction.
        // DB::transaction() retourne la valeur du callback, donc
        // on redirige après avoir récupéré $cnpsRequest.
        return redirect()->route('cnps.success', $cnpsRequest->reference);
    }

    public function success(string $reference)
    {
        $declaration = CnpsRequest::where('reference', $reference)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return Inertia::render('CNPS/Success', [
            'declaration' => $declaration,
        ]);
    }
}
