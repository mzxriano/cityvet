import 'dart:io';

import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/services/animal_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:cityvet_app/utils/image_picker.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class AnimalEditViewModel extends ChangeNotifier {

  String? _message;
  AnimalModel? _animal;
  bool _isSuccess = false;
  File? _animalProfile;

  AnimalModel? get animal => _animal;
  String? get message => _message;
  bool get isSucess => _isSuccess;
  File? get animalProfile => _animalProfile;

  void setAnimal(AnimalModel animal) {
    _animal = animal;
    notifyListeners();
  }

  void setMessage(String message) {
    _message = message;
    notifyListeners();
  }

  void setSuccess(bool isSuccess) {
    _isSuccess = isSuccess;
    notifyListeners();
  }

  void setAnimalProfile(File image) {
    _animalProfile = image;
    notifyListeners();
  }

    Future<void> editAnimal(AnimalModel animalModel) async {
    try {
      final token = await AuthStorage().getToken();

      if(token == null) return;

      print('anmimal model ${animalModel.name}');

      final response = await AnimalService().editAnimal(token, animalModel);

      if(response.data['animal'] != null && response.data['message'] != null) {
        setAnimal(AnimalModel.fromJson(response.data['animal']));
        setMessage(response.data['message']);
        setSuccess(true);
      } 

    } on DioException catch (e) {
      final error = e.response?.data;

      if(error is DioException) {
        DioExceptionHandler.handleException(e);
      } else {
        print('Error on editting animal $error');
      }
    } catch (e) {
      print(e);
    }
  }

  Future<void> pickImageFromGallery() async {
    final pickedImage = await CustomImagePicker().pickFromGallery();
    if(pickedImage == null) return;
    setAnimalProfile(File(pickedImage.path));
  }

}