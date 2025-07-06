import 'package:cityvet_app/models/activity_model.dart';
import 'package:cityvet_app/services/activity_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class HomeViewModel extends ChangeNotifier{
  final AuthStorage storage = AuthStorage();

  ActivityModel? _activity;

  ActivityModel? get activity => _activity;

  void setActivity(ActivityModel activity) {
    _activity = activity;
    notifyListeners();
  }

  Future<void> fetchActivity() async {

    try {
      final String? token = await storage.getToken();
      if(token == null) return;
 
      final response = await ActivityService().fetchActivity(token);
      print('response: $response');

      final activity = ActivityModel.fromJson(response);

      setActivity(activity);
    } on DioException catch (e) {
      final error = e.response?.data;

      DioExceptionHandler.handleException(error);
    } catch (e) {
      print('Error fetching activity: $e');
    }

  }
    
}