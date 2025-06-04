import 'package:flutter/material.dart';
import 'package:cityvet_app/utils/config.dart';

class ForgotPassView extends StatelessWidget {
  const ForgotPassView({super.key});

  @override
  Widget build(BuildContext context) {
    Config().init(context);
    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          onPressed: (){}, 
          icon: Icon(Icons.arrow_back_ios),
        ),
      ),
      body: SafeArea(
        child: Padding(
          padding: Config.paddingScreen,
          child: Column(
            children: [
              Center(
                child: Image.asset('assets/images/forgot_pass.png', 
                width: 200,
                height: 200,
                ),
              ),
              Config.heightBig,
              Text(
                'Forgot '
              )
            ],
          ),
        ),
      ),
    );
  }
}