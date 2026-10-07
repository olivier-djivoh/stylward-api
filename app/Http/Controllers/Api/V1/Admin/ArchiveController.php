<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeletedAccountArchive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $archives = DeletedAccountArchive::query()
            ->when($request->reason, fn ($q) => $q->where('deletion_reason', $request->reason))
            ->orderByDesc('deleted_at')
            ->paginate(20);

        return response()->json($archives);
    }

    public function stats(): JsonResponse
    {
        $total = DeletedAccountArchive::count();

        // Par motif
        $byReason = DeletedAccountArchive::query()
            ->whereNotNull('deletion_reason')
            ->selectRaw('deletion_reason, COUNT(*) as count')
            ->groupBy('deletion_reason')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => [
                'reason' => $r->deletion_reason,
                'label'  => DeletedAccountArchive::reasonLabels()[$r->deletion_reason] ?? $r->deletion_reason,
                'count'  => $r->count,
            ]);

        // Totaux agrégés (moyennes)
        $averages = [
            'avg_models'      => round(DeletedAccountArchive::avg('models_count') ?? 0, 1),
            'avg_views'       => round(DeletedAccountArchive::avg('shop_views_total') ?? 0, 0),
            'avg_visitors'    => round(DeletedAccountArchive::avg('shop_visitors_total') ?? 0, 0),
            'avg_whatsapp'    => round(DeletedAccountArchive::avg('shop_whatsapp_clicks') ?? 0, 1),
        ];

        // Motifs par mois (12 derniers mois)
        $byMonth = DeletedAccountArchive::query()
            ->selectRaw("TO_CHAR(deleted_at, 'YYYY-MM') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)
            ->get();

        return response()->json([
            'total'      => $total,
            'by_reason'  => $byReason,
            'averages'   => $averages,
            'by_month'   => $byMonth,
        ]);
    }

    public function clear(): JsonResponse
        {
            $count = DeletedAccountArchive::count();
            DeletedAccountArchive::truncate();

            return response()->json([
                'message' => "{$count} archive(s) supprimée(s).",
                'deleted' => $count,
            ]);
        }
}
