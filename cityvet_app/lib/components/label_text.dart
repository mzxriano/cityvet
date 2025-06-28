import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class LabelText extends StatelessWidget {

  final String label;
  final bool isRequired;

  const LabelText({super.key, required this.label, required this.isRequired});

  @override
  Widget build(BuildContext context) {
        return RichText(
      text: TextSpan(
        text: label,
        style: TextStyle(
          fontFamily: Config.primaryFont,
          fontSize: Config.fontMedium,
          color: Colors.black,
        ),
        children: [
        (isRequired) ?
          TextSpan(
            text: '*',
            style: TextStyle(color: Colors.red),
          ) : TextSpan(
            text: '(Leave the field blank if unknown)',
            style: TextStyle(color: Config.tertiaryColor, fontFamily: Config.primaryFont, fontSize: Config.fontXS),
          ),

        ],
      ),
    );
  }
}