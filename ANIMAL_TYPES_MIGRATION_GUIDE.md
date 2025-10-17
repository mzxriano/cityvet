# Animal Types and Breeds Database Migration Guide

## Overview
This guide documents the migration from hardcoded animal types and breeds to a database-driven system. This allows you to manage animal types and breeds through a CMS interface.

## What's Changed

### Before
- Animal types and breeds were hardcoded in controllers, views, and mobile app
- Adding new types required code changes across multiple files
- No centralized management

### After
- Animal types and breeds stored in database tables
- Manageable through API endpoints
- Easy to add/edit/remove through CMS
- Mobile app can fetch types and breeds dynamically

## Database Structure

### Tables Created

1. **animal_types**
   - `id` - Primary key
   - `name` - Lowercase identifier (e.g., 'dog', 'cat')
   - `display_name` - Display name (e.g., 'Dog', 'Cat')
   - `category` - Category: 'pet', 'livestock', 'poultry'
   - `icon` - Icon name for UI
   - `description` - Optional description
   - `is_active` - Active status
   - `sort_order` - Display order
   - `timestamps`

2. **animal_breeds**
   - `id` - Primary key
   - `animal_type_id` - Foreign key to animal_types
   - `name` - Breed name (e.g., 'Aspin', 'Persian')
   - `description` - Optional description
   - `is_active` - Active status
   - `sort_order` - Display order
   - `timestamps`

3. **animals table updates**
   - Added `animal_type_id` - Foreign key to animal_types
   - Added `animal_breed_id` - Foreign key to animal_breeds
   - Keep existing `type` and `breed` columns for backward compatibility

## Migration Steps

### Step 1: Run Migrations
```bash
cd cityvet_laravel

# Run the migrations
php artisan migrate

# Seed the initial data
php artisan db:seed --class=AnimalTypesAndBreedsSeeder
```

### Step 2: Verify Data
Check that data was seeded correctly:
```bash
php artisan tinker
```
```php
// Check animal types
\App\Models\AnimalType::with('breeds')->get();

// Check specific type
\App\Models\AnimalType::where('name', 'dog')->with('breeds')->first();
```

### Step 3: Test API Endpoints

**Get all animal types with breeds:**
```bash
GET /api/animal-types
```

**Get breeds for a specific type:**
```bash
GET /api/animal-types/{id}/breeds
GET /api/animal-types/by-name/dog/breeds
```

**Add new animal type (Admin only):**
```bash
POST /api/animal-types
{
    "name": "rabbit",
    "display_name": "Rabbit",
    "category": "pet",
    "icon": "pets",
    "description": "Domesticated rabbits",
    "is_active": true,
    "sort_order": 8
}
```

**Add new breed (Admin only):**
```bash
POST /api/animal-types/{typeId}/breeds
{
    "name": "Angora",
    "description": "Long-haired breed",
    "is_active": true,
    "sort_order": 0
}
```

## Updating Your Code

### Laravel Controllers

**Old way:**
```php
$breedOptions = [
    'dog' => ['Aspin', 'Shih Tzu', ...],
    'cat' => ['Puspin', 'Persian', ...],
];
```

**New way:**
```php
use App\Models\AnimalType;

// Get all types with breeds
$animalTypes = AnimalType::with('activeBreeds')->active()->ordered()->get();

// Get breeds for specific type
$dogBreeds = AnimalType::where('name', 'dog')->first()->activeBreeds;
```

### Laravel Views

**Pass to view:**
```php
$animalTypes = AnimalType::with('activeBreeds')->active()->ordered()->get();
return view('admin.animals', compact('animalTypes'));
```

**In Blade:**
```php
@foreach($animalTypes as $type)
    <optgroup label="{{ ucfirst($type->category) }}">
        <option value="{{ $type->name }}">{{ $type->display_name }}</option>
    </optgroup>
@endforeach

<!-- For breeds dropdown -->
<select name="breed" id="breed-select">
    @foreach($selectedType->activeBreeds as $breed)
        <option value="{{ $breed->name }}">{{ $breed->name }}</option>
    @endforeach
</select>
```

### Flutter/Dart Mobile App

**Create service method:**
```dart
// In animal_type_service.dart
class AnimalTypeService {
  final Dio _dio = Dio(BaseOptions(baseUrl: ApiConstant.baseUrl));

  Future<Map<String, List<String>>> fetchAnimalTypesAndBreeds() async {
    try {
      final response = await _dio.get('/api/animal-types');
      
      Map<String, List<String>> typesAndBreeds = {};
      
      for (var type in response.data['data']) {
        List<String> breeds = [];
        for (var breed in type['breeds']) {
          breeds.add(breed['name']);
        }
        typesAndBreeds[type['display_name']] = breeds;
      }
      
      return typesAndBreeds;
    } catch (e) {
      throw Exception('Failed to fetch animal types: $e');
    }
  }
}
```

**Use in widget:**
```dart
class AnimalForm extends StatefulWidget {
  @override
  _AnimalFormState createState() => _AnimalFormState();
}

class _AnimalFormState extends State<AnimalForm> {
  Map<String, List<String>> petBreeds = {};
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadAnimalTypes();
  }

  Future<void> _loadAnimalTypes() async {
    try {
      final service = AnimalTypeService();
      final data = await service.fetchAnimalTypesAndBreeds();
      setState(() {
        petBreeds = data;
        isLoading = false;
      });
    } catch (e) {
      // Handle error
      print('Error loading animal types: $e');
      setState(() => isLoading = false);
    }
  }

  // Rest of your form code...
}
```

## CMS Interface (To Be Implemented)

Create admin pages for:

1. **Animal Types Management**
   - List all types
   - Add new type
   - Edit existing type
   - Activate/deactivate type
   - Reorder types

2. **Breeds Management**
   - View breeds by type
   - Add new breed to type
   - Edit breed
   - Activate/deactivate breed
   - Reorder breeds

### Example Web Controller

```php
namespace App\Http\Controllers\Web;

use App\Models\AnimalType;
use App\Models\AnimalBreed;
use Illuminate\Http\Request;

class AnimalTypeManagementController extends Controller
{
    public function index()
    {
        $types = AnimalType::with('breeds')->ordered()->get();
        return view('admin.animal-types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.animal-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:animal_types',
            'display_name' => 'required',
            'category' => 'required|in:pet,livestock,poultry',
            // ... other fields
        ]);

        AnimalType::create($validated);
        
        return redirect()->route('admin.animal-types.index')
            ->with('success', 'Animal type created successfully');
    }

    // ... other CRUD methods
}
```

## Backward Compatibility

The migration maintains backward compatibility:
- Old `type` and `breed` string columns remain in the database
- Both old and new systems can coexist during transition
- Frontend can be updated gradually

To fully transition:
1. Update all create/update operations to use foreign keys
2. Update all views to use relationships
3. Update mobile app to fetch dynamically
4. Once confirmed working, you can remove old string columns (optional)

## Benefits

1. **Easier Management**: Add new animal types without code changes
2. **Consistency**: Single source of truth for types and breeds
3. **Flexibility**: Easy to customize per deployment
4. **Better Data Integrity**: Foreign key constraints prevent invalid data
5. **Scalability**: Easy to add metadata (descriptions, icons, etc.)
6. **Multi-language Support**: Can add translations table in future

## Troubleshooting

### Migration fails
```bash
# Rollback migrations
php artisan migrate:rollback --step=4

# Check for conflicts
php artisan migrate:status
```

### Data doesn't appear
```bash
# Re-seed
php artisan db:seed --class=AnimalTypesAndBreedsSeeder
```

### API returns empty
- Check authentication middleware
- Verify database has seeded data
- Check API routes are registered

## Next Steps

1. Create CMS web interface for managing types and breeds
2. Update animal registration forms to use new system
3. Update mobile app to fetch types dynamically
4. Add validation to ensure type/breed consistency
5. Consider adding images/icons for each type
6. Add support for custom attributes per animal type

## Files Created/Modified

### New Files
- `database/migrations/2025_01_18_000001_create_animal_types_table.php`
- `database/migrations/2025_01_18_000002_create_animal_breeds_table.php`
- `database/migrations/2025_01_18_000003_add_animal_type_breed_foreign_keys.php`
- `database/migrations/2025_01_18_000004_migrate_existing_animal_data.php`
- `database/seeders/AnimalTypesAndBreedsSeeder.php`
- `app/Models/AnimalType.php`
- `app/Models/AnimalBreed.php`
- `app/Http/Controllers/Api/AnimalTypeController.php`

### Modified Files
- `app/Models/Animal.php` - Added relationships
- `routes/api.php` - Added API routes

## Support

For questions or issues, refer to the Laravel documentation:
- Models: https://laravel.com/docs/eloquent
- Migrations: https://laravel.com/docs/migrations
- Relationships: https://laravel.com/docs/eloquent-relationships
