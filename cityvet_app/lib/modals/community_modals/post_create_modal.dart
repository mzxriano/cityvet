import 'dart:io';

import 'package:cityvet_app/modals/confirmation_modal.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';



void showCreatePostModal(BuildContext context) {
  Config().init(context);
  FocusNode focusNode = FocusNode();
  TextEditingController textEditingController = TextEditingController();
  bool isFieldNotEmpty = false;

  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    enableDrag: false,
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.zero),
    builder: (context) {
      final user = Provider.of<UserViewModel>(context).user;
      List<File?> images = [];

      return StatefulBuilder(
        builder: (context, setState) {
          // Image picker
          void pickImagesFromGallery() async {
            final pickedImages = await ImagePicker().pickMultiImage();
            if(pickedImages.isEmpty) return;
            setState((){
              images = pickedImages.map((image)=> File(image.path)).toList();
            });
          }

          return DraggableScrollableSheet(
            initialChildSize: 1.0,
            expand: true,
            maxChildSize: 1.0,
            minChildSize: 1.0,
            builder: (context, scrollController) {
              return Padding(
                padding: EdgeInsets.all(20),
                child: Column(
                  children: [
                    // Post Header
                    Container(
                      padding: EdgeInsets.symmetric(vertical: 10),
                      decoration: BoxDecoration(
                        border: Border(
                          bottom: BorderSide(width: 0.5, color: Color(0xFFDDDDDD)),
                        ),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              IconButton(
                                onPressed: () async {
                                  if (textEditingController.text.trim().isNotEmpty) {
                                    final discard = await showConfirmationModal(context);

                                    if(discard == true) {
                                      textEditingController.dispose();
                                      Navigator.pop(context);
                                    }

                                  } else {
                                    Navigator.pop(context);
                                  }
                                },
                                icon: Icon(Icons.arrow_back_ios_rounded),
                              ),
                              SizedBox(width: 20),
                              const Text(
                                'Create Post',
                                style: TextStyle(
                                  fontFamily: Config.primaryFont,
                                  fontSize: Config.fontMedium,
                                  fontWeight: Config.fontW600,
                                ),
                              )
                            ],
                          ),
                          TextButton(
                            onPressed: isFieldNotEmpty
                                ? () {
                                    Navigator.pop(context);
                                  }
                                : null,
                            style: ButtonStyle(
                              foregroundColor: WidgetStateProperty.resolveWith<Color>((states) {
                                if (states.contains(WidgetState.disabled)) {
                                  return Config.secondaryColor; 
                                }
                                return Colors.blueAccent;
                              }),
                            ),
                            child: Text(
                              'Post',
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontMedium,
                                fontWeight: Config.fontW600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    Config.heightBig,

                    Row(
                      children: [
                      CircleAvatar(
                        radius: 30,
                        backgroundImage: user?.imageUrl != null ? 
                          NetworkImage(user!.imageUrl!) : 
                          null,
                      ),
                        SizedBox(width: 20),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '${user?.firstName} ${user?.lastName}' ,
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontBig,
                                fontWeight: Config.fontW600,
                                color: Config.tertiaryColor,
                              ),
                              softWrap: true,
                              maxLines: 3,
                            ),
                          ],
                        )
                      ],
                    ),

                    Config.heightMedium,

                    TextField(
                      controller: textEditingController,
                      focusNode: focusNode,
                      keyboardType: TextInputType.text,
                      maxLines: null,
                      minLines: 1,
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontMedium,
                        color: Config.tertiaryColor,
                      ),
                      decoration: InputDecoration(
                        hintText: 'Start writing here',
                        hintStyle: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontMedium,
                          color: Config.tertiaryColor,
                        ),
                        border: OutlineInputBorder(borderSide: BorderSide.none),
                        focusedBorder: OutlineInputBorder(borderSide: BorderSide.none),
                      ),
                      onChanged: (value) {
                        setState(() {
                          isFieldNotEmpty = textEditingController.text.isNotEmpty;
                        }); 
                      },
                    ),

                    Config.heightMedium,

                    Container(
                      width: double.infinity,
                      decoration: BoxDecoration(
                        border: Border.all(
                          width: 1,
                          color: Colors.blueAccent,
                        ),
                        borderRadius: BorderRadius.circular(15),
                      ),
                      child: IconButton(
                        onPressed: () => pickImagesFromGallery(), 
                        icon: Icon(Icons.photo, size: 40, color: Colors.blueAccent,)
                      ),
                    ),

                    Config.heightMedium,

                      // Display selected images
                      if (images.isNotEmpty)
                        Wrap(
                          spacing: 10,
                          runSpacing: 10,
                          children: images.map((file) {
                            return ClipRRect(
                              borderRadius: BorderRadius.circular(10),
                              child: Image.file(
                                file!,
                                width: 100,
                                height: 100,
                                fit: BoxFit.cover,
                              ),
                            );
                          }).toList(),
                        ),
                  ],
                ),
              );
            },
          );
        },
      );
    },
  );
}