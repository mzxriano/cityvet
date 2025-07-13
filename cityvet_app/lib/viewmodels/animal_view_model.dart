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

  String? get errors => _errors;
  List<AnimalModel> get animals => _animals;
  bool get isLoading => _isLoading;
  String? get message => _message;

  setAnimals(List<AnimalModel> animals) {
    _animals = animals;
    notifyListeners();
  }

  setErrors(String errors) {
    _errors = errors;
    notifyListeners();
  }

  setLoading(bool isLoading){
    _isLoading = isLoading;
    notifyListeners();
  }

  setMessage(String? message) {
    _message = message;
    notifyListeners();
  }


Future<void> fetchAnimals() async {
  try {
    setLoading(true);

    final response = await _animalService.fetchAnimals();

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
    final data = e.response?.data;

    print(data);
    if (data is Map<String, dynamic> && data['errors'] != null) {
      print('Server-side errors: ${data['errors']}');
    } else {
      setMessage(DioExceptionHandler.handleException(e));
    }
  } catch (e) {
    print('Unexpected error: $e');
  } finally {
    setLoading(false);
  }
}

Future<void> deleteAnimal(AnimalModel animalModel) async {
  try {

    final token = await AuthStorage().getToken();

    if(token == null) return;
    
    final response = await AnimalService().deleteAnimal(token, animalModel);

    if(response.statusCode == 200) {
      setMessage(response.data['message']);
    }

  } on DioException catch (e) {
    final exception = e.response?.data;

    if(exception is DioException) {
      DioExceptionHandler.handleException(exception);
    }
  } catch (e) {
    print('Error deleting animal $e');
  }
}


  void updateAnimal(AnimalModel updatedAnimal) {
    final index = animals.indexWhere((a) => a.id == updatedAnimal.id);
    if (index != -1) {
      animals[index] = updatedAnimal;
      notifyListeners();
    }
  }

}