import 'package:cityvet_app/models/activity_model.dart';
import 'package:dio/dio.dart';

class ActivityService {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api/auth',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
    connectTimeout: const Duration(seconds: 30),
    receiveTimeout: const Duration(seconds: 30),
  ));

  Future<Map<String, dynamic>> fetchActivity(String token) async {
    final response = await _dio.get(
      '/activity',
      options: Options(
        headers: {
          'Authorization': 'Bearer $token'
        }
      )
    );

    print(response);
    return response.data;
  }

  Future<List<ActivityModel>> fetchRecentActivities(String token) async {
    final response = await _dio.get(
      '/recent-activities',
      options: Options(
        headers: {
          'Authorization': 'Bearer $token'
        }
      )
    );

    final List<dynamic> data = response.data;
    print(data);
    return data.map((json) => ActivityModel.fromJson(json as Map<String, dynamic>)).toList();
  }
}