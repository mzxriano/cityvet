import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class AnimalPreview extends StatelessWidget {
  const AnimalPreview({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          onPressed: (){
            Navigator.pop(context);
          }, 
        icon: Config.backButtonIcon
        ),
      ),
      body: Padding(
        padding: Config.paddingScreen,
        child: Center(
          child: Column(
            children: [
              Text('Animal Preview')
            ],
          ),
        ),
      ),
    );
  }
}