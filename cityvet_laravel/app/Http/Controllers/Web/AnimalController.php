<?php

namespace App\Http\Controllers\Web;

use App\Models\Animal;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;

class AnimalController extends Controller
{
    /**
     * Display a list of animals through json response.
     */
    public function index(Request $request)
    {
                
        $query = Animal::with('user');

        if($request->filled('type')){
            $query->where('type', $request->input('type'));
        }

        if($request->filled('gender')){
            $query->where('gender', $request->input('gender'));
        }

        if($request->filled('search')){
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Handle pagination
        $perPage = $request->filled('per_page') ? $request->per_page : 10;
        
        if ($perPage === 'all') {
            $animals = $query->get();
            // Create a mock paginator for "all" results
            $animals = new \Illuminate\Pagination\LengthAwarePaginator(
                $animals,
                $animals->count(),
                $animals->count(),
                1,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
            $animals->appends(request()->query());
        } else {
            $animals = $query->paginate((int)$perPage)->appends(request()->query());
        }

        return view("admin.animals", compact("animals"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Check if we're receiving multiple animals
            if ($request->has('animals') && is_array($request->input('animals'))) {
                return $this->storeMultiple($request);
            }

            // Single animal validation
            $validated = $request->validate([
                'type'       => 'required|string',
                'breed'      => 'required|string',
                'name'       => 'required|string',
                'birth_date' => 'nullable|date',
                'gender'     => 'required|in:male,female',
                'weight'     => 'nullable|numeric',
                'height'     => 'nullable|numeric',
                'color'      => 'required|string',
                'unique_spot' => 'nullable|string|max:255',
                'known_conditions' => 'nullable|string|max:255',
                'user_id'   => 'required|exists:users,id',
            ]);
            
            $animal = Animal::create($validated);

            // Check if request is AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Animal registered successfully!',
                    'animal' => $animal
                ]);
            }

            return redirect()->route('admin.animals')->with('success', 'Animal added successfully.');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while registering the animal'
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while registering the animal');
        }
    }

    /**
     * Store multiple animals from the modal form.
     */
    private function storeMultiple(Request $request)
    {
        try {
            // Validate the animals array
            $validated = $request->validate([
                'animals' => 'required|array|min:1|max:10',
                'animals.*.type' => 'required|string',
                'animals.*.breed' => 'required|string',
                'animals.*.name' => 'required|string',
                'animals.*.birth_date' => 'nullable|date',
                'animals.*.gender' => 'required|in:male,female',
                'animals.*.weight' => 'nullable|numeric',
                'animals.*.height' => 'nullable|numeric',
                'animals.*.color' => 'required|string',
                'animals.*.unique_spot' => 'nullable|string|max:255',
                'animals.*.known_conditions' => 'nullable|string|max:255',
                'animals.*.user_id' => 'required|exists:users,id',
            ]);

            $animalsCreated = 0;
            $createdAnimals = [];

            DB::beginTransaction();
            
            foreach ($validated['animals'] as $animalData) {
                // Remove empty values
                $animalData = array_filter($animalData, function($value) {
                    return $value !== '' && $value !== null;
                });

                $animal = Animal::create($animalData);
                $createdAnimals[] = $animal;
                $animalsCreated++;
            }

            DB::commit();

            // Check if request is AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$animalsCreated} animal" . ($animalsCreated > 1 ? 's' : '') . " registered successfully!",
                    'animals' => $createdAnimals,
                    'count' => $animalsCreated
                ]);
            }

            return redirect()->route('admin.animals')
                ->with('success', "{$animalsCreated} animal" . ($animalsCreated > 1 ? 's' : '') . " added successfully.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while registering the animals'
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while registering the animals');
        }
    }

    /**
     * Show batch registration form.
     */
    public function showBatchRegistration()
    {
        return view('admin.animals-batch-register');
    }

    /**
     * Store multiple animals from batch registration.
     */
    public function batchStore(Request $request)
    {
        $request->validate([
            'common_user_id' => 'required|exists:users,id',
            'common_type' => 'required|string',
            'common_breed' => 'required|string',
            'animals' => 'required|array|min:1|max:100',
            'animals.*.name' => 'required|string|max:100',
            'animals.*.gender' => 'nullable|in:male,female',
            'animals.*.color' => 'nullable|string|max:100',
            'animals.*.weight' => 'nullable|numeric|min:0',
            'animals.*.height' => 'nullable|numeric|min:0',
            'animals.*.birth_date' => 'nullable|date',
            'animals.*.unique_spot' => 'nullable|string|max:255',
            'animals.*.known_conditions' => 'nullable|string|max:255',
        ]);

        $commonData = [
            'user_id' => $request->common_user_id,
            'type' => $request->common_type,
            'breed' => $request->common_breed,
            'gender' => $request->common_gender,
            'color' => $request->common_color,
            'birth_date' => $request->common_birth_date,
        ];

        $animalsCreated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->animals as $index => $animalData) {
                // Merge common data with individual animal data
                $finalData = array_filter(array_merge($commonData, [
                    'name' => $animalData['name'],
                    'gender' => $animalData['gender'] ?: $commonData['gender'],
                    'color' => $animalData['color'] ?: $commonData['color'],
                    'weight' => $animalData['weight'] ?? null,
                    'height' => $animalData['height'] ?? null,
                    'birth_date' => $animalData['birth_date'] ?: $commonData['birth_date'],
                    'unique_spot' => $animalData['unique_spot'] ?? null,
                    'known_conditions' => $animalData['known_conditions'] ?? null,
                ]));

                // Remove empty string values
                $finalData = array_filter($finalData, function($value) {
                    return $value !== '' && $value !== null;
                });

                // Ensure required fields are present
                if (empty($finalData['gender'])) {
                    $errors[] = "Animal #" . ($index + 1) . " is missing gender";
                    continue;
                }
                if (empty($finalData['color'])) {
                    $errors[] = "Animal #" . ($index + 1) . " is missing color";
                    continue;
                }

                Animal::create($finalData);
                $animalsCreated++;
            }

            if (count($errors) > 0) {
                DB::rollBack();
                return redirect()->back()
                    ->withErrors($errors)
                    ->withInput()
                    ->with('error', 'Some animals could not be registered. Please fix the errors and try again.');
            }

            DB::commit();
            return redirect()->route('admin.animals')
                ->with('success', "{$animalsCreated} animals registered successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while registering animals: ' . $e->getMessage());
        }
    }

    /**
     * Import animals from CSV file.
     */
    public function csvImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file->getPathname()));
        $header = array_shift($csvData); // Remove header row

        // Required columns
        $requiredColumns = ['owner_email', 'type', 'breed', 'name', 'gender', 'color'];
        
        // Validate CSV header
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $header)) {
                return redirect()->back()->with('error', "Missing required column: {$column}");
            }
        }

        $animalsCreated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($csvData as $rowIndex => $row) {
                if (count($row) != count($header)) {
                    $errors[] = "Row " . ($rowIndex + 2) . " has incorrect number of columns";
                    continue;
                }

                $data = array_combine($header, $row);
                
                // Find user by email
                $user = User::where('email', $data['owner_email'])->first();
                if (!$user) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": Owner with email '{$data['owner_email']}' not found";
                    continue;
                }

                // Prepare animal data
                $animalData = [
                    'user_id' => $user->id,
                    'type' => $data['type'],
                    'breed' => $data['breed'],
                    'name' => $data['name'],
                    'gender' => $data['gender'],
                    'color' => $data['color'],
                    'birth_date' => !empty($data['birth_date']) ? $data['birth_date'] : null,
                    'weight' => !empty($data['weight']) ? (float)$data['weight'] : null,
                    'height' => !empty($data['height']) ? (float)$data['height'] : null,
                    'unique_spot' => !empty($data['unique_spot']) ? $data['unique_spot'] : null,
                    'known_conditions' => !empty($data['known_conditions']) ? $data['known_conditions'] : null,
                ];

                // Validate required fields
                foreach ($requiredColumns as $column) {
                    if (empty($animalData[str_replace('owner_email', 'user_id', $column)])) {
                        $errors[] = "Row " . ($rowIndex + 2) . ": Missing required field '{$column}'";
                        continue 2;
                    }
                }

                Animal::create($animalData);
                $animalsCreated++;
            }

            if (count($errors) > 5) { // If too many errors, rollback
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Too many errors in CSV file. Please check the format and try again.')
                    ->with('csv_errors', array_slice($errors, 0, 10));
            } else if (count($errors) > 0 && $animalsCreated == 0) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'No animals could be imported due to errors.')
                    ->with('csv_errors', $errors);
            }

            DB::commit();
            
            $message = "{$animalsCreated} animals imported successfully!";
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " rows had errors and were skipped.";
            }

            return redirect()->route('admin.animals')
                ->with('success', $message)
                ->with('csv_errors', $errors);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'An error occurred while importing animals: ' . $e->getMessage());
        }
    }

    /**
     * Download CSV template for batch import.
     */
    public function csvTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="animal-import-template.csv"',
        ];

        $csvData = [
            ['owner_email', 'type', 'breed', 'name', 'gender', 'color', 'birth_date', 'weight', 'height', 'unique_spot', 'known_conditions'],
            ['john@example.com', 'cattle', 'Holstein', 'Cow #1', 'female', 'Black and White', '2023-01-15', '450.5', '140', 'White spot on forehead', ''],
            ['john@example.com', 'cattle', 'Holstein', 'Cow #2', 'male', 'Black', '2022-12-20', '500.0', '145', '', 'Vaccinated'],
            ['jane@example.com', 'chicken', 'Rhode Island Red', 'Hen #1', 'female', 'Reddish-brown', '2024-03-10', '2.5', '25', '', ''],
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {   
        $animal = Animal::find($id);
        $vaccines = $animal->vaccines;
        return view('admin.animals_view', compact('animal', 'vaccines'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'type'       => 'required|string',
            'breed'      => 'required|string',
            'name'       => 'required|string',
            'birth_date' => 'nullable|date',
            'gender'     => 'required|in:male,female',
            'weight'     => 'nullable|numeric',
            'height'     => 'nullable|numeric',
            'color'      => 'required|string',
            'unique_spot' => 'nullable|string|max:255',
            'known_conditions' => 'nullable|string|max:255',
            'user_id'   => 'required|exists:users,id',
        ]);

        $animal = Animal::findOrFail($id);
        $animal->update($validated);

        return redirect()->route('admin.animals')->with('success', 'Animal updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
