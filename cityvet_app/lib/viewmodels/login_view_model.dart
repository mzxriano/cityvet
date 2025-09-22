import 'package:cityvet_app/models/user_model.dart';
import 'package:cityvet_app/services/auth_service.dart';
import 'package:cityvet_app/services/user_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
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

    print('DioException: ${e.response?.data}');
    print('Status code: ${e.response?.statusCode}');
    print('Type: ${e.response?.data.runtimeType}');

    // Check if data is null first
    if (data == null) {
      setError('Network error. Please check your connection and try again.');
      return;
    }

    // Handle authentication errors (401)
    if(statusCode == 401 && data is Map<String, dynamic> && data['errors'] != null) {
      setError(data['errors']);
      setMessage(data['message'] ?? 'Authentication failed');
      return;
    }

    // Handle banned account (403)
    if(statusCode == 403 && data is Map<String, dynamic> && data['error'] == 'banned_account') {
      setError(data['error']);
      setMessage(data['message'] ?? 'Account is banned');
      return;
    }

    // Handle user not found (404)
    if(statusCode == 404 && data is Map<String, dynamic> && data['error'] == 'user_not_found') {
      setError(data['error']);
      setMessage(data['message'] ?? 'User not found');
      return;
    }

    // Handle invalid credentials (400)
    if(statusCode == 400 && data is Map<String, dynamic> && data['error'] == 'invalid_credentials') {
      setError(data['error']);
      setMessage(data['message'] ?? 'Invalid email or password');
      return;
    }

    // Handle email not verified (400)
    if(statusCode == 400 && data is Map<String, dynamic> && data['error'] == 'email_not_verified') {
      setEmailVerify(false);
      setError(data['error']);
      setMessage('Email not verified. Please verify your email.');
      return;
    }

    // Handle 422 validation errors
    if (statusCode == 422 && data is Map<String, dynamic> && data['errors'] is Map<String, dynamic>) {
      setFieldErrors(Map<String, dynamic>.from(data['errors']));
      setMessage(data['message'] ?? 'Validation failed');
      return;
    } 

    // Handle general 400 errors (like email not found)
    if (statusCode == 400 && data is Map<String, dynamic>) {
      final message = data['message'];
      if(message is String && message.isNotEmpty) {
        setError('login_failed');
        setMessage(message);
        return;
      }
    }

    // Handle other data formats
    if (data is Map<String, dynamic>) {
      final message = data['message'];
      if(message is String && message.isNotEmpty) {
        setError('login_failed');
        setMessage(message);
        return;
      }
    }

    // Fallback error handling
    setError('login_failed');
    setMessage('Login failed. Please check your credentials and try again.');

  } catch (e) {
    print('Unexpected error: $e');
    setError('An unexpected error occurred. Please try again.');
    setMessage('Please check your internet connection and try again.');
  } finally {
    setLoading(false);
  }
}


}