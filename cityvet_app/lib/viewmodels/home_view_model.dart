import 'package:cityvet_app/models/activity_model.dart';
import 'package:cityvet_app/models/aew_model.dart';
import 'package:cityvet_app/services/activity_service.dart';
import 'package:cityvet_app/services/aew_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class HomeViewModel extends ChangeNotifier {
  final AuthStorage storage = AuthStorage();
  final ActivityService _activityService = ActivityService();

  // Single upcoming activity (for the top section)
  ActivityModel? _upcomingActivity;
  
  // Single ongoing activity (for staff/veterinarian section)
  ActivityModel? _ongoingActivity;
  
  // Recent completed activities (for "See Recent Activities") - still a list
  List<ActivityModel>? _recentActivities;
  
  bool _isLoadingUpcoming = false;
  bool _isLoadingOngoing = false;
  bool _isLoadingRecent = false;
  String? _error;
  bool _disposed = false;

  // Getters - Updated for single activities
  ActivityModel? get upcomingActivity => _upcomingActivity;
  ActivityModel? get ongoingActivity => _ongoingActivity;
  List<ActivityModel>? get recentActivities => _recentActivities;
  
  bool get isLoadingUpcoming => _isLoadingUpcoming;
  bool get isLoadingOngoing => _isLoadingOngoing;
  bool get isLoadingRecent => _isLoadingRecent;
  
  String? get error => _error;

  // Legacy getters for backward compatibility
  ActivityModel? get activity => _upcomingActivity;
  List<ActivityModel>? get upcomingActivities => _upcomingActivity != null ? [_upcomingActivity!] : null;
  List<ActivityModel>? get ongoingActivities => _ongoingActivity != null ? [_ongoingActivity!] : null;
  bool get isLoading => _isLoadingUpcoming;

  @override
  void dispose() {
    _disposed = true;
    super.dispose();
  }

  void setUpcomingActivity(ActivityModel? activity) {
    if (_disposed) return;
    _upcomingActivity = activity;
    notifyListeners();
  }

  void setOngoingActivity(ActivityModel? activity) {
    if (_disposed) return;
    _ongoingActivity = activity;
    notifyListeners();
  }

  void setRecentActivities(List<ActivityModel> activities) {
    if (_disposed) return;
    _recentActivities = activities;
    notifyListeners();
  }

  void _setLoadingUpcoming(bool loading) {
    if (_disposed) return;
    _isLoadingUpcoming = loading;
    notifyListeners();
  }

  void _setLoadingOngoing(bool loading) {
    if (_disposed) return;
    _isLoadingOngoing = loading;
    notifyListeners();
  }

  void _setLoadingRecent(bool loading) {
    if (_disposed) return;
    _isLoadingRecent = loading;
    notifyListeners();
  }

  void _setError(String? error) {
    if (_disposed) return;
    _error = error;
    notifyListeners();
  }

  // Fetch single upcoming activity
  Future<void> fetchUpcomingActivity() async {
    if (_disposed) return;
    _setLoadingUpcoming(true);
    _setError(null);

    try {
      final String? token = await storage.getToken();
      if (token == null) {
        _setError('No authentication token found');
        return;
      }

      final activity = await _activityService.fetchUpcomingActivity(token);
      if (_disposed) return;
      setUpcomingActivity(activity);
      
    } on DioException catch (e) {
      if (_disposed) return;
      final errorMessage = _handleDioException(e);
      _setError(errorMessage);
      
    } catch (e) {
      if (_disposed) return;
      _setError('Error fetching upcoming activity: $e');
      print('Error fetching upcoming activity: $e');
    } finally {
      if (_disposed) return;
      _setLoadingUpcoming(false);
    }
  }

  // Fetch single ongoing activity
  Future<void> fetchOngoingActivity() async {
    if (_disposed) return;
    _setLoadingOngoing(true);
    _setError(null);

    try {
      final String? token = await storage.getToken();
      if (token == null) {
        _setError('No authentication token found');
        return;
      }

      final activity = await _activityService.fetchOngoingActivity(token);
      if (_disposed) return;
      setOngoingActivity(activity);
      
    } on DioException catch (e) {
      if (_disposed) return;
      final errorMessage = _handleDioException(e);
      _setError(errorMessage);
      
    } catch (e) {
      if (_disposed) return;
      _setError('Error fetching ongoing activity: $e');
      print('Error fetching ongoing activity: $e');
    } finally {
      if (_disposed) return;
      _setLoadingOngoing(false);
    }
  }

  // Fetch recent activities list - FIXED VERSION
  Future<void> fetchRecentActivities() async {
    if (_disposed) return;
    _setLoadingRecent(true);
    _setError(null);

    try {
      final String? token = await storage.getToken();
      if (token == null) {
        _setError('No authentication token found');
        return;
      }

      final activities = await _activityService.fetchRecentActivities(token);
      if (_disposed) return;
      setRecentActivities(activities);
      
    } on DioException catch (e) {
      if (_disposed) return;
      final errorMessage = _handleDioException(e);
      _setError(errorMessage);
      
    } catch (e) {
      if (_disposed) return;
      _setError('Error fetching recent activities: $e');
      print('Error fetching recent activities: $e');
    } finally {
      if (_disposed) return;
      _setLoadingRecent(false);
    }
  }

  // Legacy methods for backward compatibility
  Future<void> fetchActivity() async {
    await fetchUpcomingActivity();
  }

  Future<void> fetchUpcomingActivities() async {
    await fetchUpcomingActivity();
  }

  Future<void> fetchOngoingActivities() async {
    await fetchOngoingActivity();
  }

  String _handleDioException(DioException e) {
    final error = e.response?.data;
    
    if (error is Map<String, dynamic>) {
      return error['message'] ?? 
             error['error'] ?? 
             'An error occurred';
    } else if (error is String) {
      return error;
    } else {
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
    if (_disposed) return;
    _setError(null);
  }

  // Method to refresh all data
  Future<void> refreshData() async {
    if (_disposed) return;
    await Future.wait([
      fetchUpcomingActivity(),
      fetchOngoingActivity(),
      fetchRecentActivities(),
    ]);
  }

  // Aew service
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

      print(response);

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