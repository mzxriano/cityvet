import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

class Config {

  static MediaQueryData? mediaQueryData;
  static double? screenWidth;
  static double? screenHeight;

  void init(BuildContext context) {
    mediaQueryData = MediaQuery.of(context);
    screenWidth = mediaQueryData!.size.width;
    screenHeight = mediaQueryData!.size.height;
  }

  // Height sizes
  static const heightSmall = SizedBox(
    height: 10,
  );

  static const heightMedium = SizedBox(
    height: 20,
  );

  // Text field styles
  static const borderRadius = OutlineInputBorder(
    borderRadius: BorderRadius.all(Radius.circular(10))
  );

  static const enabledBorder = OutlineInputBorder(
    borderRadius: BorderRadius.all(Radius.circular(10)),
    borderSide: BorderSide.none,
  );

  static const focusedBorder = OutlineInputBorder(
    borderRadius: BorderRadius.all(Radius.circular(10)),
    borderSide: BorderSide(
      width: 1,
      color: primaryColor,
    )
  );

  static const paddingTextfield = EdgeInsets.symmetric(horizontal: 15.0, vertical: 20.0);

  // Colors  
  static const Color primaryColor = Color(0xFF8ED968);

  static const Color secondaryColor = Color(0xFFDADADA);

  // Font
  static const primaryFont = 'Poppins';

  static const double fontSmall = 15;

  static const double fontMedium = 18;

  static const double fontBig = 25;

}
