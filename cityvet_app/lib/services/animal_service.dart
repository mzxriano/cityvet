import 'dart:io';
import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:dio/dio.dart';

class AnimalService {
  final AuthStorage storage = AuthStorage();
  
  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api/auth', 
    headers: {
      'Accept': 'application/json',
    },
  ));

  Future<Response> createAnimal(AnimalModel animalModel, {File? imageFile}) async {
    final token = await storage.getToken();
    
    // If there's an image, use FormData. If not, use JSON like before
    dynamic requestData;
    Map<String, String> headers = {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    };
    
    if (imageFile != null) {
      // Use FormData for image upload
      FormData formData = FormData.fromMap({
        'type': animalModel.type,
        'name': animalModel.name,
        'breed': animalModel.breed,
        'gender': animalModel.gender,
        'color': animalModel.color,
      });
      
      // Add optional fields
      if (animalModel.birthDate != null) formData.fields.add(MapEntry('birth_date', animalModel.birthDate!));
      if (animalModel.weight != null) formData.fields.add(MapEntry('weight', animalModel.weight.toString()));
      if (animalModel.height != null) formData.fields.add(MapEntry('height', animalModel.height.toString()));
      
      // Add image
      formData.files.add(MapEntry('image', await MultipartFile.fromFile(imageFile.path)));
            
      requestData = formData;
    } else {
      // Use JSON like before (no image)
      requestData = animalModel.toJson();
    }
    
    final response = await _dio.post(
      '/animals',
      data: requestData,
      options: Options(headers: headers)
    );
    
    return response;
  }

  Future<Response> fetchAnimals() async {
    final token = await storage.getToken();
    
    final response = await _dio.get(
      '/animals',
      options: Options(
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        }
      )
    );
    print(response);
    return response;
  }
    
  Future<Response> updateAnimal(AnimalModel animalModel, {File? imageFile}) async {
    final token = await storage.getToken();
    
    dynamic requestData;
    Map<String, String> headers = {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    };
    
    if (imageFile != null) {
      // Use FormData for image upload
      FormData formData = FormData.fromMap({
        'type': animalModel.type,
        'name': animalModel.name,
        'breed': animalModel.breed,
        'gender': animalModel.gender,
        'color': animalModel.color,
      });
      
      // Add optional fields
      if (animalModel.birthDate != null) formData.fields.add(MapEntry('birth_date', animalModel.birthDate!));
      if (animalModel.weight != null) formData.fields.add(MapEntry('weight', animalModel.weight.toString()));
      if (animalModel.height != null) formData.fields.add(MapEntry('height', animalModel.height.toString()));
      
      // Add image
      formData.files.add(MapEntry('image', await MultipartFile.fromFile(imageFile.path)));
            
      requestData = formData;
    } else {
      // Use regular form data (no image)
      requestData = animalModel.toJson();
      
    }
    
    // Use POST for both scenarios
    final response = await _dio.post(
      '/animals/${animalModel.id}',
      data: requestData,
      options: Options(headers: headers)
    );
    
    return response;
  }

  Future<Response> deleteAnimal(String token, AnimalModel animalModel) async {
    final response = await _dio.delete(
      '/animals/${animalModel.id}',
      options: Options(
        headers: {
          'Authorization': 'Bearer $token',
        }
      ),
      data: animalModel.toJson()
    );
        
    return response;
  }
}