import 'package:cityvet_app/main_layout.dart';
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
      home: MainLayout(),
    );
  }
}
