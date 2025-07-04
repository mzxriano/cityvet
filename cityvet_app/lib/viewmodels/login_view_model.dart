import 'package:cityvet_app/services/auth_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class LoginViewModel extends ChangeNotifier{

  final AuthStorage storage = AuthStorage();

  var _isLoading = false;
  var _isLogin = false;
  String? _error;
  Map<String, dynamic> _user = {};
  Map<String, dynamic> _fieldErrors = {};

  get isLoading => _isLoading;
  get isLogin => _isLogin;
  get error => _error;
  get user => _user;
  get fieldErrors => _fieldErrors;

  setLoading(bool isLoading) {
    _isLoading = isLoading;
    notifyListeners();
  }

  setLogin(bool isLogin){
    _isLogin = isLogin;
    notifyListeners();
  }

  setError(String error) {
    _error = error;
    notifyListeners();
  }

  setUser(Map<String, dynamic> user) {
    _user = user;
    notifyListeners();
  }

  setFieldErrors(Map<String, dynamic> fieldErrors){
    _fieldErrors = fieldErrors;
    notifyListeners();
  }

Future<void> login(String email, String password) async {
  try {
    setLoading(true);
    setError('');
    setFieldErrors({});

    final result = await AuthService().login(email, password);

    print(result.data['token']);

    final token = result.data['token'];
    if(token != null){
      await storage.saveToken(token);
      setLogin(true);
    }

  } on DioException catch (e) {
    final data = e.response?.data;

    // check if invalid credentials;
      print('Ito ay data: $data');
    if(e.response?.statusCode == 401 && data['errors'] != null) {
      setError(data['errors']);
    }

    if (data is Map<String, dynamic> && data['errors'] is Map<String, dynamic>) {
      setFieldErrors(Map<String, dynamic>.from(data['errors']));
    } 
    else if (data is Map<String, dynamic>) {
      final message = data['message'];
      if(message is String && message.isNotEmpty) {
        setError(data['message']);
      }
    }
    else {
      setError(DioExceptionHandler.handleException(e));
    }

  } catch (e) {
    print('Unexpected error: $e');
    setError('An unexpected error occurred. Please try again.');
  } finally {
    setLoading(false);
  }
}


}