import 'package:cityvet_app/models/user_model.dart';
import 'package:dio/dio.dart';

class UserService {

Future<Map<String, dynamic>> fetchUser(String token) async {

  final Dio dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api/auth',
    headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    },
  ));

  final user = await dio.get('/user');

  return user.data;

}

Future<Response> editProfile(String token, UserModel user) async {
  final Dio dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api/auth',
    headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    },
  ));

  final userData = await dio.post('/user/edit', data: user.toJson());

  return userData;
}

}