<?php

namespace App\Http\Controllers\Web;

use App\Models\Vaccine;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VaccineController extends Controller
{
    private function getCloudinary()
    {
        return new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
                'secure' => env('CLOUDINARY_SECURE', true),
            ],
        ]);
    }

    public function index(Request $request)
    {
        $query = Vaccine::query();

        if ($request->filled('affected')) {
            $query->where('affected', $request->affected);
        }

        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->where('stock', '<=', 5);
                    break;
                case 'medium':
                    $query->whereBetween('stock', [6, 20]);
                    break;
                case 'high':
                    $query->where('stock', '>', 20);
                    break;
            }
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%$searchTerm%")
                    ->orWhere('description', 'like', "%$searchTerm%")
                    ->orWhere('protect_against', 'like', "%$searchTerm%");
            });
        }

        $vaccines = $query->orderBy('name', 'asc')->get();
        return view('admin.vaccines', compact('vaccines'));
    }

    public function create()
    {
        return view('admin.vaccines_add');
    }

    public function store(Request $request)
    {

        $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'stock'           => 'nullable|integer',
            'protect_against' => 'nullable|string',
            'affected'        => 'nullable|string',
            'schedule'        => 'nullable|string',
            'expiration_date' => 'nullable|date',
            'image'           => 'nullable|image|mimes:jpg,png,jpeg,webp|max:2048',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            try {
                $cloudinary = $this->getCloudinary();

                $uploadResult = $cloudinary->uploadApi()->upload(
                    $request->file('image')->getPathname(),
                    [
                        'folder' => 'vaccines',
                        'transformation' => [
                            'width' => 800,
                            'height' => 600,
                            'crop' => 'limit',
                            'quality' => 'auto',
                            'fetch_format' => 'auto'
                        ]
                    ]
                );

                $data['image_url'] = $uploadResult['secure_url'];
                $data['image_public_id'] = $uploadResult['public_id'];

                \Log::info('File received', [
                    'original_name' => $request->file('image')->getClientOriginalName(),
                    'mime_type' => $request->file('image')->getMimeType(),
                    'size' => $request->file('image')->getSize()
                ]);


            } catch (\Exception $e) {
                return back()->withErrors(['image' => 'Image upload failed: ' . $e->getMessage()])
                             ->withInput();
            }
        }

        Vaccine::create($data);

        return redirect()->route('admin.vaccines')->with('success', 'Vaccine added successfully!');
    }

    public function show(string $id)
    {
        $vaccine = Vaccine::find($id);
        return view('admin.vaccines_view', compact('vaccine'));
    }

    public function edit(string $id)
    {
        $vaccine = Vaccine::findOrFail($id);
        return response()->json($vaccine);
    }

    public function update(Request $request, string $id)
    {

                \Log::info('Store method called', [
            'has_file' => $request->hasFile('image'),
            'all_files' => $request->allFiles(),
            'all_data' => $request->all(),
            'php_upload_max' => ini_get('upload_max_filesize'),
            'php_post_max' => ini_get('post_max_size'),
            'php_file_uploads' => ini_get('file_uploads'),
        ]);
        $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'stock'           => 'nullable|integer',
            'protect_against' => 'nullable|string',
            'affected'        => 'nullable|string',
            'schedule'        => 'nullable|string',
            'expiration_date' => 'nullable|date',
            'image'           => 'nullable|image|mimes:jpg,png,jpeg,webp|max:2048',
        ]);

        $vaccine = Vaccine::findOrFail($id);
        $data = $request->except('image');

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            try {
                $cloudinary = $this->getCloudinary();

                // Delete old image if exists
                if ($vaccine->image_public_id) {
                    $cloudinary->uploadApi()->destroy($vaccine->image_public_id);
                }

                $uploadResult = $cloudinary->uploadApi()->upload(
                    $request->file('image')->getPathname(),
                    [
                        'folder' => 'vaccines',
                        'transformation' => [
                            'width' => 800,
                            'height' => 600,
                            'crop' => 'limit',
                            'quality' => 'auto',
                            'fetch_format' => 'auto'
                        ]
                    ]
                );

                $data['image_url'] = $uploadResult['secure_url'];
                $data['image_public_id'] = $uploadResult['public_id'];

            } catch (\Exception $e) {
                return back()->withErrors(['image' => 'Image upload failed: ' . $e->getMessage()])
                             ->withInput();
            }
        }

        $vaccine->update($data);

        return redirect()->route('admin.vaccines')->with('success', 'Vaccine updated successfully!');
    }

    public function destroy(string $id)
    {
        $vaccine = Vaccine::findOrFail($id);

        if ($vaccine->image_public_id) {
            $this->getCloudinary()->uploadApi()->destroy($vaccine->image_public_id);
        }

        $vaccine->delete();
        return redirect()->route('admin.vaccines')->with('success', 'Vaccine deleted successfully!');
    }

    public function updateStock(Request $request, Vaccine $vaccine)
    {
        $validated = $request->validate([
            'action'   => 'required|in:add,remove',
            'quantity' => 'required|integer|min:1',
            'reason'   => 'nullable|string|max:255',
        ]);

        $quantity = $validated['quantity'];
        if ($validated['action'] === 'remove') {
            $quantity = -$quantity;
        }

        $vaccine->stock = max(0, $vaccine->stock + $quantity);
        $vaccine->save();

        return back()->with('success', 'Stock updated successfully');
    }
    
}
