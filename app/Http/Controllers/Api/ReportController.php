<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function exportParcels(Request $request)
    {
        $query = Parcel::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at');
        if ($request->filled('status_in')) {
            $statusList = collect(explode(',', $request->string('status_in')->toString()))
                ->map(fn ($s) => trim($s))
                ->filter()
                ->values()
                ->all();
            if ($statusList) {
                $query->whereIn('status', $statusList);
            }
        } elseif ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('recipient_name', 'like', "%{$term}%")
                    ->orWhere('tracking_code', 'like', "%{$term}%")
                    ->orWhere('facility_name', 'like', "%{$term}%");
            });
        }
        if ($request->filled('from')) {
            $query->whereDate('updated_at', '>=', $request->string('from')->toString());
        }
        if ($request->filled('to')) {
            $query->whereDate('updated_at', '<=', $request->string('to')->toString());
        }

        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Recipient', 'Status', 'Facility', 'Location', 'Tracking Code', 'Received At']);

            $query->chunk(200, function ($parcels) use ($handle) {
                foreach ($parcels as $parcel) {
                    fputcsv($handle, [
                        $parcel->id,
                        $parcel->recipient_name,
                        $parcel->status,
                        $parcel->facility_name,
                        $parcel->location,
                        $parcel->tracking_code,
                        $parcel->received_at?->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="parcels.csv"');

        return $response;
    }
}
