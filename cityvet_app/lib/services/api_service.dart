import 'package:dio/dio.dart';

class ApiService {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api',
    headers: {
      'Accept': 'application/json',
    },
  ));

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
}
