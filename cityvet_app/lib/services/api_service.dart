import 'package:cityvet_app/models/notification_model.dart';
import 'package:dio/dio.dart';

class ApiService {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api',
    headers: {
      'Accept': 'application/json',
    },
  ));

  // Optionally, add a callback for new notifications
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
      final response = await _dio.get('/auth/vaccines', options: Options(headers: {'Authorization': 'Bearer $token'}));
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
        '/auth/animals/$animalId/vaccines',
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

  Future<List<dynamic>> getAnimalVaccinations(String token, int animalId) async {
    try {
      final response = await _dio.get('/auth/animals/$animalId', options: Options(headers: {'Authorization': 'Bearer $token'}));
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
      final response = await _dio.get('/auth/notifications', options: Options(headers: {'Authorization': 'Bearer $token'}));
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

  Future<void> markNotificationAsRead(String token, String? notificationId) async {
    try {
      final response = await _dio.post(
        '/auth/notifications/$notificationId/read',
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
}
