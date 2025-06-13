import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/views/main_screens/community/community_post_widget.dart';
import 'package:flutter/material.dart';

class CommunityView extends StatelessWidget {
  const CommunityView({super.key});

  @override
  Widget build(BuildContext context) {
    Config().init(context);
    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Community',
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontMedium,
                fontWeight: Config.fontW600,
              ),
            ),
            ElevatedButton(
              onPressed: (){},
              style: ElevatedButton.styleFrom(
                backgroundColor: Color(0xFF30EF53),
                padding: EdgeInsets.symmetric(vertical: 3, horizontal: 25)
              ), 
              child: Text(
                'Add post',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: Config.fontXS,
                  color: Colors.white,
                ),
              )
            ),
          ],
        ),
        Config.heightBig,
        CommunityPostWidget(),
      ],
    );
  }
}
