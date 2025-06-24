import 'package:cityvet_app/services/auth_service.dart';
import 'package:flutter/material.dart';

class SignupViewModel extends ChangeNotifier {

  final AuthService _authService = AuthService();

  bool _isLoading = false;
  String? _errorMsg;

  bool get isLoading => _isLoading;
  String? get errorMsg => _errorMsg;

  Future<String?> register({
    required String firstName,
    required String lastName,
    required String phoneNumber,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {

    _isLoading = true;
    _errorMsg = null;
    notifyListeners();
    
    try {

      final message = await _authService.register(firstName, lastName, phoneNumber, email, password, passwordConfirmation);

      print('Message $message');

      if(message != null) {
        _errorMsg = message;
        notifyListeners();
        return message;
      }

    } catch (e) {
      _errorMsg = 'Something went wrong. Please try again.';
      notifyListeners();
    }finally {
      _isLoading = false;
      notifyListeners();
    }

  }

}