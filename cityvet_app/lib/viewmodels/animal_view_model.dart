import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/services/animal_service.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class AnimalViewModel extends ChangeNotifier{

  final AnimalService _animalService = AnimalService();

  List<AnimalModel> _animals = [];
  String? _errors;
  String get errors => _errors!;

  List<AnimalModel> get animals => _animals;

  setAnimals(List<AnimalModel> animals) {
    _animals = animals;
    notifyListeners();
  }

  setErrors(String errors) {
    _errors = errors;
    notifyListeners();
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