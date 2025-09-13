import 'package:cityvet_app/models/user_model.dart';
import 'package:cityvet_app/services/user_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:flutter/material.dart';

class UserViewModel extends ChangeNotifier {

  UserModel? _user;
  bool _disposed = false;
  bool _isChangingPassword = false;
  String? _passwordError;

  UserModel? get user => _user;
  bool get isChangingPassword => _isChangingPassword;
  String? get passwordError => _passwordError;
  bool get needsPasswordChange => _user?.forcePasswordChange ?? false;

  @override
  void dispose() {
    _disposed = true;
    super.dispose();
  }

  void setUser(UserModel user) {
    if (_disposed) return;
    _user = user;
    notifyListeners();
  }

  void clearUser() {
    if (_disposed) return;
    _user = null;
    notifyListeners();
  }

    Future<bool> changePassword(String newPassword) async {
    try {
      _isChangingPassword = true;
      _passwordError = null;
      notifyListeners();

      final token = await AuthStorage().getToken(); 

      if (token == null) {
        _passwordError = 'User not authenticated';
        return false;
      }

      final response = await UserService().changePassword(
        password: newPassword, 
        passwordConfirmation: newPassword,
        token: token);
      
      if (response.statusCode == 200) {
        _user!.forcePasswordChange = false;
        notifyListeners();
      }
      
      return true;
    } catch (e) {
      _passwordError = 'Failed to change password: $e';
      return false;
    } finally {
      _isChangingPassword = false;
      notifyListeners();
    }
  }
  
}