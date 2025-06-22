import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/views/login_view.dart';
import 'package:cityvet_app/views/main_screens/notification_view.dart';
import 'package:flutter/material.dart';

void main() {
  runApp(
    const MainApp()
  );
}

class MainApp extends StatelessWidget {
  const MainApp({super.key});

  @override
  Widget build(BuildContext context) {
    Config().init(context);
    return const MaterialApp(
      home: NotificationView(),
    );
  }
}
