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

        // If validation errors are present
        if (data['errors'] != null) {
          return {'errors': Map<String, dynamic>.from(data['errors'])};
        }

        // Fallback to a generic message from server if available
        if (data['message'] != null) {
          return {'message': data['message']};
        }
      }

      // Network or other errors fallback message
      return {'message': 'Unexpected error occurred.'};
    } catch (e) {
      print('Error registering user: $e');
    }
  }
}
