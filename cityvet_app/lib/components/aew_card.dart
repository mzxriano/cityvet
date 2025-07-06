import 'package:cityvet_app/components/card.dart';
import 'package:cityvet_app/components/role.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class AewCard extends StatelessWidget {
  const AewCard({super.key});

  @override
  Widget build(BuildContext context) {
    final role = RoleWidget();

    return CustomCard(
      width: double.infinity, 
      color: Colors.white, 
      widget: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          SizedBox(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.start,
              children: [
                CircleAvatar(radius: 30,),
                const SizedBox(width: 20,),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Ms Aew',
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontMedium,
                        color: Config.tertiaryColor,
                      ),
                    ),
                    role['AEW'],
                  ],
                ),
              ],
            ),
          ),
          Icon(Icons.message_rounded, color: Config.tertiaryColor,),
        ],
      )
    );
  }
}