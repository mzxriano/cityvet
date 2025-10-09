<?php

namespace App\Http\Controllers\Web;

use App\Models\AnimalArchive;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ArchiveController extends Controller
{
    /**
     * Display a listing of archived animals.
     */
    public function index(Request $request)
    {
        $query = AnimalArchive::with(['animal.user']);

        // Filter by archive type (deceased/deleted)
        if ($request->filled('archive_type')) {
            $query->where('archive_type', $request->archive_type);
        }

        // Filter by animal type
        if ($request->filled('type')) {
            $query->whereHas('animal', function ($q) use ($request) {
                $q->where('type', $request->type);
            });
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('animal', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('breed', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })->orWhereHas('animal.user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            })->orWhere('reason', 'like', "%{$search}%");
        }

        $archives = $query->orderBy('created_at', 'desc')->paginate(15)->appends(request()->query());

        return view('admin.archives', compact('archives'));
    }

    /**
     * Display memorial page for a deceased animal.
     */
    public function memorial($id)
    {
        $archive = AnimalArchive::with(['animal.user'])
                                ->where('id', $id)
                                ->where('archive_type', 'deceased')
                                ->firstOrFail();

        return view('admin.archives.memorial', compact('archive'));
    }

    /**
     * Display record page for a deleted animal.
     */
    public function record($id)
    {
        $archive = AnimalArchive::with(['animal.user'])
                                ->where('id', $id)
                                ->where('archive_type', 'deleted')
                                ->firstOrFail();

        return view('admin.archives.record', compact('archive'));
    }

    /**
     * Restore an archived animal (only for deleted animals).
     */
    public function restore($id)
    {
        $archive = AnimalArchive::with('animal')
                                ->where('id', $id)
                                ->where('archive_type', 'deleted') // Only deleted animals can be restored
                                ->firstOrFail();

        try {
            \DB::transaction(function () use ($archive) {
                // Update animal status back to alive
                $archive->animal->update(['status' => 'alive']);
                
                // Delete the archive record
                $archive->delete();
            });

            return redirect()->route('admin.archives')
                           ->with('success', "Animal '{$archive->animal->name}' has been successfully restored.");
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Failed to restore animal: ' . $e->getMessage());
        }
    }
}
