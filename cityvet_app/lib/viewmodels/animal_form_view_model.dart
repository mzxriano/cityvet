import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/services/animal_service.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class AnimalFormViewModel extends ChangeNotifier {

  final AnimalService _animalService = AnimalService();

  AnimalModel? _animalModel;
  String? _message;
  String? _errors;
  List<AnimalModel> _animals = [];
  bool _isLoading = false;
  
  AnimalModel? get animalModel => _animalModel;
  String? get message => _message;
  String? get errors => _errors;
  List<AnimalModel> get animals => _animals;
  bool get isLoading => _isLoading;

  setAnimalModel(AnimalModel animalModel) {
    _animalModel = animalModel;
    notifyListeners();
  }

  setMessage(String message) {
    _message = message;
    notifyListeners();
  }

  setErrors(String errors) {
    _errors = errors;
    notifyListeners();
  }

  setAnimals(List<AnimalModel> animals) {
    _animals = animals;
    notifyListeners();
  }

  setLoading(bool isLoading) {
    _isLoading = isLoading;
  }

  Future<void> createAnimal(AnimalModel animalModel) async {
    try {
      setLoading(true);

      final result = await _animalService.createAnimal(animalModel);

      if(result.statusCode == 200) {
        print(result.data['message']);
        setMessage(result.data['message']);
      }
      
    } on DioException catch(e) {
      final data = e.response?.data;

      print(data);

      if(data != null && data['errors'] !=null) {
        setErrors(data['errors'].toString());
      }
      else {
        setMessage(DioExceptionHandler.handleException(e));
      }
    } 
    catch (e) {

      print('May error: $e');

    }finally {
      setLoading(false);
    }
  }

Future<void> fetchAnimals() async {
  try {
    final response = await _animalService.fetchAnimals();

    if (response.statusCode == 200 && response.data is List) {
      final List<dynamic> jsonList = response.data;

      // Map each JSON item to an AnimalModel
      final animalsList = jsonList.map((json) => AnimalModel.fromJson(json)).toList();

      setAnimals(animalsList);
    } else {
      print('Unexpected response: ${response.statusCode}');
    }
  } on DioException catch (e) {
    final data = e.response?.data;

    if (data is Map<String, dynamic> && data['errors'] != null) {
      print('Server-side errors: ${data['errors']}');
    } else {
      setErrors(DioExceptionHandler.handleException(e));
    }
  } catch (e) {
    print('Unexpected error: $e');
  }
}



}