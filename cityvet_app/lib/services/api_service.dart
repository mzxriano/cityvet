import 'dart:convert';

import 'package:cityvet_app/models/notification_model.dart';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:dio/dio.dart';
import 'dart:io';

class ApiService {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: ApiConstant.baseUrl,
    headers: {
      'Accept': 'application/json',
    },
  ));

  void Function(NotificationModel)? onNewNotification;

  Future<List<dynamic>> getUsers() async {
    try {
      final response = await _dio.get('/users');

      if (response.statusCode == 200) {
        return response.data;
      } else {
         throw Exception('Failed to load users: ${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }

  // Old fetching vaccines method
  Future<List<dynamic>> getVaccines(String token) async {
    try {
      final response = await _dio.get('/vaccines', options: Options(headers: {'Authorization': 'Bearer $token'}));
      if (response.statusCode == 200) {
        return response.data;
      } else {
        throw Exception('Failed to load vaccines: \\${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }

  // New fetching vaccines method
  Future<List<dynamic>> getNewVaccines(String token) async {
    try {
      final response = await _dio.get('/vaccines', options: Options(headers: {'Authorization': 'Bearer $token'}));
      if (response.statusCode == 200) {
        return response.data;
      } else {
        throw Exception('Failed to load vaccines: \\${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }

  Future<void> attachVaccinesToAnimal(String token, int animalId, List<Map<String, dynamic>> vaccines) async {
    try {
      final response = await _dio.post(
        '/animals/$animalId/vaccines',
        data: {
          'vaccines': vaccines,
        },
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      if (response.statusCode != 200) {
        throw Exception('Failed to attach vaccines: \\${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }

  Future<void> attachVaccinesToActivity(String token, int activityId, int animalId, List<Map<String, dynamic>> vaccines) async {
    try {
      final response = await _dio.post(
        '/animals/activity/$activityId/vaccinate',
        data: {
          'animal_id': animalId,
          'vaccines': vaccines,
        },
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      if (response.statusCode != 200) {
        throw Exception('Failed to attach vaccines to activity: \\${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }

  // Log vaccinated animal on a specific activity
  Future<void> logAdministration(String token, Map<String, dynamic> payload) async {
    
    try {
        final response = await _dio.post(
            '/vaccinations/log', // Match the Laravel route prefix
            data: payload,
            options: Options(headers: {
                'Authorization': 'Bearer $token',
                'Content-Type': 'application/json',
            }),
        );

        // Expecting 201 Created from the Laravel controller
        if (response.statusCode != 201) {
            // If the backend sends an error message in the response body
            final responseData = response.data is String ? json.decode(response.data) : response.data;
            final errorMessage = responseData['message'] ?? 'Failed to log vaccination.';
            throw Exception(errorMessage);
        }
    } on DioException catch (e) {
        // Handle specific Dio errors, e.g., Insufficient Stock (400 or 422)
        if (e.response?.data != null) {
              final errorMessage = e.response!.data['message'] ?? e.response!.statusMessage;
              throw Exception(errorMessage);
        }
        throw Exception('API Request failed: ${e.message}');
    } catch (e) {
        throw Exception('Error: $e');
    }
  }

  Future<List<dynamic>> getAnimalVaccinations(String token, int animalId) async {
    try {
      final response = await _dio.get('/animals/$animalId', options: Options(headers: {'Authorization': 'Bearer $token'}));
      if (response.statusCode == 200) {
        return response.data['vaccinations'] ?? [];
      } else {
        throw Exception('Failed to load animal vaccinations: \\${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }

  Future<List<dynamic>> getNotifications(String token) async {
    try {
      final response = await _dio.get('/notifications', options: Options(headers: {'Authorization': 'Bearer $token'}));
      if (response.statusCode == 200) {
        final notifications = response.data;
        // If a callback is set, notify about new notifications
        if (onNewNotification != null && notifications.isNotEmpty) {
          final unread = notifications.where((n) => n['read'] == false || n['read'] == 0).toList();
          if (unread.isNotEmpty) {
            onNewNotification!(NotificationModel.fromJson(unread.first));
          }
        }
        return notifications;
      } else {
        throw Exception('Failed to load notifications: \\${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }

  Future<Map<String, dynamic>> getVaccinatedAnimals(String token, String date, {int? activityId}) async {
    try {
      final queryParams = {'date': date};
      if (activityId != null) {
        queryParams['activity_id'] = activityId.toString();
      }
      
      final response = await _dio.get(
        '/activity/vaccinated-animals',
        queryParameters: queryParams,
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      
      if (response.statusCode == 200) {
        return response.data;
      } else {
        throw Exception('Failed to load vaccinated animals: \\${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }

  Future<List<Map<String, dynamic>>> fetchAllVaccinatedAnimals() async {
    try {
      final response = await _dio.get(
        '/animals/vaccinated-animals/all',
      );

      if (response.statusCode == 200) {
        // The Laravel endpoint is expected to return a JSON array of complex objects
        List<dynamic> data = json.decode(response.data)['vaccinated_animals']; 
        
        // Cast the dynamic list to List<Map<String, dynamic>>
        return data.cast<Map<String, dynamic>>().toList();
      } else {
        final responseBody = json.decode(response.data);
        final errorMessage = responseBody['message'] ?? 'Failed to load vaccinated animals.';
        throw Exception('API Error (${response.statusCode}): $errorMessage');
      }
    } catch (e) {
      print('Error fetching all vaccinated animals: $e');
      throw Exception('Network error or failed to fetch vaccinated animals.');
    }
  }

  Future<Map<String, dynamic>> getVaccinatedAnimalsByActivity(String token, int activityId) async {
    try {
      final response = await _dio.get(
        '/activity/$activityId/vaccinated-animals',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      
      if (response.statusCode == 200) {
        return response.data;
      } else {
        throw Exception('Failed to load vaccinated animals for activity: \\${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }

  // New method for fetching vaccinated animals by activity
  Future<Map<String, dynamic>> getVaccinatedAnimalsByActivityNew(String token, int activityId) async {
    try {
      final response = await _dio.get(
        '/activity/vaccinated-animals/$activityId',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      
      if (response.statusCode == 200) {
        return response.data;
      } else {
        throw Exception('Failed to load vaccinated animals for activity: \\${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }

  Future<void> markNotificationAsRead(String token, String? notificationId) async {
    try {
      final response = await _dio.post(
        '/notifications/$notificationId/read',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      if (response.statusCode != 200) {
        throw Exception('Failed to mark notification as read: \\${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }

  Future<List<dynamic>> fetchVeterinarians(String token) async {
    final response = await _dio.get(
      '/veterinarians',
      options: Options(headers: {'Authorization' : 'Bearer $token'}),
    );

    return response.data;
  }

  Future<void> uploadActivityImages(String token, int activityId, List<File> images) async {
    try {
      FormData formData = FormData();
      
      // Add images to form data - use 'images' as key for array validation
      for (int i = 0; i < images.length; i++) {
        String fileName = images[i].path.split('/').last;
        formData.files.add(MapEntry(
          'images[$i]', // Use indexed format for Laravel array validation
          await MultipartFile.fromFile(images[i].path, filename: fileName),
        ));
      }

      final response = await _dio.post(
        '/activity/$activityId/upload-images',
        data: formData,
        options: Options(
          headers: {
            'Authorization': 'Bearer $token',
            // Remove Content-Type header, let Dio set it automatically for multipart
          },
        ),
      );

      if (response.statusCode != 200) {
        throw Exception('Failed to upload images: ${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error uploading images: $e');
    }
  }
}


