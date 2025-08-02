import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/services/animal_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class AnimalViewModel extends ChangeNotifier{

  final AnimalService _animalService = AnimalService();

  List<AnimalModel> _animals = [];
  String? _errors;
  bool _isLoading = false;
  String? _message;
  bool _disposed = false;

  String? get errors => _errors;
  List<AnimalModel> get animals => _animals;
  bool get isLoading => _isLoading;
  String? get message => _message;

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


Future<void> fetchAnimals() async {
  if (_disposed) return;
  try {
    setLoading(true);

    final response = await _animalService.fetchAnimals();
    if (_disposed) return;

    if (response.statusCode == 200 && response.data is Map<String, dynamic>) {
      final responseData = response.data as Map<String, dynamic>;
      final List<dynamic> jsonList = responseData['data'];

      final animalsList = jsonList
          .map((json) => AnimalModel.fromJson(json as Map<String, dynamic>))
          .toList();

      setAnimals(animalsList);
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

Future<void> deleteAnimal(AnimalModel animalModel) async {
  if (_disposed) return;
  try {

    final token = await AuthStorage().getToken();

    if(token == null) return;
    
    final response = await AnimalService().deleteAnimal(token, animalModel);
    if (_disposed) return;

    if(response.statusCode == 200) {
      setMessage(response.data['message']);
    }

  } on DioException catch (e) {
    if (_disposed) return;
    final exception = e.response?.data;

    if(exception is DioException) {
      DioExceptionHandler.handleException(exception);
    }
  } catch (e) {
    if (_disposed) return;
    print('Error deleting animal $e');
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

}