import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/utils/text.dart';
import 'package:flutter/material.dart';
import 'package:cityvet_app/utils/config.dart';

class ResetPassView extends StatefulWidget {
  const ResetPassView({super.key});

  @override
  State<ResetPassView> createState() => _ResetPassViewState();
}

class _ResetPassViewState extends State<ResetPassView> {

  final TextEditingController _newPassController = TextEditingController();
  final TextEditingController _confirmNewPassController = TextEditingController();

  final FocusNode _newPassNode = FocusNode();
  final FocusNode _confirmNewPassNode = FocusNode();

  bool _isnewPassFocused = false;
  bool _isconfirmNewPassFocused = false;
  bool _isnewPassObscured = false;
  bool _isconfirmNewPassObscured = false;

  @override
  void initState() {
    super.initState();
    _newPassNode.addListener(() {
      setState(() {
        _isnewPassFocused = _newPassNode.hasFocus;
      });
    });

    _confirmNewPassNode.addListener(() {
      setState(() {
        _isconfirmNewPassFocused = _confirmNewPassNode.hasFocus;
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);
    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          onPressed: (){}, 
          icon: Icon(Icons.arrow_back_ios_new_rounded),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          child: Padding(
          padding: Config.paddingScreen,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Align(
                  alignment: Alignment.center,
                  child: Column(
                    children: [
                      Image.asset('assets/images/reset_pass.png', 
                        width: 200,
                        height: 200,
                      ),
                      Config.heightSmall,
                      Text(
                        'Reset Password',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontBig,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      Config.heightSmall,
                      Text(
                        AppText.enText['reset_password']!,
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
                          color: Config.tertiaryColor,
                        ),
                      ),
                    ],
                  ),
                ),
                Config.heightBig,
                Text(
                  'New Password',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium
                  ),
                ),
                TextField(
                  controller: _newPassController,
                  focusNode: _newPassNode,
                  obscureText: _isnewPassObscured,
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: _isnewPassFocused ? Colors.transparent 
                      : Config.secondaryColor,
                    enabledBorder: Config.enabledBorder, 
                    focusedBorder: Config.focusedBorder,
                    contentPadding: Config.paddingTextfield,
                    suffixIcon: IconButton(
                      padding: const EdgeInsetsDirectional.only(end: 12),
                      onPressed: () {
                        setState(() {
                          _isnewPassObscured = !_isnewPassObscured;
                        });
                      }, 
                      icon: _isnewPassObscured ? const Icon(Icons.visibility)
                        : const Icon(Icons.visibility_off)
                    ),
                  ),
                ),
                Config.heightMedium,
                Text(
                  'Confirm New Password',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium
                  ),
                ),
                TextField(
                  controller: _confirmNewPassController,
                  focusNode: _confirmNewPassNode,
                  obscureText: _isconfirmNewPassObscured,
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: _isconfirmNewPassFocused ? Colors.transparent 
                      : Config.secondaryColor,
                    enabledBorder: Config.enabledBorder, 
                    focusedBorder: Config.focusedBorder,
                    contentPadding: Config.paddingTextfield,
                    suffixIcon: IconButton(
                      padding: const EdgeInsetsDirectional.only(end: 12),
                      onPressed: () {
                        setState(() {
                          _isconfirmNewPassObscured = !_isconfirmNewPassObscured;
                        });
                      }, 
                      icon: _isconfirmNewPassObscured ? const Icon(Icons.visibility)
                        : const Icon(Icons.visibility_off)
                    ),
                  ),
                ),
                Text(
                  AppText.enText['reset_password_validation']!,
                ),
                Config.heightMedium,
                Button(
                  width: double.infinity, 
                  title: 'Done', 
                  onPressed: (){}
                ),
              ],
            ),
          ),
        )
      ),
    );
  }
}