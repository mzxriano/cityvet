import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/models/animal_archive_model.dart';
import 'package:cityvet_app/services/animal_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class AnimalViewModel extends ChangeNotifier{

  final AnimalService _animalService = AnimalService();

  List<AnimalModel> _animals = [];
  List<AnimalModel> _allAnimals = [];
  String? _errors;
  bool _isLoading = false;
  String? _message;
  bool _disposed = false;
  List<AnimalArchiveModel> _archivedAnimals = [];

  String? get errors => _errors;
  List<AnimalModel> get animals => _animals;
  List<AnimalModel> get allAnimals => _allAnimals;
  bool get isLoading => _isLoading;
  String? get message => _message;
  List<AnimalArchiveModel> get archivedAnimals => _archivedAnimals;

  @override
  void dispose() {
    _disposed = true;
    super.dispose();
  }

  setAnimals(List<AnimalModel> animals) {
    if (_disposed) return;
    _animals = animals;
    notifyListeners();
  }

  setAllAnimals(List<AnimalModel> animals) {
    if (_disposed) return;
    _allAnimals = animals;
    notifyListeners();
  }

  setErrors(String errors) {
    if (_disposed) return;
    _errors = errors;
    notifyListeners();
  }

  setLoading(bool isLoading){
    if (_disposed) return;
    _isLoading = isLoading;
    notifyListeners();
  }

  setMessage(String? message) {
    if (_disposed) return;
    _message = message;
    notifyListeners();
  }

Future<void> fetchAllAnimals() async {
  if (_disposed) return;
  try {
    setLoading(true);

    final response = await _animalService.fetchAllAnimals();
    if (_disposed) return;

    if (response.statusCode == 200 && response.data is Map<String, dynamic>) {
      final responseData = response.data as Map<String, dynamic>;
      final List<dynamic> jsonList = responseData['data'];

      final animalsList = jsonList
          .map((json) => AnimalModel.fromJson(json as Map<String, dynamic>))
          .toList();

      setAllAnimals(animalsList);
    } else {
      print('Unexpected response format: ${response.data}');
    }
  } on DioException catch (e) {
    if (_disposed) return;
    final data = e.response?.data;

    print(data);
    if (data is Map<String, dynamic> && data['errors'] != null) {
      print('Server-side errors: ${data['errors']}');
    } else {
      setMessage(DioExceptionHandler.handleException(e));
    }
  } catch (e) {
    if (_disposed) return;
    print('Unexpected error: $e');
  } finally {
    if (_disposed) return;
    setLoading(false);
  }
}


Future<void> fetchAnimals() async {
  if (_disposed) return;
  try {
    setLoading(true);
    setErrors(''); // Clear previous errors
    setMessage(null); // Clear previous messages

    final response = await _animalService.fetchAnimals();
    if (_disposed) return;

    if (response.statusCode == 200 && response.data is Map<String, dynamic>) {
      final responseData = response.data as Map<String, dynamic>;
      
      // Handle the case where 'data' might be null or not a list
      final dynamic dataField = responseData['data'];
      if (dataField is List) {
        final animalsList = dataField
            .map((json) => AnimalModel.fromJson(json as Map<String, dynamic>))
            .toList();

        setAnimals(animalsList);
        setMessage('Animals loaded successfully');
      } else {
        print('Data field is not a list: $dataField');
        setAnimals([]); // Set empty list if no data
        setMessage('No animals found');
      }
    } else {
      print('Unexpected response format: ${response.data}');
      setErrors('Failed to load animals: Unexpected response format');
    }
  } on DioException catch (e) {
    if (_disposed) return;
    final data = e.response?.data;

    print('DioException: $data');
    if (data is Map<String, dynamic> && data['errors'] != null) {
      print('Server-side errors: ${data['errors']}');
      setErrors('Server error: ${data['errors']}');
    } else {
      final errorMessage = DioExceptionHandler.handleException(e);
      setErrors(errorMessage);
    }
  } catch (e) {
    if (_disposed) return;
    print('Unexpected error: $e');
    setErrors('An unexpected error occurred: $e');
  } finally {
    if (_disposed) return;
    setLoading(false);
  }
}

/// Archive an animal (either deleted or deceased)
Future<void> archiveAnimal(AnimalModel animalModel, {
  required String archiveType, 
  required String archiveDate,
  String? reason,
  String? notes,
}) async {
  if (_disposed) return;
  try {
    setLoading(true);
    setErrors(''); // Clear previous errors
    setMessage(null); // Clear previous messages

    final token = await AuthStorage().getToken();
    if (token == null) {
      setErrors('Authentication token not found');
      return;
    }

    print('Archiving animal: ${animalModel.name} (ID: ${animalModel.id}) as $archiveType');
    
    final response = await _animalService.archiveAnimal(
      token,
      animalModel.id!,
      archiveType: archiveType,
      archiveDate: archiveDate,
      reason: reason,
      notes: notes,
    );
    
    if (_disposed) return;

    print('Archive response: ${response.statusCode} - ${response.data}');

    if (response.statusCode == 200) {
      // Remove from local list since it's now archived
      _animals.removeWhere((a) => a.id == animalModel.id);
      _allAnimals.removeWhere((a) => a.id == animalModel.id);
      
      final archiveTypeText = archiveType == 'deceased' ? 'marked as deceased' : 'deleted';
      setMessage('Animal ${animalModel.name} has been $archiveTypeText successfully');
      notifyListeners();
    } else {
      print('Unexpected response code: ${response.statusCode}');
      setErrors('Failed to archive animal: unexpected response');
    }

  } on DioException catch (e) {
    if (_disposed) return;
    final data = e.response?.data;
    
    print('DioException in archiveAnimal: ${e.response?.statusCode} - $data');
    
    if (data is Map<String, dynamic>) {
      if (data['message'] != null) {
        setErrors('Failed to archive animal: ${data['message']}');
      } else if (data['errors'] != null) {
        setErrors('Validation error: ${data['errors']}');
      } else {
        setErrors('Failed to archive animal: Unknown server error');
      }
    } else {
      setErrors(DioExceptionHandler.handleException(e));
    }
  } catch (e) {
    if (_disposed) return;
    print('Unexpected error in archiveAnimal: $e');
    setErrors('An unexpected error occurred: $e');
  } finally {
    if (_disposed) return;
    setLoading(false);
  }
}


  void updateAnimal(AnimalModel updatedAnimal) {
    if (_disposed) return;
    final index = animals.indexWhere((a) => a.id == updatedAnimal.id);
    if (index != -1) {
      animals[index] = updatedAnimal;
      notifyListeners();
    }
  }

  Future<List<Map<String, dynamic>>> searchOwners(String query) async {
    if (_disposed) return [];
    try {
      final response = await _animalService.searchOwners(query);
      if (_disposed) return [];

      if (response.statusCode == 200 && response.data is Map<String, dynamic>) {
        final responseData = response.data as Map<String, dynamic>;
        final List<dynamic> jsonList = responseData['data'] ?? [];
        
        return jsonList.cast<Map<String, dynamic>>();
      } else {
        throw Exception('Failed to search owners');
      }
    } on DioException catch (e) {
      if (_disposed) return [];
      throw Exception(DioExceptionHandler.handleException(e));
    } catch (e) {
      if (_disposed) return [];
      throw Exception('Unexpected error: $e');
    }
  }

  Future<void> addAnimalForOwner({
    required int ownerId,
    required String type,
    required String name,
    String? breed,
    String? birthDate,
    String? gender,
    String? color,
    double? weight,
    double? height,
    String? uniqueSpot,
    String? knownConditions,
    dynamic animalProfile,
  }) async {
    if (_disposed) return;
    try {
      final response = await _animalService.addAnimalForOwner(
        ownerId: ownerId,
        type: type,
        name: name,
        breed: breed,
        birthDate: birthDate,
        gender: gender,
        color: color,
        weight: weight,
        height: height,
        uniqueSpot: uniqueSpot,
        knownConditions: knownConditions,
        animalProfile: animalProfile,
      );
      if (_disposed) return;

      if (response.statusCode == 201) {
        setMessage(response.data['message'] ?? 'Animal added successfully');
      } else {
        throw Exception('Failed to add animal');
      }
    } on DioException catch (e) {
      if (_disposed) return;
      throw Exception(DioExceptionHandler.handleException(e));
    } catch (e) {
      if (_disposed) return;
      throw Exception('Unexpected error: $e');
    }
  }

  /// Fetch archived animals
  Future<void> fetchArchivedAnimals({String? archiveType}) async {
    if (_disposed) return;
    try {
      setLoading(true);

      final token = await AuthStorage().getToken();
      if (token == null) return;

      final response = await _animalService.getArchivedAnimals(token, archiveType: archiveType);
      if (_disposed) return;

      if (response.statusCode == 200 && response.data is Map<String, dynamic>) {
        final responseData = response.data as Map<String, dynamic>;
        final List<dynamic> jsonList = responseData['data'];

        final archivesList = jsonList
            .map((json) => AnimalArchiveModel.fromJson(json as Map<String, dynamic>))
            .toList();

        _archivedAnimals = archivesList;
        notifyListeners();
      } else {
        print('Unexpected response format: ${response.data}');
      }
    } on DioException catch (e) {
      if (_disposed) return;
      final data = e.response?.data;

      if (data is Map<String, dynamic> && data['errors'] != null) {
        print('Server-side errors: ${data['errors']}');
      } else {
        setMessage(DioExceptionHandler.handleException(e));
      }
    } catch (e) {
      if (_disposed) return;
      print('Unexpected error: $e');
    } finally {
      if (_disposed) return;
      setLoading(false);
    }
  }

  /// Restore an archived animal back to active animals
  Future<void> restoreArchivedAnimal(AnimalArchiveModel archive) async {
    if (_disposed) return;
    try {
      setLoading(true);
      setErrors(''); // Clear previous errors
      setMessage(null); // Clear previous messages

      final token = await AuthStorage().getToken();
      if (token == null) {
        setErrors('Authentication token not found');
        return;
      }

      print('Restoring animal: ${archive.animal.name} (Archive ID: ${archive.id})');
      
      final response = await _animalService.restoreArchivedAnimal(
        token,
        archive.id,
      );
      
      if (_disposed) return;

      print('Restore response: ${response.statusCode} - ${response.data}');

      if (response.statusCode == 200) {
        // Remove from archived list
        _archivedAnimals.removeWhere((a) => a.id == archive.id);
        
        // Add back to active animals list if the response contains the animal data
        if (response.data is Map<String, dynamic>) {
          final responseData = response.data as Map<String, dynamic>;
          if (responseData['data'] != null) {
            final restoredAnimal = AnimalModel.fromJson(responseData['data'] as Map<String, dynamic>);
            _animals.add(restoredAnimal);
            _allAnimals.add(restoredAnimal);
          }
        }
        
        setMessage('Animal ${archive.animal.name} has been restored successfully');
        notifyListeners();
      } else {
        print('Unexpected response code: ${response.statusCode}');
        setErrors('Failed to restore animal: unexpected response');
      }

    } on DioException catch (e) {
      if (_disposed) return;
      final data = e.response?.data;
      
      print('DioException in restoreArchivedAnimal: ${e.response?.statusCode} - $data');
      
      if (data is Map<String, dynamic>) {
        if (data['message'] != null) {
          setErrors('Failed to restore animal: ${data['message']}');
        } else if (data['errors'] != null) {
          setErrors('Validation error: ${data['errors']}');
        } else {
          setErrors('Failed to restore animal: Unknown server error');
        }
      } else {
        setErrors(DioExceptionHandler.handleException(e));
      }
    } catch (e) {
      if (_disposed) return;
      print('Unexpected error in restoreArchivedAnimal: $e');
      setErrors('An unexpected error occurred: $e');
    } finally {
      if (_disposed) return;
      setLoading(false);
    }
  }
}