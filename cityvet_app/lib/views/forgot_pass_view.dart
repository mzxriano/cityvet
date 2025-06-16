import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/utils/text.dart';
import 'package:cityvet_app/views/otp_verification_view.dart';
import 'package:flutter/material.dart';
import 'package:cityvet_app/utils/config.dart';

class ForgotPassView extends StatefulWidget {
  const ForgotPassView({super.key});

  @override
  State<ForgotPassView> createState() => _ForgotPassViewState();
}

class _ForgotPassViewState extends State<ForgotPassView> {

  final TextEditingController _forgotPassController = TextEditingController();
  final FocusNode _forgotPassNode = FocusNode();
  bool _isForgotPassFocused = false;

  @override
  void initState() {
    super.initState();
    _forgotPassNode.addListener(() {
      setState(() {
        _isForgotPassFocused = _forgotPassNode.hasFocus;
      });
    });
  }

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
        child: SingleChildScrollView(
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
                  'Forgot Password?',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontBig,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                Config.heightSmall,
                Text(
                  AppText.enText['forgot_password']!,
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontSmall,
                    color: Config.tertiaryColor,
                  ),
                ),
                Config.heightMedium,
                TextField(
                  controller: _forgotPassController,
                  focusNode: _forgotPassNode,
                  decoration: InputDecoration(
                    hintText: 'Enter your Email/Phone number',
                    filled: true,
                    fillColor: _isForgotPassFocused ? Colors.transparent 
                      : Config.secondaryColor,
                    enabledBorder: Config.enabledBorder,
                    focusedBorder: Config.focusedBorder,
                    contentPadding: Config.paddingTextfield, 
                  ),
                ),
                Config.heightMedium,
                Button(
                  width: double.infinity, 
                  title: 'Send Code', 
                  onPressed: (){
                    Navigator.of(context).push(MaterialPageRoute(builder: (context) => OtpVerificationView()));
                  }
                ),
              ],
            ),
          ),
        )
      ),
    );
  }
}