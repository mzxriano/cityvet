import 'package:cityvet_app/views/forgot_pass_view.dart';
import 'package:cityvet_app/views/login_view.dart';
import 'package:cityvet_app/views/reset_pass_view.dart';
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
    return const MaterialApp(
      home: ResetPassView(),
    );
  }
}
