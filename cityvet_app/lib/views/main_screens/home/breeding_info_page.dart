import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class BreedingInfoPage extends StatelessWidget {
  final String animalType;

  const BreedingInfoPage({super.key, required this.animalType});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        leading: IconButton(onPressed: (){
          Navigator.pop(context);
        }, icon: Config.backButtonIcon),
        title: Text('Breeding Info'),
      ),
      body: SafeArea(
        child: Center(
          child: Text('No info yet.', style: TextStyle(
            fontFamily: Config.primaryFont,
            fontSize: Config.fontMedium,
            color: Config.tertiaryColor,
          ),),
        ),)
    );
  }
}
