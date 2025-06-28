import 'package:cityvet_app/models/auth_model.dart';
import 'package:cityvet_app/services/auth_service.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class SignupViewModel extends ChangeNotifier {
  final _authService = AuthService();

  Map<String, dynamic> _fieldErrors = {};
  bool _isPasswordObscured = true;
  bool _isConfirmPasswordObscured = true;
  bool _success = false;
  String? _successMessage;
  String? _selectedBarangay;
  String? _error;

  get fieldErrors => _fieldErrors;
  bool get isPasswordObscured => _isPasswordObscured;
  bool get isConfirmPasswordObscured => _isConfirmPasswordObscured;
  bool get success => _success;
  String? get successMessage => _successMessage;
  String? get selectedBarangay => _selectedBarangay;
  String? get error => _error;
 
  setFieldErrors(Map<String, dynamic> fieldErrors) {
    _fieldErrors = fieldErrors;
    notifyListeners();
  }

  setSuccess(bool success) {
    _success = success;
    notifyListeners();
  }

  setPasswordObscured(bool isPasswordObscured) {
    _isPasswordObscured = isPasswordObscured;
    notifyListeners();
  }

  setConfirmPasswordObscured(bool isConfirmPasswordObscured) {
    _isConfirmPasswordObscured = isConfirmPasswordObscured;
    notifyListeners();
  }

  setSuccessMessage(String? message) {
    _successMessage = message;
    notifyListeners();
  }

  setBarangay(String? barangay) {
    _selectedBarangay = barangay;
    notifyListeners();
  }

  setError(String error) {
    _error = error;
  }
 
  void clearErrors() {
    _fieldErrors.clear();
    notifyListeners();
  }

  String? getFieldError(String fieldName) {
    final error = _fieldErrors[fieldName];
    if (error == null) return null;
    
    if (error is List && error.isNotEmpty) {
      return error.first.toString();
    }
    
    return error.toString();
  }

  Future<void> register({
    required String firstName,
    required String lastName,
    required String birthDate,
    required String phoneNumber,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    final AuthModel authModel = 
      AuthModel(firstName: firstName, lastName: lastName, birthDate: birthDate, phoneNumber: phoneNumber, email: email, password: password);

    try {
      final data = await _authService.register(authModel, passwordConfirmation);

      if(data == null) return null;

      if(data.containsKey('message')) {
        setSuccess(true);
        setSuccessMessage(data['message'].toString()); 
      }

    } on DioException catch (e) {

      final data = e.response?.data;

      print(data);

      if (data != null && data['errors'] != null) {
        setFieldErrors(Map<String, dynamic>.from(data['errors']));
      }else {
        setError(DioExceptionHandler.handleException(e));
      }

    } catch (e) {

      setError('An unexpected error occurred.');

    }

  }

}
