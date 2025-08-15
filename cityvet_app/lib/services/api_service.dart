import 'package:cityvet_app/models/notification_model.dart';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:dio/dio.dart';

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
}


