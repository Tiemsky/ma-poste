<?php

namespace App\Http\Controllers;

use App\Models\CnpsRequest;
use App\Models\CustomsRequest;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        // 🔹 Requêtes avec relations (eager loading pour éviter N+1)
        $cnpsRequests = CnpsRequest::with('service.administration')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        $customsRequests = CustomsRequest::with('service.administration')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        // 🔹 Statistiques globales combinées
        $statistics = $this->calculateGlobalStatistics($userId);

        // 🔹 Services uniques liés à l'utilisateur (pour les filtres/affichage)
        $services = $this->getUserServices($userId);



        return Inertia::render('Dashboard', [
            'statistics' => $statistics,
            'requests' => [
                'cnps' => $cnpsRequests,
                'customs' => $customsRequests,
            ],
            'services' => $services,
            'status_labels' => [
                'pending' => 'En attente',
                'approved' => 'Approuvée',
                'rejected' => 'Rejetée',
            ],
        ]);
    }

    /**
     * Calcule les statistiques globales CNPS + Customs
     */
    private function calculateGlobalStatistics(int $userId): array
    {
        $statuses = ['pending', 'approved', 'rejected'];
        $stats = array_fill_keys($statuses, 0);

        // Stats CNPS
        $cnpsStats = CnpsRequest::where('user_id', $userId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Stats Customs
        $customsStats = CustomsRequest::where('user_id', $userId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Fusion
        foreach ($statuses as $status) {
            $stats[$status] = ($cnpsStats[$status] ?? 0) + ($customsStats[$status] ?? 0);
        }

        $total = array_sum($stats);

        return [
            'total' => $total,
            'pending' => $stats['pending'],
            'approved' => $stats['approved'],
            'rejected' => $stats['rejected'],
            // Pour les graphiques/cards frontend
            'breakdown' => [
                ['label' => 'En attente', 'value' => $stats['pending'], 'status' => 'pending', 'color' => '#f59e0b'],
                ['label' => 'Approuvée', 'value' => $stats['approved'], 'status' => 'approved', 'color' => '#10b981'],
                ['label' => 'Rejetée', 'value' => $stats['rejected'], 'status' => 'rejected', 'color' => '#ef4444'],
            ]
        ];
    }

    /**
     * Récupère les services uniques liés à l'utilisateur
     */
    private function getUserServices(int $userId): array
    {
        $serviceIds = CnpsRequest::where('user_id', $userId)->pluck('service_id')
            ->merge(CustomsRequest::where('user_id', $userId)->pluck('service_id'))
            ->unique()
            ->filter(); // retire les null

        if ($serviceIds->isEmpty()) {
            return [];
        }

        return Service::with('administration')
            ->whereIn('id', $serviceIds)
            ->get()
            ->map(fn($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'key' => $service->key,
                'administration' => $service->administration ? [
                    'id' => $service->administration->id,
                    'name' => $service->administration->name,
                    'key' => $service->administration->key,
                ] : null,
            ])
            ->values()
            ->toArray();
    }
}
