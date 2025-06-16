import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/utils/text.dart';
import 'package:cityvet_app/views/reset_pass_view.dart';
import 'package:flutter/material.dart';

class OtpVerificationView extends StatefulWidget {
  const OtpVerificationView({super.key});

  @override
  State<OtpVerificationView> createState() => _OtpVerificationViewState();
}

class _OtpVerificationViewState extends State<OtpVerificationView> {
  @override
  Widget build(BuildContext context) {
    Config().init(context);
    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          onPressed: (){
            Navigator.pop(context);
          }, 
          icon: Config.backButtonIcon,
        ),
      ),
      body: SafeArea(
        child: Padding(
          padding: Config.paddingScreen,
          child: Column(
            children: [
              Align(
                child: Column(
                  children: [
                    Config.primaryLogo,
                    Config.heightSmall,
                    Text(
                      'OTP Verification',
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontBig,
                      ),
                    ),
                    Config.heightSmall,
                    Text(
                      AppText.enText['otp_verification']!,
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontSmall,
                        color: Config.tertiaryColor,
                      ),
                    ),
                  ],
                ),
              ),
              Config.heightMedium,
              Wrap(
                spacing: 12,
                children: List.generate(4, (_) => SizedBox(
                  width: 60,
                  child: _buildOtpField(),
                )),
              ),
              Config.heightMedium,
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    'Did\'t get the code?',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontSmall,
                      color: Config.tertiaryColor,
                    ),
                  ),
                  TextButton(
                    onPressed: (){
                    }, 
                    child: Text(
                      'Resend',
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontSmall,
                        color: Config.primaryColor,
                      ),
                    ),
                  ),
                ],
              ),
              Config.heightMedium,
              Button(
                width: double.infinity, 
                title: 'Verify', 
                onPressed: (){
                  Navigator.of(context).push(MaterialPageRoute(builder: (context) => ResetPassView() ));
                }
              ),
            ],
          ),
        ),
      ),
    );
  }
}

Widget _buildOtpField() {
  return TextField(
    keyboardType: TextInputType.number,
    decoration: InputDecoration(
      border: OutlineInputBorder(
        borderSide: BorderSide(
          width: 1,
          color: Config.primaryColor,
        ),
        borderRadius: BorderRadius.zero,
      ),
      enabledBorder: OutlineInputBorder(
        borderSide: BorderSide(
          width: 1,
          color: Config.primaryColor,
        ),
        borderRadius: BorderRadius.zero,
      ),
      focusedBorder: OutlineInputBorder(
        borderSide: BorderSide(
          width: 1,
          color: Config.primaryColor,
        ),
        borderRadius: BorderRadius.zero,
      ),
    ),
  );
}