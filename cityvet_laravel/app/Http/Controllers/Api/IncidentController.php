<?php

namespace App\Http\Controllers\Api;

use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller;

class IncidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Incident::query();

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $query->search($request->search);
            }

            // Apply species filter
            if ($request->has('species') && $request->species) {
                $query->bySpecies($request->species);
            }

            // Apply provocation filter
            if ($request->has('provocation') && $request->provocation) {
                $query->byProvocation($request->provocation);
            }

            // Apply date range filter
            if ($request->has('from_date') && $request->has('to_date')) {
                $query->dateRange($request->from_date, $request->to_date);
            } elseif ($request->has('from_date')) {
                $query->where('incident_time', '>=', $request->from_date);
            } elseif ($request->has('to_date')) {
                $query->where('incident_time', '<=', $request->to_date);
            }

            // Order by most recent first
            $query->orderBy('incident_time', 'desc');

            // Pagination
            $limit = min($request->get('limit', 10), 50); // Max 50 per page
            $incidents = $query->paginate($limit);

            return response()->json([
                'success' => true,
                'message' => 'Incidents retrieved successfully',
                'incidents' => $incidents->items(),
                'total' => $incidents->total(),
                'current_page' => $incidents->currentPage(),
                'total_pages' => $incidents->lastPage(),
                'per_page' => $incidents->perPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve incidents',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'victim_name' => 'required|string|max:255',
                'age' => 'required|integer|min:0|max:150',
                'species' => 'required|string|max:255',
                'bite_provocation' => 'required|string|max:255',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'location_address' => 'required|string|max:500',
                'incident_time' => 'required|date|before_or_equal:now',
                'remarks' => 'nullable|string|max:1000',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $incidentData = $validator->validated();

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $fileName = 'incident_' . time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('incidents', $fileName, 'public');
                $incidentData['photo_path'] = $photoPath;
            }

            // Set reported_by if user is authenticated
            if ($request->user()) {
                $incidentData['reported_by'] = $request->user()->name ?? $request->user()->email;
            }

            // Set initial status as pending for barangay personnel review
            $incidentData['status'] = 'pending';

            $incident = Incident::create($incidentData);

            return response()->json([
                'success' => true,
                'message' => 'Incident reported successfully',
                'incident' => $incident,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to report incident',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $incident = Incident::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Incident retrieved successfully',
                'incident' => $incident,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Incident not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $incident = Incident::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'victim_name' => 'sometimes|required|string|max:255',
                'age' => 'sometimes|required|integer|min:0|max:150',
                'species' => 'sometimes|required|string|max:255',
                'bite_provocation' => 'sometimes|required|string|max:255',
                'latitude' => 'sometimes|required|numeric|between:-90,90',
                'longitude' => 'sometimes|required|numeric|between:-180,180',
                'location_address' => 'sometimes|required|string|max:500',
                'incident_time' => 'sometimes|required|date',
                'remarks' => 'nullable|string|max:1000',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $incidentData = $validator->validated();

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($incident->photo_path && Storage::disk('public')->exists($incident->photo_path)) {
                    Storage::disk('public')->delete($incident->photo_path);
                }

                $photo = $request->file('photo');
                $fileName = 'incident_' . time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('incidents', $fileName, 'public');
                $incidentData['photo_path'] = $photoPath;
            }

            $incident->update($incidentData);

            return response()->json([
                'success' => true,
                'message' => 'Incident updated successfully',
                'incident' => $incident->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update incident',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $incident = Incident::findOrFail($id);

            // Delete photo if exists
            if ($incident->photo_path && Storage::disk('public')->exists($incident->photo_path)) {
                Storage::disk('public')->delete($incident->photo_path);
            }

            $incident->delete();

            return response()->json([
                'success' => true,
                'message' => 'Incident deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete incident',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get incident statistics
     */
    public function statistics(Request $request)
    {
        try {
            $query = Incident::query();

            // Apply date filter if provided
            if ($request->has('from_date') && $request->has('to_date')) {
                $query->dateRange($request->from_date, $request->to_date);
            } else {
                // Default to last 30 days
                $query->recent(30);
            }

            $totalIncidents = $query->count();
            
            // Species breakdown
            $speciesStats = $query->selectRaw('species, COUNT(*) as count')
                                 ->groupBy('species')
                                 ->orderBy('count', 'desc')
                                 ->get()
                                 ->pluck('count', 'species');

            // Provocation breakdown
            $provocationStats = $query->selectRaw('bite_provocation, COUNT(*) as count')
                                     ->groupBy('bite_provocation')
                                     ->orderBy('count', 'desc')
                                     ->get()
                                     ->pluck('count', 'bite_provocation');

            // Recent incidents (last 7 days)
            $recentIncidents = Incident::recent(7)->count();

            // Monthly trend (last 6 months)
            $monthlyTrend = Incident::selectRaw('DATE_FORMAT(incident_time, "%Y-%m") as month, COUNT(*) as count')
                                   ->where('incident_time', '>=', now()->subMonths(6))
                                   ->groupBy('month')
                                   ->orderBy('month')
                                   ->get()
                                   ->pluck('count', 'month');

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'total_incidents' => $totalIncidents,
                'recent_incidents' => $recentIncidents,
                'species_breakdown' => $speciesStats,
                'provocation_breakdown' => $provocationStats,
                'monthly_trend' => $monthlyTrend,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update incident status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $incident = Incident::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,under_review,confirmed,disputed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $newStatus = $request->status;
            $user = $request->user();

            // Update status with proper workflow validation
            if ($newStatus === 'confirmed' && $user) {
                $incident->confirm($user->name ?? $user->email);
            } elseif ($newStatus === 'disputed' && $user) {
                $incident->dispute($user->name ?? $user->email);
            } else {
                $incident->update([
                    'status' => $newStatus,
                    'confirmed_by' => $user ? ($user->name ?? $user->email) : null,
                    'confirmed_at' => ($newStatus === 'confirmed' || $newStatus === 'disputed') ? now() : null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Incident status updated successfully',
                'incident' => $incident->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update incident status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
