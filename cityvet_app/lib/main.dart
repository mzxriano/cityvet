import 'package:cityvet_app/firebase_options.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/animal_view_model.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:cityvet_app/viewmodels/incident_viewmodel.dart';
import 'package:cityvet_app/views/force_password_change_view.dart';
import 'package:cityvet_app/views/login_view.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AnimalViewModel()),
        ChangeNotifierProvider(create: (_) => UserViewModel()),
        ChangeNotifierProvider(create: (_) => IncidentViewModel()),
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
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      home: Consumer<UserViewModel>(
        builder: (context, userVM, _) {
          if (userVM.user != null && userVM.needsPasswordChange) {
            return const ForcePasswordChangeView();
          }
          return const LoginView();
        },
      ),
    );
  }
}
