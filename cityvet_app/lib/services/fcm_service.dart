import 'dart:convert';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

class FcmService {
  static final FcmService _instance = FcmService._internal();
  factory FcmService() => _instance;
  FcmService._internal();

  final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  final Dio _dio = Dio(BaseOptions(
    baseUrl: ApiConstant.baseUrl, 
  ));

  Future<void> initialize({
    required int userId,
    String deviceType = 'android',
  }) async {
    await _requestPermission();
    _setupForegroundListener();
    _setupBackgroundHandler();
    await _sendTokenToBackend(userId: userId, deviceType: deviceType);
  }

  Future<void> _requestPermission() async {
    NotificationSettings settings = await _messaging.requestPermission();
    if (kDebugMode) {
      print('User granted permission: \\${settings.authorizationStatus}');
    }
  }

  void _setupForegroundListener() {
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      if (kDebugMode) {
        print('Received a message in the foreground!');
        print('Message data: \\${message.data}');
        if (message.notification != null) {
          print('Notification: \\${message.notification}');
        }
      }
      // TODO: Show a local notification or update UI
    });
  }

  void _setupBackgroundHandler() {
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
  }

  Future<void> _sendTokenToBackend({
    required int userId,
    required String deviceType,
  }) async {
    String? token = await _messaging.getToken();
    String? jwtToken = await AuthStorage().getToken();

    if(jwtToken == null) return;

    if (kDebugMode) {
      print('FCM Token: \\${token ?? "No token"}');
    }
    if (token != null) {
      try {
        final response = await _dio.post(
          '/save-device-token',
          options: Options(
            headers: {
              'Content-Type': 'application/json',
              'Authorization': 'Bearer $jwtToken',
            },
          ),
          data: jsonEncode({
            'device_token': token,
            'user_id': userId,
            'device_type': deviceType,
          }),
        );
        if (kDebugMode) {
          print('Sent token to backend: \\${response.statusCode}');
        }
      } catch (e) {
        if (kDebugMode) {
          print('Failed to send token to backend: $e');
        }
      }
    }
  }
}

Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  if (kDebugMode) {
    print('Handling a background message: \\${message.messageId}');
  }
  // TODO: Handle background notification
} 