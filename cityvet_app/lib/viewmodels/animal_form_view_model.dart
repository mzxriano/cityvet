import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/models/auth_model.dart';
import 'package:cityvet_app/services/animal_service.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:dio/dio.dart';

class AnimalFormViewModel {

  final AnimalService _animalService = AnimalService();

  AnimalModel? _animalModel;
  String? _message;
  String? _errors;
  
  AnimalModel get animalModel => _animalModel!;
  String get message => _message!;
  String get errors => _errors!;

  setAnimalModel(AnimalModel animalModel) {
    _animalModel = animalModel;
  }

  setMessage(String message) {
    _message = message;
  }

  setErrors(String errors) {
    _errors = errors;
  }

  Future<void> createAnimal(AnimalModel animalModel) async {
    try {

      final result = await _animalService.createAnimal(animalModel);

      if(result.statusCode == 200) {
        setMessage(result.data['message']);
      }
      
    } on DioException catch(e) {
      final data = e.response?.data;

      if(data != null && data['errors'] !=null) {
        setErrors(data['errors'].toString());
      }
      else {
        setErrors(DioExceptionHandler.handleException(e));
      }
    } 
    catch (e) {

      print('May error: $e');

    }
  }

}