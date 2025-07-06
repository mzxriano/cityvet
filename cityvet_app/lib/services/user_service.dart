import 'package:dio/dio.dart';

class UserService {

Future<Map<String, dynamic>> fetchUser(String token) async {

  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api/auth',
    headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    },
  ));

  final user = await _dio.get('/user');

  return user.data;

}

}