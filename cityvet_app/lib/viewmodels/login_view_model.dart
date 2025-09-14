import 'package:cityvet_app/models/user_model.dart';
import 'package:cityvet_app/services/auth_service.dart';
import 'package:cityvet_app/services/user_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class LoginViewModel extends ChangeNotifier{

  final AuthStorage storage = AuthStorage();

  var _isLoading = false;
  var _isLogin = false;
  var _isEmailVerified = false;
  String? _message;
  String? _error;
  UserModel? _user;
  Map<String, dynamic> _fieldErrors = {};

  get isLoading => _isLoading;
  get isLogin => _isLogin;
  get error => _error;
  UserModel? get user => _user;
  get fieldErrors => _fieldErrors;
  get isEmailVerified => _isEmailVerified;
  get message => _message;

  setLoading(bool isLoading) {
    _isLoading = isLoading;
    notifyListeners();
  }

  setLogin(bool isLogin){
    _isLogin = isLogin;
    notifyListeners();
  }

  setError(String? error) {
    _error = error;
    notifyListeners();
  }

  setMessage(String? message) {
    _message = message;
    notifyListeners();
  }

  setUser(UserModel user) {
    _user = user;
    notifyListeners();
  }

  setFieldErrors(Map<String, dynamic> fieldErrors){
    _fieldErrors = fieldErrors;
    notifyListeners();
  }

  setEmailVerify(bool value) {
    _isEmailVerified = value;
    notifyListeners();
  }

Future<void> login(String email, String password) async 
{
  try {
    setLoading(true);
    setError(null);
    setFieldErrors({});

    final result = await AuthService().login(email, password);
    final token = result.data['token'];

    if(token != null) {
      await storage.saveToken(token);
      final userResponse = await UserService().fetchUser(token);
      
      if(userResponse.containsKey('user')) {
        final userData = userResponse['user'];
        print('User Data: $userData'); 
        
        final user = UserModel.fromJson(userData);
        setUser(user);

        print('Force Password Change: ${user.forcePasswordChange}'); 
                
        if (userData['id'] != null) {
          await storage.saveUserId(userData['id'].toString());
        }

        setLogin(true);

      }
    }
  } 

  on DioException catch (e) 
  {
    final data = e.response?.data;
    final statusCode = e.response?.statusCode;

    if(e.response?.statusCode == 401 && data['errors'] != null) {
      setError(data['errors']);
      setMessage(data['message']);
      return;
    }

    if(statusCode == 403 && data['error'] == 'banned_account') {
      setError(data['error']);
      setMessage(data['message']);
      return;
    }

    if(statusCode == 404 && data['error'] == 'user_not_found') {
      setError(data['error']);
      setMessage(data['message']);
      return;
    }

    if(statusCode == 400 && data['error'] == 'invalid_credentials') {
      setError(data['error']);
      setMessage(data['message']);
      return;
    }

    if(statusCode == 400 && data['error'] == 'email_not_verified') {
      setEmailVerify(!true);
      setError(data['error']);
      setMessage('Email not verified. Please verify your email.');
      return;
    }

    if (data is Map<String, dynamic> && data['errors'] is Map<String, dynamic>) {
      setFieldErrors(Map<String, dynamic>.from(data['errors']));
      return;
    } 
    else if (data is Map<String, dynamic>) {
      final message = data['message'];
      if(message is String && message.isNotEmpty) {
        setError(data['message']);
        return;
      }
    }
    else {
      setError(DioExceptionHandler.handleException(e));
      return;
    }

  } catch (e) {
    setError('An unexpected error occurred. Please try again.');
  } finally {
    setLoading(false);
  }
}


}