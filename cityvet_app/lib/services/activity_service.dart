import 'package:dio/dio.dart';

class ActivityService {

  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api/auth',
    headers: {
      'Accept': 'application/json',
    }
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

}