import 'package:cityvet_app/main_layout.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/animal_form_view_model.dart';
import 'package:cityvet_app/viewmodels/animal_view_model.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

void main() {
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AnimalFormViewModel()),
        ChangeNotifierProvider(create: (_) => AnimalViewModel())
      ],
      child: const MainApp(),
    ),
  );
}

class MainApp extends StatelessWidget {
  const MainApp({super.key});

  @override
  Widget build(BuildContext context) {
    Config().init(context);
    return const MaterialApp(
      debugShowCheckedModeBanner: false,
      home: MainLayout(),
    );
  }
}
