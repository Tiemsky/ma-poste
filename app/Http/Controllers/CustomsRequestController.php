<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomsDeclarationRequest;
use App\Models\CustomsItem;
use App\Models\CustomsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CustomsRequestController extends Controller
{
        public function store(StoreCustomsDeclarationRequest $request)
    {


        $validated = $request->validated();
        $user      = Auth::user();
        $folder    = "customs_documents/user_{$user->id}";

        // 1. Uploads AVANT la transaction (même pattern que CNPS)
        $cniRectoPath  = $request->file('cni_recto')->store($folder, 'public');
        $cniVersoPath  = $request->file('cni_verso')->store($folder, 'public');
        $invoicePath   = $request->file('invoice')->store($folder, 'public');

        try {
            $customsRequest = DB::transaction(function () use ($validated, $user, $cniRectoPath, $cniVersoPath, $invoicePath) {

                // 2. Créer la demande principale
                $customsRequest = CustomsRequest::create([
                    'key'               => Str::uuid(),
                    'user_id'           => $user->id,
                    'service_id'        => $validated['service_id'],
                    'transport_mode'    => $validated['transport_mode'],
                    'departure_country' => $validated['departure_country'],
                    'arrival_country'   => $validated['arrival_country'],
                    'transport_company' => $validated['transport_company'],
                    'tracking_number'   => $validated['tracking_number'] ?? null,
                    'invoice_path'      => $invoicePath,
                    'declarant_type'    => $validated['declarant_type'] === 'individual' ? 'particulier' : 'entreprise',
                    'declarant_name'      =>  $validated['declarant_name'] === 'company' ?? Auth::user()->last_name . ' '. Auth::user()->first_name  ,
                    'declarant_id_number' => $validated['declarant_id_number'] ?? null,
                    'cni_recto' => $cniRectoPath,
                    'cni_verso' => $cniVersoPath
                ]);

                // 3. Créer les articles (items_json décodé)
                $items = json_decode($validated['items_json'], true);
                foreach ($items as $item) {
                    CustomsItem::create([
                        'customs_request_id' => $customsRequest->id,
                        'name'               => $item['name'],
                        'category'           => $item['category'],
                        'quantity'           => $item['quantity'],
                        'unit_value'         => $item['unit_value'],
                        'total_value'        => $item['total_value'],
                        'origin_country'     => $item['origin_country'],
                        'weight'             => $item['weight'] ?? null,
                    ]);
                }

                return $customsRequest;
            });
        } catch (\Throwable $e) {
            // Nettoyage fichiers orphelins si DB échoue
            Storage::disk('public')->delete([$cniRectoPath, $cniVersoPath, $invoicePath]);
            throw $e;
        }

        return redirect()->route('guichet.douane.success', $customsRequest->reference);
    }

    public function success(string $reference)
    {
        $declaration = CustomsRequest::with('items')
            ->where('reference', $reference)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return Inertia::render('Douane/Success', [
            'declaration' => $declaration,
        ]);
    }
}
