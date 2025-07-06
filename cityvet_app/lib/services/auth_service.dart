import 'package:cityvet_app/models/auth_model.dart';
import 'package:dio/dio.dart';

class AuthService {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api/auth',
    headers: {
      'Accept': 'application/json',
    },
  ));

  Future<Map<String, dynamic>?> register(AuthModel authModel, String passwordConfirmation) async {
    final user = authModel;

    final response = await _dio.post('/register', data: {
      ...user.toJson(),
      'password_confirmation': passwordConfirmation, 
    });

    return response.data;

  }

  Future<Response> login(String email, String password) async {

    var response = await _dio.post('/login', data: {
      'email': email,
      'password': password,
    });

    return response;

  }

  Future<Response> getBarangays() async {
    var response = await _dio.get('/barangay');

    return response;
  }

}
