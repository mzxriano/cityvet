import 'package:cityvet_app/models/auth_model.dart';
import 'package:dio/dio.dart';

class AuthService {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api',
    headers: {
      'Accept': 'application/json',
    },
  ));

  Future<Map<String, dynamic>?> register(AuthModel authModel, String passwordConfirmation) async {
    try {
      final user = authModel;

      final response = await _dio.post('/register', data: {
        ...user.toJson(),
        'password_confirmation': passwordConfirmation, 
      });

      print('This is the message ${response.data['message']}');
      return {'message' : response.data['message']};
    } on DioException catch (e) {
      if (e.response != null && e.response?.data != null) {
     
        final data = e.response!.data;

        if (data['errors'] != null) {
          return {'errors': Map<String, dynamic>.from(data['errors'])};
        }

        if (data['message'] != null) {
          return {'message': data['message']};
        }
      }

      return {'message': 'Unexpected error occurred.'};
    } catch (e) {
      return null;
    }
  }

  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      var response = await _dio.post('/login', data: {
        'email': email,
        'password': password,
      });


      if(response.statusCode == 200 && response.data['user'] != null) {
        return {'user' : response.data['user']};
      }

      throw Exception('Login error!');
  
    } on DioException catch (e)  {
      
      if(e.response?.statusCode == 401 && e.response?.data['message'] != null) {
        return {'error' : e.response?.data['message']};
      }

      return {'error' : e.response?.data['errors']};
    } 

  }
}
