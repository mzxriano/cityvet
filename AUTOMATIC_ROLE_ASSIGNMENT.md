# Automatic Role Assignment Feature

## Overview
The CityVet system now automatically assigns appropriate roles to users when they register animals of specific types. This ensures users have the correct permissions and access levels based on the types of animals they own.

## How It Works

### Role Mapping
The system maps animal types to specific user roles:

**Pet Animals:**
- `dog` → `pet_owner`
- `cat` → `pet_owner`

**Livestock Animals:**
- `cattle` → `livestock_owner`
- `goat` → `livestock_owner`
- `carabao` → `livestock_owner`

**Poultry Animals:**
- `chicken` → `poultry_owner`
- `duck` → `poultry_owner`

### When Roles Are Assigned

Automatic role assignment occurs whenever an animal is registered through:

1. **API Animal Registration** (`POST /api/animals`)
2. **API Animal Registration for Owner** (`POST /api/animals/add-for-owner`)
3. **Web Admin Single Animal Registration**
4. **Web Admin Batch Animal Registration**
5. **Web Admin CSV Import**
6. **Web Admin Quantity-based Registration**

### Behavior

- **Smart Assignment**: Roles are only assigned if the user doesn't already have them
- **Multi-Role Support**: Users can have multiple roles (e.g., pet_owner + livestock_owner + poultry_owner)
- **No Duplicates**: The system prevents duplicate role assignments
- **Logging**: All role assignments are logged for audit purposes

### Examples

**Example 1: New Pet Owner**
```
User registers their first dog
Before: No roles
After: pet_owner role automatically assigned
```

**Example 2: Expanding to Livestock**
```
User (already has pet_owner) registers their first goat
Before: pet_owner
After: pet_owner + livestock_owner
```

**Example 3: Full Animal Owner**
```
User registers dog, cattle, and chicken over time
Final roles: pet_owner + livestock_owner + poultry_owner
```

## Testing

Use the test command to verify role assignment:

```bash
# Test livestock role assignment
php artisan test:role-assignment user@example.com cattle

# Test poultry role assignment  
php artisan test:role-assignment user@example.com chicken

# Test pet role assignment
php artisan test:role-assignment user@example.com dog
```

## Benefits

1. **Automatic Permissions**: Users get appropriate access without manual role management
2. **Better Organization**: Sidebar navigation shows relevant animal categories based on user roles
3. **Improved UX**: Users only see features relevant to their animal types
4. **Administrative Efficiency**: Reduces manual role assignment overhead
5. **Scalability**: Easily extensible for new animal types and roles

## Implementation Details

The role assignment logic is implemented in:
- `Api\AnimalController::assignRoleBasedOnAnimalType()`
- `Web\AnimalController::assignRoleBasedOnAnimalType()`

Both controllers use the same mapping logic to ensure consistency across API and web interfaces.
