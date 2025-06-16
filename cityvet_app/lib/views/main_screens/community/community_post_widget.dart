import 'package:cityvet_app/components/role.dart';
import 'package:cityvet_app/modals/community_modals/post_comment_modal.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/views/main_screens/community/community_photo_grid.dart';
import 'package:flutter/material.dart';

class CommunityPostWidget extends StatefulWidget {
  const CommunityPostWidget({super.key});

  @override
  State<CommunityPostWidget> createState() => CommunityPostWidgetState();
}

class CommunityPostWidgetState extends State<CommunityPostWidget> {

  var roleWidget = RoleWidget();

  var urls = <String>[
  'assets/images/logo.png',
  'assets/images/default_avatar.png',
  'assets/images/forgot_pass.png',
  'assets/images/reset_pass.png',
  'assets/images/logo.png',
  'assets/images/logo.png',
];

  @override
  Widget build(BuildContext context) {
    Config().init(context);
    return Container(
      width: double.infinity,
      constraints: BoxConstraints(
        maxWidth: 700
      ),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(
          width: 0.5,
          color: Color(0xFFDDDDDD),
        ),
        borderRadius: BorderRadius.circular(15),
      ),
      child: SizedBox(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: EdgeInsets.all(20),
              decoration: BoxDecoration(
                border: Border(
                  bottom: BorderSide(
                    width: 0.5,
                    color: Color(0xFFDDDDDD),
                  )
                )
              ),
              child: Column(
                children: [
                  Row(
                    children: [
                      CircleAvatar(
                        backgroundImage: AssetImage('assets/images/default.png'),
                        radius: 30,
                      ),
                      const SizedBox(width: 20,),

                      // Post details
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Francisco Mejia',
                            style: TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: Config.fontMedium,
                              fontWeight: Config.fontW600,
                              color: Config.tertiaryColor,
                            ),
                          ),
                          roleWidget['Owner'],
                          Text(
                            '1hr ago',
                            style: TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: 10,
                              color: Config.tertiaryColor
                            ),
                          )
                        ],
                      )
                    ],
                  ),
                  Config.heightMedium,

                  // Post text
                  SizedBox(
                    width: double.infinity,
                    child: Expanded(
                      child: Text(
                        'Test post',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
                          color: Config.tertiaryColor,
                        ),
                        maxLines: 5,
                      )
                    ),
                  ),
                ],
              ),
            ),

            // Post images
            CommunityPhotoGrid(
              maxImages: 4, 
              imageUrls: urls, 
              onImageClicked: (index) => print('Image @ $index clicked!'), 
              onExpandClicked: () => print('Post clicked!')
            ),

            // Post comment button
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: (){
                  showCommentsModal(
                    context,
                    comments: [
                      'Nice post!',
                      'Test comment.',
                      'Nice comment!',
                      'Test comment! , Test comment!,  Test comment!, Test comment!, Test comment!, Test comment!, Test comment!',
                    ],
                    onSend: (text) {
                      print("New comment: $text");
                      // You could update your state here to add the comment
                    },
                  );
                }, 
                style: ElevatedButton.styleFrom(
                  padding: EdgeInsets.all(20),
                  backgroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    side: BorderSide(
                      width: 0.5,
                      color: Color(0xFFDDDDDD),
                    ),
                    borderRadius: BorderRadius.only(bottomLeft: Radius.circular(15), bottomRight: Radius.circular(15)),
                  ),
                  elevation: 0
                ),
                child: Text(
                  'Comment',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                    color: Config.tertiaryColor,
                  ),
                )
              ),
            ),
          ],
        ),
      ),
    );
  }
}