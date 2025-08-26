import 'package:cityvet_app/models/aew_model.dart';
import 'package:cityvet_app/services/aew_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:flutter/material.dart';

class AewViewModel extends ChangeNotifier {
  bool _isLoadingAEW = false;
  bool get isLoadingAew => _isLoadingAEW;

  setLoadingAew(bool val) {
    _isLoadingAEW = val;
    notifyListeners();
  }

  Future<List<AewModel>> fetchAEWUsers() async {
    setLoadingAew(true);

    final token = await AuthStorage().getToken();

    if (token == null) return [];

    try {
      final response = await AewService().fetchAEWUsers(token);

      if (response.data['aew_users'] != null) {
        final List<dynamic> aewList = response.data['aew_users'];
        return aewList.map((json) => AewModel.fromJson(json)).toList();
      }

      return [];
    } catch (e) {
      print('Error fetching AEW users: $e');
      return [];
    } finally {
      setLoadingAew(false);
    }
  }
}
