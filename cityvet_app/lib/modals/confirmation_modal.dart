import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

void showConfirmationModal(BuildContext context, String title, Widget widget) {
  Config().init(context);

  showDialog(
    context: context,
    builder: (context) {
      return Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
        insetPadding: EdgeInsets.symmetric(horizontal: 40),
        child: ConstrainedBox(
          constraints: BoxConstraints(maxWidth: 500),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: EdgeInsets.all(15),
                decoration: BoxDecoration(
                  border: Border(
                    bottom: BorderSide(width: 0.5, color: Color(0xFFDDDDDD)),
                  ),
                ),
                child: Text(
                  title,
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                  ),
                ),
              ),
              Container(
                padding: EdgeInsets.all(15),
                child: widget,
              ),
            ],
          ),
        ),
      );
    },
  );
}
