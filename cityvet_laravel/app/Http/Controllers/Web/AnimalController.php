<?php

namespace App\Http\Controllers\Web;

use App\Models\Animal;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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

        $animals = $query->paginate(10)->appends(key: request()->query());

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
        $validated = $request->validate([
            'type'       => 'required|string',
            'breed'      => 'required|string',
            'name'       => 'required|string',
            'birth_date' => 'nullable|date',
            'gender'     => 'required|in:male,female',
            'weight'     => 'nullable|numeric',
            'height'     => 'nullable|numeric',
            'color'      => 'required|string',
            'user_id'   => 'required|exists:users,id',
        ]);
        
        Animal::create($validated);

        return redirect()->route('admin.animals')->with('success', 'Animal added successfully.');
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
        $animal = Animal::findOrFail($id);

        $validated = $request->validate([
            'type'       => 'sometimes|string',
            'breed'      => 'sometimes|string',
            'name'       => 'sometimes|string',
            'birth_date' => 'sometimes|nullable|date',
            'gender'     => 'sometimes|in:male,female',
            'weight'     => 'sometimes|nullable|numeric',
            'height'     => 'sometimes|nullable|numeric',
            'color'      => 'sometimes|string',
            'user_id'   => 'sometimes|exists:users,id',
        ]);

        $animal->type = $validated['type'];
        $animal->breed = $validated['breed'];
        $animal->name = $validated['name'];
        $animal->birth_date = $validated['birth_date'];
        $animal->gender = $validated['gender'];
        $animal->weight = $validated['weight'];
        $animal->height = $validated['height'];
        $animal->color = $validated['color'];
        $animal->user_id = $validated['user_id'];

        $animal->save();

        return redirect()->route('admin.animals')->with('success', 'Animal updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function searchOwner(Request $request){

        $q = $request->query('q', '');
        $users = User::where('first_name', 'like', "%{$q}%")
            ->orWhere('last_name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->limit(10)
            ->get();


        return response()->json($users->map(fn($u) => [
            'id' => $u->id,
            'name' => "{$u->first_name} {$u->last_name}",
            'email' => $u->email,
        ]));

    }
}
