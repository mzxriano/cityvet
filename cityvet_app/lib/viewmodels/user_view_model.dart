import 'package:cityvet_app/models/user_model.dart';
import 'package:flutter/material.dart';

class UserViewModel extends ChangeNotifier {

  UserModel? _user;

  UserModel? get user => _user;

  void setUser(UserModel user) {
    _user = user;
    notifyListeners();
  }

  void clearUser() {
    _user = null;
    notifyListeners();
  }
  
}