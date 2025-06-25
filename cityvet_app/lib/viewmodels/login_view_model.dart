import 'package:cityvet_app/services/auth_service.dart';
import 'package:flutter/material.dart';

class LoginViewModel extends ChangeNotifier{

  var _isLoading = false;
  var _isLogin = false;
  Map<String, dynamic> _user = {};
  Map<String, dynamic> _fieldErrors = {};

  get isLoading => _isLoading;
  get isLogin => _isLogin;
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

  setUser(Map<String, dynamic> user) {
    _user = user;
  }

  setFieldErrors(Map<String, dynamic> fieldErrors){
    _fieldErrors = fieldErrors;
  }

Future<void> login(String email, String password) async {
  try {
    setLoading(true);

    var result = await AuthService().login(email, password);

    if (result.isNotEmpty) {

      if (result.containsKey('user') && result['user'] != null) {
        setUser(result['user']);  
        setLogin(true);           
      } 
      else if(result.containsKey('error')) {
        setLogin(false);
        
        if (result['error'] is String) {
          setFieldErrors({'password': [result['error']]});
        }
        else if (result['error'] is Map) {
          setFieldErrors(Map<String, dynamic>.from(result['error']));
        }
      } 
      else {
        setLogin(false);        
        throw Exception('User data not found in response.');
      } 

    } 
    else {
      setLogin(false);        
      throw Exception('Invalid login credentials or server error');
    }
  } finally {
    setLoading(false);  
  }
}



}