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
  String? _error;
  UserModel? _user;
  Map<String, dynamic> _fieldErrors = {};

  get isLoading => _isLoading;
  get isLogin => _isLogin;
  get error => _error;
  UserModel? get user => _user;
  get fieldErrors => _fieldErrors;
  get isEmailVerified => _isEmailVerified;

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

Future<void> login(String email, String password) async {
  try {
    setLoading(true);
    setError(null);
    setFieldErrors({});
    setEmailVerify(false);

    final result = await AuthService().login(email, password);
    final token = result.data['token'];

    if(token != null){
      await storage.saveToken(token);


      final userResponse = await UserService().fetchUser(token);
      
      if(userResponse.containsKey('user')) {
        final userData = userResponse['user'];
        final user = UserModel.fromJson(userData);
        print('user ${userData}');
        setUser(user);
        setLogin(true);
      }

    }

  } on DioException catch (e) {
    final data = e.response?.data;

    // check if invalid credentials;
      print('Ito ay data: $data');
    if(e.response?.statusCode == 401 && data['errors'] != null) {
      setError(data['errors']);
    }

    if(data['error'] != null && data['error'] == 'user_not_found') {
      setError(data['error']);
    }

    if(data['error'] != null && data['error'] == 'email_not_verified') {
      setEmailVerify(!true);
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
    print(user);
    print('Unexpected error: $e');
    setError('An unexpected error occurred. Please try again.');
  } finally {
    setLoading(false);
  }
}


}