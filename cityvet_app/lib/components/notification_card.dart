import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class NotificationCard extends StatelessWidget {
  const NotificationCard({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      constraints: BoxConstraints(
        maxWidth: 700,
      ),
      padding: EdgeInsets.symmetric(vertical: 15, horizontal: 25),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(25),
        color: Color(0xFFE1E1E1)
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Sofia Doe',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontMedium,
            ),
          ),
          Config.heightSmall,
          Text(
            'commented on your post',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontSmall,
              color: Color(0xFF524F4F)
            ),
          ),
          Config.heightSmall,
          Text(
            '5 mins ago',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontXS,
              color: Color(0xFF524F4F)
            ),
          ),
        ],
      ),
    );
  }
}
