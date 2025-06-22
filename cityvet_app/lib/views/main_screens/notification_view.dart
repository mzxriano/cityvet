import 'package:cityvet_app/components/notification_card.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class NotificationView extends StatelessWidget {
  const NotificationView({super.key});

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Notifications',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontBig,
              fontWeight: Config.fontW600,
            ),
          ),
          Config.heightMedium,
          Expanded(
            child: ListView.separated(
              itemBuilder: (context, index) {
                return NotificationCard();
              }, 
              separatorBuilder: (context, index) => const SizedBox(height: 10,), 
              itemCount: 10,
            ),
          ),
        ],
      ),
    );
  }
}