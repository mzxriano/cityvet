import 'package:cityvet_app/models/activity_model.dart';
import 'package:cityvet_app/services/activity_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class HomeViewModel extends ChangeNotifier {
  final AuthStorage storage = AuthStorage();
  final ActivityService _activityService = ActivityService();

  ActivityModel? _activity;
  List<ActivityModel>? _recentActivities;
  bool _isLoading = false;
  bool _isLoadingRecent = false;
  String? _error;

  // Getters
  ActivityModel? get activity => _activity;
  List<ActivityModel>? get recentActivities => _recentActivities;
  bool get isLoading => _isLoading;
  bool get isLoadingRecent => _isLoadingRecent;
  String? get error => _error;

  void setActivity(ActivityModel activity) {
    _activity = activity;
    notifyListeners();
  }

  void setRecentActivities(List<ActivityModel> list) {
    _recentActivities = list;
    notifyListeners();
  }

  void _setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  void _setLoadingRecent(bool loading) {
    _isLoadingRecent = loading;
    notifyListeners();
  }

  void _setError(String? error) {
    _error = error;
    notifyListeners();
  }

  Future<void> fetchActivity() async {
    _setLoading(true);
    _setError(null);

    try {
      final String? token = await storage.getToken();
      if (token == null) {
        _setError('No authentication token found');
        return;
      }

      final response = await _activityService.fetchActivity(token);
      final activity = ActivityModel.fromJson(response);
      print(activity);
      setActivity(activity);
      
    } on DioException catch (e) {
      final errorMessage = _handleDioException(e);
      _setError(errorMessage);
      
    } catch (e) {
      _setError('Error fetching activity: $e');
      print('Error fetching activity: $e');
    } finally {
      _setLoading(false);
    }
  }

  Future<void> fetchRecentActivities() async {
    _setLoadingRecent(true);
    _setError(null);

    try {
      final String? token = await storage.getToken();
      if (token == null) {
        _setError('No authentication token found');
        return;
      }

      final activities = await _activityService.fetchRecentActivities(token);
      print(activities);
      setRecentActivities(activities);
      
    } on DioException catch (e) {
      final errorMessage = _handleDioException(e);
      _setError(errorMessage);
      
    } catch (e) {
      _setError('Error fetching recent activities: $e');
      print('Error fetching recent activities: $e');
    } finally {
      _setLoadingRecent(false);
    }
  }

  String _handleDioException(DioException e) {
    final error = e.response?.data;
    
    if (error is Map<String, dynamic>) {
      // Try to extract error message from common API response formats
      return error['message'] ?? 
             error['error'] ?? 
             'An error occurred';
    } else if (error is String) {
      return error;
    } else {
      // Use your existing DioExceptionHandler if available
      try {
        DioExceptionHandler.handleException(e);
        return 'Network error occurred';
      } catch (handlerError) {
        return 'Network error occurred';
      }
    }
  }

  // Method to clear error state
  void clearError() {
    _setError(null);
  }

  // Method to refresh all data
  Future<void> refreshData() async {
    await Future.wait([
      fetchActivity(),
      fetchRecentActivities(),
    ]);
  }
}