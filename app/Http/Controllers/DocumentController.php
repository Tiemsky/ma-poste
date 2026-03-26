<?php

namespace App\Http\Controllers;

use App\Models\CnpsRequest;
use App\Models\CustomsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DocumentController extends Controller
{
public function index(Request $request)
{
    $user = Auth::user();
    $search = $request->input('search', '');
    $administration = $request->input('administration', 'all');

    // 🔹 Récupérer les requêtes
    $cnpsQuery = CnpsRequest::with('service.administration')
        ->where('user_id', $user->id)
        ->orderByDesc('created_at');

    $customsQuery = CustomsRequest::with('service.administration')
        ->where('user_id', $user->id)
        ->orderByDesc('created_at');

    // 🔹 Utiliser ->toBase() pour transformer la collection Eloquent en collection de base
    // Cela permet de manipuler des tableaux sans que Laravel ne cherche des clés de modèles
    $cnpsDocuments = $cnpsQuery->get()->toBase()->map(fn($req) => $this->transformToDocument($req, 'cnps'));
    $customsDocuments = $customsQuery->get()->toBase()->map(fn($req) => $this->transformToDocument($req, 'guichet'));

    // 🔹 La fusion fonctionnera maintenant normalement sur des tableaux
    $allDocuments = $cnpsDocuments->merge($customsDocuments)
        ->sortByDesc(fn($doc) => $doc['timestamp'])
        ->values();

    // 🔹 Filtrage
    $filteredDocuments = $allDocuments->filter(function ($doc) use ($search, $administration) {
        $matchesSearch = empty($search) || stripos($doc['title'], $search) !== false;
        $matchesAdmin = $administration === 'all' || $doc['admin'] === $administration;
        return $matchesSearch && $matchesAdmin;
    })->values();

    // 🔹 Administrations uniques
    $administrations = $allDocuments
        ->pluck('admin')
        ->unique()
        ->filter()
        ->values();

    return Inertia::render('Document/Index', [
        'documents' => $filteredDocuments->all(),
        'allDocumentsCount' => $allDocuments->count(),
        'administrations' => $administrations->prepend('Tous')->toArray(),
        'filters' => [
            'search' => $search,
            'administration' => $administration,
        ],
        'status_labels' => [
            'valid' => 'Valide',
            'expired' => 'Expiré',
        ],
    ]);
}

    /**
     * Transforme une requête en format document pour le frontend
     */
    private function transformToDocument($request, string $type): array
    {
        $processedAt = $request->created_at ? Carbon::parse($request->created_at) : now();

        //  Document valide si approuvé il y a moins de 12 mois (ajustable)
        // $isValid = $processedAt->gt(Carbon::now()->subYear());

        //  Titre selon le type de demande
        $title = match($type) {
            'cnps' => 'Attestation CNPS - ' . ($request->service?->name ?? 'Service'),
            'guichet' => 'Certificat Douane - ' . ($request->service?->name ?? 'Service'),
            default => $request->reference ?? 'Document',
        };

        //  Taille simulée (à remplacer par stockage réel si besoin)
        $size = rand(500, 3000);
        $sizeFormatted = $size > 1000
            ? number_format($size / 1000, 1) . ' MB'
            : $size . ' KB';

        return [
            'id' => $request->id,
            'reference' => $request->reference,
            'type' => $type, // 'cnps' ou 'customs'
            'title' => $title,
            'admin' => $request->service?->administration?->name ?? 'Non défini',
            'administration_key' => $request->service?->administration?->key ?? null,
            'service_name' => $request->service?->name ?? null,
            'date' => $processedAt->locale('fr')->isoFormat('DD MMMM YYYY'),
            'timestamp' => $processedAt->toISOString(),
            'type_file' => 'PDF',
            'size' => $sizeFormatted,
            'status' => $request->status,
            // 'expires_at' => $processedAt->copy()->addYear()->toISOString(),
            'download_url' => route('document.download', ['type' => $type, 'id' => $request->id]),
            'view_url' => route('document.show', ['type' => $type, 'id' => $request->id]),
        ];
    }

    /**
     * Afficher/ Télécharger un document spécifique
     */
    public function show(string $type, int $id)
    {
        $user = Auth::user();

        $request = match($type) {
            'cnps' => CnpsRequest::with('service.administration')->findOrFail($id),
            'guichet' => CustomsRequest::with('service.administration')->findOrFail($id),
            default => abort(404),
        };

        // 🔐 Vérifier que le document appartient à l'utilisateur et est approuvé
        if ($request->user_id !== $user->id || $request->status !== 'approved') {
            abort(403, 'Accès non autorisé');
        }

        // 📄 Ici : générer le PDF ou retourner les métadonnées
        // Pour l'exemple, on retourne les infos du document
        return response()->json([
            'success' => true,
            'data' => $this->transformToDocument($request, $type),
            'preview_url' => asset('storage/document/preview-' . $request->reference . '.pdf'),
        ]);
    }

    /**
     * Télécharger le document (PDF généré)
     */
    public function download(string $type, int $id)
    {
        $user = Auth::user();

        $request = match($type) {
            'cnps' => CnpsRequest::findOrFail($id),
            'guichet' => CustomsRequest::findOrFail($id),
            default => abort(404),
        };

        if ($request->user_id !== $user->id || $request->status !== 'approved') {
            abort(403, 'Accès non autorisé');
        }

        // 🖨️ Générer le PDF avec Laravel-Snappy / DomPDF
        // return Pdf::loadView('pdf.document', ['request' => $request])
        //     ->download($request->reference . '.pdf');

        // Pour l'instant, retour temporaire
        return response()->json([
            'message' => 'Téléchargement simulé: ' . $request->reference . '.pdf',
            'reference' => $request->reference,
        ]);
    }
}
