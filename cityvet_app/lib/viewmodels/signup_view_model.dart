import 'package:cityvet_app/models/auth_model.dart';
import 'package:cityvet_app/services/auth_service.dart';
import 'package:flutter/material.dart';

class SignupViewModel {
  final _authService = AuthService();

  // Notifier for form field errors
  final ValueNotifier<Map<String, String?>> fieldErrors = ValueNotifier({
    'firstName': null,
    'lastName': null,
    'birthDate': null,
    'phoneNumber': null,
    'email': null,
    'password': null,
    'passwordConfirmation': null,
  });

  // Notifier for password visibility
  final ValueNotifier<bool> isPasswordObscured = ValueNotifier(true);
  final ValueNotifier<bool> isConfirmPasswordObscured = ValueNotifier(true);

  // Notifier for selected barangay
  final ValueNotifier<String?> selectedBarangay = ValueNotifier<String?>(null);

  Future<String?> register({
    required String firstName,
    required String lastName,
    required String birthDate,
    required String phoneNumber,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    final errors = <String, String?>{};

    if (errors.isNotEmpty) return null;

    final AuthModel authModel = 
      AuthModel(firstName: firstName, lastName: lastName, birthDate: birthDate, phoneNumber: phoneNumber, email: email, password: password);

    final data = await _authService.register(authModel, passwordConfirmation);

    if(data == null) return null;

    if(data.containsKey('message')) {
      print('Data message ${data['message']}');
      return data['message'];
    }

    if (data.containsKey('errors')) {
      final fieldErrors = <String, String>{};
      data['errors'].forEach((key, value) {
        fieldErrors[key] = (value as List).isNotEmpty ? value[0] : null;
      });

      this.fieldErrors.value = fieldErrors;
    }

    return 'Something went wrong!';

  }

  void dispose() {
    fieldErrors.dispose();
    isPasswordObscured.dispose();
    isConfirmPasswordObscured.dispose();
  }
}
