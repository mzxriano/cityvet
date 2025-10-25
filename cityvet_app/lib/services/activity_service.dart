import 'package:cityvet_app/models/activity_model.dart';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:dio/dio.dart';

class ActivityService {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: ApiConstant.baseUrl,
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

  Future<ActivityModel?> fetchUpcomingActivity(String token) async {
    try {
      final response = await _dio.get(
        '/activity/upcoming',
        options: Options(
          headers: {
            'Authorization': 'Bearer $token',
            'Accept': 'application/json',
          },
        ),
      );

      if (response.statusCode == 200) {
        final dynamic data = response.data;
        print(data);
        return ActivityModel.fromJson(data);
      } else {
        throw Exception('Failed to fetch upcoming activity');
      }
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) {
        return null; 
      }
      rethrow;
    }
  }

  Future<ActivityModel?> fetchOngoingActivity(String token) async {
    try {
      final response = await _dio.get(
        '/activity/ongoing',
        options: Options(
          headers: {
            'Authorization': 'Bearer $token',
            'Accept': 'application/json',
          },
        ),
      );

      if (response.statusCode == 200) {
        final dynamic data = response.data;
        print(data);
        return ActivityModel.fromJson(data);
      } else {
        throw Exception('Failed to fetch ongoing activity');
      }
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) {
        return null; // Return null when no activity found
      }
      rethrow;
    }
  }

  // Legacy methods for backward compatibility
  Future<List<ActivityModel>> fetchUpcomingActivities(String token) async {
    final activity = await fetchUpcomingActivity(token);
    return activity != null ? [activity] : [];
  }

  Future<List<ActivityModel>> fetchOngoingActivities(String token) async {
    final activity = await fetchOngoingActivity(token);
    return activity != null ? [activity] : [];
  }

Future<List<ActivityModel>> fetchRecentActivities(String token) async {
  try {
    final response = await _dio.get(
      '/activity/recent',
      options: Options(
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      ),
    );

    if (response.statusCode == 200) {
      final dynamic responseData = response.data;
      print('Recent activities response: $responseData');
      
      List<dynamic> data;
      
      if (responseData is List) {
        data = responseData;
      } else if (responseData is Map<String, dynamic>) {
        if (responseData.containsKey('data')) {
          data = responseData['data'] as List<dynamic>;
        } else if (responseData.containsKey('activities')) {
          data = responseData['activities'] as List<dynamic>;
        } else if (responseData.containsKey('results')) {
          data = responseData['results'] as List<dynamic>;
        } else {
          data = [responseData];
        }
      } else {
        throw Exception('Unexpected response format: ${responseData.runtimeType}');
      }
      
      print('Parsed activities data: $data');
      return data.map((json) => ActivityModel.fromJson(json as Map<String, dynamic>)).toList();
    } else {
      throw Exception('Failed to fetch recent activities');
    }
  } on DioException catch (e) {
    if (e.response?.statusCode == 404) {
      return []; 
    }
    rethrow;
  } catch (e) {
    print('Error in fetchRecentActivities: $e');
    rethrow;
  }
}
}