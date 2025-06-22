import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class NotificationView extends StatelessWidget {
  const NotificationView({super.key});

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Padding(
        padding: Config.paddingScreen,
        child: Column(
          children: [
            Text(
              'Notifications',
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontMedium,
                fontWeight: Config.fontW600,
              ),
            )
          ],
        )
      ),
    );
  }
}