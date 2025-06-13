import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

void showCommentsModal(BuildContext context, {
  required List<String> comments,
  required Function(String) onSend,
}) {
  TextEditingController commentController = TextEditingController();
  Config().init(context);

  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.white,
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
    ),
    builder: (context) {
      return Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom,
        ),
        child: SizedBox(
          height: MediaQuery.of(context).size.height * 0.65,
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 12.0),
                child: Text('Comments', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              ),
              Divider(),

              Expanded(
                child: ListView.separated(
                  padding: EdgeInsets.all(16),
                  itemCount: comments.length,
                  separatorBuilder: (_, __) => Divider(height: 20),
                  itemBuilder: (context, index) {
                    return Padding(
                      padding: const EdgeInsets.symmetric(vertical: 10.0),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          CircleAvatar(radius: 25, backgroundImage: AssetImage('assets/images/default.png'),),
                          SizedBox(width: 10),
                          Flexible(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Text(
                                      'Sofia Smith',
                                      style: TextStyle(
                                        fontFamily: Config.primaryFont,
                                        fontSize: Config.fontMedium,
                                      ),
                                    ),
                                    const SizedBox(width: 15,),
                                    Text(
                                      '1min ago',
                                      style: TextStyle(
                                        fontFamily: Config.primaryFont,
                                        fontSize: Config.fontXS,
                                      ),
                                    ),
                                  ],
                                ),
                                Container(
                                  padding: EdgeInsets.all(15),
                                  decoration: BoxDecoration(
                                    color: Config.secondaryColor,
                                    borderRadius: BorderRadius.circular(15),
                                  ),
                                  child: Text(
                                    comments[index],
                                    style: TextStyle(
                                      fontFamily: Config.primaryFont,
                                      fontSize: Config.fontSmall,
                                      color: Config.tertiaryColor,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                ),
              ),

              Divider(height: 0.3, color: Color(0xFFD9D9D9),),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 12.0, vertical: 8),
                child: Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: commentController,
                        decoration: InputDecoration(
                          hintText: "Write a comment...",
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(30),
                          ),
                          contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        ),
                      ),
                    ),
                    SizedBox(width: 8),
                    IconButton(
                      icon: Icon(Icons.send, color: Colors.blue),
                      onPressed: () {
                        if (commentController.text.trim().isNotEmpty) {
                          onSend(commentController.text.trim());
                          commentController.clear();
                        }
                      },
                    )
                  ],
                ),
              ),
            ],
          ),
        ),
      );
    }
  );
}
