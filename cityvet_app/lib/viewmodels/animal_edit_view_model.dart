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
  bool _isLoading = false;

  AnimalModel? get animal => _animal;
  String? get message => _message;
  bool get isSucess => _isSuccess;
  File? get animalProfile => _animalProfile;
  bool get isLoading => _isLoading;

  void setAnimal(AnimalModel animal) {
    _animal = animal;
    notifyListeners();
  }

  void setMessage(String? message) {
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

  void setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  void clearState() {
    _message = null;
    _isSuccess = false;
    _animalProfile = null;
    notifyListeners();
  }

  Future<void> updateAnimal(AnimalModel animalModel) async {
    try {
      setLoading(true);
      setSuccess(false);
      setMessage(null);

      final token = await AuthStorage().getToken();
      if (token == null) {
        setMessage('Authentication token not found');
        setLoading(false);
        return;
      }

      final response = await AnimalService().updateAnimal(
        animalModel, 
        imageFile: _animalProfile
      );

      print('Update response: ${response.data}');

      if (response.statusCode == 200 && response.data['data'] != null) {
        // Create updated animal model from response
        final updatedAnimalData = response.data['data'];
        final updatedAnimal = AnimalModel.fromJson(updatedAnimalData);
        
        setAnimal(updatedAnimal);
        setMessage(response.data['message'] ?? 'Animal updated successfully');
        setSuccess(true);
        
        // Clear the local image file since it's now uploaded
        _animalProfile = null;
      } else {
        setMessage(response.data['message'] ?? 'Update failed');
        setSuccess(false);
      }
    } on DioException catch (e) {
      setLoading(false);
      setSuccess(false);
      
      if (e.response?.data != null) {
        final errorData = e.response!.data;
        if (errorData is Map<String, dynamic>) {
          if (errorData.containsKey('message')) {
            setMessage(errorData['message']);
          } else if (errorData.containsKey('errors')) {
            // Handle validation errors
            final errors = errorData['errors'] as Map<String, dynamic>;
            final firstError = errors.values.first;
            if (firstError is List && firstError.isNotEmpty) {
              setMessage(firstError.first.toString());
            } else {
              setMessage('Validation failed');
            }
          } else {
            setMessage('Update failed');
          }
        } else {
          setMessage('Update failed');
        }
      } else {
        DioExceptionHandler.handleException(e);
        setMessage('Network error occurred');
      }
    } catch (e) {
      setLoading(false);
      setSuccess(false);
      setMessage('An unexpected error occurred');
      print('Error updating animal: $e');
    } finally {
      setLoading(false);
    }
  }

  Future<void> pickImageFromGallery() async {
    try {
      final pickedImage = await CustomImagePicker().pickFromGallery();
      if (pickedImage == null) return;
      
      setAnimalProfile(File(pickedImage.path));
    } catch (e) {
      print('Error picking image: $e');
      setMessage('Failed to pick image');
    }
  }
}