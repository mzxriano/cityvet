import 'package:cityvet_app/utils/api_constant.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class ScheduleActivityViewModel extends ChangeNotifier {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: ApiConstant.baseUrl,
    headers: {
      'Accept': 'application/json',
    },
  ));
  final AuthStorage _authStorage = AuthStorage();
  
  bool _isLoading = false;
  String? _message;
  List<Map<String, dynamic>> _barangays = [];

  bool get isLoading => _isLoading;
  String? get message => _message;
  List<Map<String, dynamic>> get barangays => _barangays;

  void clearMessage() {
    _message = null;
    notifyListeners();
  }

  Future<void> loadBarangays() async {
    try {
      final response = await _dio.get('/barangay');
      
      if (response.statusCode == 200) {
        final List<dynamic> data = response.data;
        _barangays = data.map((barangay) => {
          'id': barangay['id'].toString(),
          'name': barangay['name'],
        }).toList();
        notifyListeners();
      }
    } catch (e) {
      debugPrint('Error loading barangays: $e');
      // Set fallback barangays
      _barangays = [
        {'id': '1', 'name': 'Barangay 1'},
        {'id': '2', 'name': 'Barangay 2'},
        {'id': '3', 'name': 'Barangay 3'},
      ];
      notifyListeners();
    }
  }

  Future<void> submitActivityRequest(Map<String, dynamic> activityData) async {
    _setLoading(true);
    _message = null;

    try {
      // Get token from secure storage
      final token = await _authStorage.getToken();
      if (token == null) {
        _message = 'Authentication required. Please log in again.';
        _setLoading(false);
        return;
      }

      final response = await _dio.post(
        '/activities/request',
        data: activityData,
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      
      if (response.statusCode == 200 || response.statusCode == 201) {
        _message = 'Activity request submitted successfully! Please wait for admin approval.';
      } else {
        _message = response.data['message'] ?? 'Failed to submit activity request. Please try again.';
      }
    } catch (e) {
      if (e is DioException) {
        if (e.response?.statusCode == 422) {
          _message = 'Validation error: Please check your input and try again.';
        } else if (e.response?.statusCode == 401) {
          _message = 'Authentication failed. Please log in again.';
        } else {
          _message = e.response?.data['message'] ?? 'Failed to submit activity request. Please try again.';
        }
      } else {
        _message = 'Error: ${e.toString()}';
      }
      debugPrint('Error submitting activity request: $e');
    } finally {
      _setLoading(false);
    }
  }

  void _setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }
}
