import 'package:flutter/material.dart';

// This configuration is mainly for the ui struture
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

    static const heightBig = SizedBox(
    height: 35,
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

  // Padding styles
  static const paddingTextfield = EdgeInsets.symmetric(horizontal: 15.0, vertical: 20.0);
  static EdgeInsets get paddingScreen => EdgeInsets.symmetric(horizontal: screenWidth! * 0.09, vertical: screenHeight! * 0.05);

  // Colors  
  static const Color primaryColor = Color(0xFF8ED968);

  static const Color secondaryColor = Color(0xFFDADADA);

  static const Color tertiaryColor = Color(0xFF858585);

  static const Color color524F4F = Color(0xFF524F4F);

  // Font
  static const primaryFont = 'Poppins';

    static const double fontXS = 13;

  static const double fontSmall = 15;

  static const double fontMedium = 18;

  static const double fontBig = 25;

  // Image
  static final Image primaryLogo = Image.asset('assets/images/cityvet-logo.png', width: 100, height: 100,);

  // IconButton
  static const Icon backButtonIcon = Icon(Icons.arrow_back_ios_new_rounded);

  // Font Weight
  static const FontWeight fontW600 = FontWeight.w600;

}
