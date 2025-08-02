import 'package:cityvet_app/models/auth_model.dart';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:dio/dio.dart';

class AuthService {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: ApiConstant.baseUrl,
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

    static Future<Response> resendVerification(String email) async {


    final Dio dio = Dio(BaseOptions(
      baseUrl: ApiConstant.baseUrl,
      headers: {
        'Accept': 'application/json',
      },
    ));

      final response = await dio.post('/resend-verification', data: {
        'email': email,
      });

      return response;

  }

  Future<Response> forgotPassword(String email) async {
    final response = await _dio.post('/forgot-password', data: {
      'email': email,
    });
    return response;
  }

  Future<Response> resetPassword({
    required String email,
    required String otp,
    required String password,
    required String passwordConfirmation,
  }) async {
    final response = await _dio.post('/reset-password', data: {
      'email': email,
      'otp': otp,
      'password': password,
      'password_confirmation': passwordConfirmation,
    });
    return response;
  }

  Future<Response> verifyOtp({
    required String email,
    required String otp,
  }) async {
    final response = await _dio.post('/verify-otp', data: {
      'email': email,
      'otp': otp,
    });
    return response;
  }

}
