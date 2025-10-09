import 'dart:io';
import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:dio/dio.dart';

class AnimalService {
  final AuthStorage storage = AuthStorage();
  
  final Dio _dio = Dio(BaseOptions(
    baseUrl: ApiConstant.baseUrl, 
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

    Future<Response> fetchAllAnimals() async {
    final token = await storage.getToken();
    
    final response = await _dio.get(
      '/animals/all',
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

  Future<Response> searchOwners(String query) async {
    final token = await storage.getToken();
    
    final response = await _dio.get(
      '/search-owners',
      queryParameters: {'query': query},
      options: Options(
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        }
      )
    );
    
    return response;
  }

  Future<Response> addAnimalForOwner({
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
    final token = await storage.getToken();
    
    FormData formData = FormData.fromMap({
      'user_id': ownerId,
      'type': type,
      'name': name,
      'breed': breed ?? '',
      'birth_date': birthDate,
      'gender': gender ?? 'male',
      'color': color ?? 'Not specified',
      if (weight != null) 'weight': weight,
      if (height != null) 'height': height,
      'unique_spot': uniqueSpot ?? '',
      'known_conditions': knownConditions ?? '',
    });

    // Add profile image if provided
    if (animalProfile != null) {
      String fileName = animalProfile.path.split('/').last;
      formData.files.add(MapEntry(
        'profile_image',
        await MultipartFile.fromFile(animalProfile.path, filename: fileName),
      ));
    }

    final response = await _dio.post(
      '/animals/add-for-owner',
      data: formData,
      options: Options(
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
        }
      )
    );
    
    return response;
  }

  /// Archive an animal (deceased or deleted)
  Future<Response> archiveAnimal(
    String token,
    int animalId, {
    required String archiveType,
    required String archiveDate,
    String? reason,
    String? notes,
  }) async {
    final response = await _dio.post(
      '/animals/$animalId/archive',
      data: {
        'archive_type': archiveType,
        'archive_date': archiveDate,
        'reason': reason,
        'notes': notes,
      },
      options: Options(
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        }
      )
    );
    
    return response;
  }

  /// Get archived animals
  Future<Response> getArchivedAnimals(String token, {String? archiveType}) async {
    Map<String, dynamic> queryParams = {};
    if (archiveType != null) {
      queryParams['type'] = archiveType;
    }

    final response = await _dio.get(
      '/animals/archived',
      queryParameters: queryParams,
      options: Options(
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        }
      )
    );
    
    return response;
  }

  /// Restore an archived animal
  Future<Response> restoreArchivedAnimal(String token, int archiveId) async {
    final response = await _dio.post(
      '/animals/archived/$archiveId/restore',
      options: Options(
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        }
      )
    );
    
    return response;
  }

}