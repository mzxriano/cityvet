
import 'package:cityvet_app/modals/confirmation_modal.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

void showCreatePostModal(BuildContext context) {
  TextEditingController textEditingController = TextEditingController();
  Config().init(context);

  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    enableDrag: false,
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.zero),
    builder: (context) {
      bool isFieldNotEmpty = false;

      return DraggableScrollableSheet(
        initialChildSize: 1.0,
        expand: true,
        maxChildSize: 1.0,
        minChildSize: 1.0,
        builder: (context, scrollController) {
          return StatefulBuilder(
            builder: (context, setState) {
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
                                onPressed: isFieldNotEmpty ? () {
                                  showConfirmationModal(context, 'Confirmation Dialog', Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Are you sure you want to discard changes?',
                                        style: TextStyle(
                                          fontFamily: Config.primaryFont,
                                          fontSize: Config.fontSmall,
                                          color: Config.tertiaryColor
                                        ),
                                      ),
                                      Config.heightMedium,
                                      Row(
                                        children: [
                                          Expanded(
                                            child: ElevatedButton(
                                              onPressed: (){
                                                Navigator.pop(context);
                                              }, 
                                              style: ElevatedButton.styleFrom(
                                                padding: EdgeInsets.symmetric(vertical: 8, horizontal: 15),
                                                backgroundColor: Color(0xFF808080),
                                                foregroundColor: Colors.white,
                                                shape: RoundedRectangleBorder(
                                                  borderRadius: BorderRadius.circular(5),
                                                )
                                              ),
                                              child: Text(
                                                'Cancel',
                                                style: TextStyle(
                                                  fontFamily: Config.primaryFont,
                                                  fontSize: Config.fontSmall,
                                                  fontWeight: Config.fontW600,
                                                ),
                                              )
                                            ),
                                          ),
                                          const SizedBox(width: 10,),
                                          Expanded(
                                            child: ElevatedButton(
                                            onPressed: () {
                                              Navigator.pop(context);
                                              Future.delayed(Duration(milliseconds: 100), () {
                                                Navigator.pop(context); 
                                              });
                                            },
                                              style: ElevatedButton.styleFrom(
                                                padding: EdgeInsets.symmetric(vertical: 8, horizontal: 15),
                                                backgroundColor: Color(0xFFDD6454),
                                                foregroundColor: Colors.white,
                                                shape: RoundedRectangleBorder(
                                                  borderRadius: BorderRadius.circular(5),
                                                )
                                              ),
                                              child: Text(
                                                'Discard',
                                                style: TextStyle(
                                                  fontFamily: Config.primaryFont,
                                                  fontSize: Config.fontSmall,
                                                  fontWeight: Config.fontW600,
                                                ),
                                              )
                                            ),
                                          ),
                                        ],
                                      )
                                    ],
                                  ));
                                } : () => Navigator.pop(context),
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
                                    print('Posting: ${textEditingController.text}');
                                    Navigator.pop(context);
                                  }
                                : null,
                            style: ButtonStyle(
                              foregroundColor: WidgetStateProperty.resolveWith<Color>((states) {
                                if (states.contains(WidgetState.disabled)) {
                                  return Config.secondaryColor; 
                                }
                                return Colors.grey;
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
                          backgroundImage: AssetImage('assets/images/default.png'),
                          radius: 30,
                        ),
                        SizedBox(width: 20),

                        // Post details
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Francisco Mejia',
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontBig,
                                fontWeight: Config.fontW600,
                                color: Config.tertiaryColor,
                              ),
                            ),
                          ],
                        )
                      ],
                    ),

                    Config.heightMedium,

                    // Post body
                    TextField(
                      controller: textEditingController,
                      keyboardType: TextInputType.text,
                      maxLines: null,
                      minLines: 1,
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontMedium,
                        color: Config.tertiaryColor,
                      ),
                      decoration: InputDecoration(
                        hintText: 'Ano ang iyong nasa isip?',
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
                          isFieldNotEmpty = value.trim().isNotEmpty;
                        });
                      },
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

