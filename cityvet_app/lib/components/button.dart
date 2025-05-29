import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class Button extends StatelessWidget {
  const Button({
    super.key,
    required this.width,
    required this.title,
    required this.onPressed,
  });

  final double width;
  final String title;
  final Function() onPressed;

  @override
  Widget build(BuildContext context) {

    Config().init(context);

    return SizedBox(
      width: width,
      child: ElevatedButton(
        onPressed: onPressed, 
        style: ElevatedButton.styleFrom(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
          backgroundColor: Config.primaryColor,
          foregroundColor: Colors.white,
          padding: EdgeInsets.symmetric(horizontal: 10.0, vertical: 20.0)
        ),
        child: Text(
          title,
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontSize: Config.fontMedium
          ),
        )
      ),
    );
  }
}