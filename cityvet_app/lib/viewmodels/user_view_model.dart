import 'package:cityvet_app/models/user_model.dart';
import 'package:flutter/material.dart';

class UserViewModel extends ChangeNotifier {

  UserModel? _user;
  bool _disposed = false;

  UserModel? get user => _user;

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
  
}