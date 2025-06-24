import 'package:cityvet_app/models/auth_model.dart';
import 'package:dio/dio.dart';

class AuthService {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api',
    headers: {
      'Accept': 'application/json',
    },
  ));

  Future<String?> register(
    String firstName,
    String lastName,
    String phoneNumber,
    String email,
    String password,
    String passwordConfirmation,
  ) async {
    try {
      final user = AuthModel(
          firstName: firstName,
          lastName: lastName,
          phoneNumber: phoneNumber,
          email: email,
          password: password);

      final response = await _dio.post('/register', data: {
        ...user.toJson(),
        'password_confirmation': passwordConfirmation,  // note underscore, Laravel expects this
      });

      print('This is the message ${response.data['message']}');
      return response.data['message'];
    } on DioException catch (e) {
      if (e.response != null && e.response?.data != null) {
        // Laravel usually sends validation errors in 'errors' or 'message'
        final data = e.response!.data;

        // If validation errors are present
        if (data['errors'] != null) {
          // errors is usually a map of field -> [list of errors]
          final errors = data['errors'] as Map<String, dynamic>;
          // Extract the first error message from the errors map
          final firstError = errors.values.first[0];
          print('First error $firstError');
          return firstError;
        }

        // Fallback to a generic message from server if available
        if (data['message'] != null) {
          print('Error message ${data['message']}');
          return data['message'];
        }
      }

      // Network or other errors fallback message
      return 'An unexpected error occurred. Please try again.';
    } catch (e) {
      print('Error registering user: $e');
      return 'An unexpected error occurred. Please try again.';
    }
  }
}
