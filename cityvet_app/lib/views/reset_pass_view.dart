import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/utils/text.dart';
import 'package:flutter/material.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/services/auth_service.dart';

class ResetPassView extends StatefulWidget {
  final String email;
  final String otp;
  const ResetPassView({super.key, required this.email, required this.otp});

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
  bool _isnewPassObscured = true;
  bool _isconfirmNewPassObscured = true;
  bool _isLoading = false;
  String? _errorMessage;
  String? _successMessage;

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
          onPressed: (){
            Navigator.pop(context);
          }, 
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
                const SizedBox(height: 10,),
                Text(
                  AppText.enText['reset_password_validation']!,
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontXS,
                    color: Config.tertiaryColor,
                  ),
                ),
                Config.heightMedium,
                Button(
                  width: double.infinity, 
                  title: 'Done', 
                  onPressed: () async {
                    final newPassword = _newPassController.text.trim();
                    final confirmPassword = _confirmNewPassController.text.trim();
                    setState(() { _isLoading = true; _errorMessage = null; _successMessage = null; });
                    if (newPassword.isEmpty || confirmPassword.isEmpty) {
                      setState(() { _errorMessage = 'Please fill in all fields.'; _isLoading = false; });
                      return;
                    }
                    if (newPassword != confirmPassword) {
                      setState(() { _errorMessage = 'Passwords do not match.'; _isLoading = false; });
                      return;
                    }
                    try {
                      final response = await AuthService().resetPassword(
                        email: widget.email,
                        otp: widget.otp,
                        password: newPassword,
                        passwordConfirmation: confirmPassword,
                      );
                      if (response.statusCode == 200) {
                        setState(() { _successMessage = 'Password reset successful!'; });
                        Future.delayed(Duration(seconds: 2), () {
                          Navigator.of(context).popUntil((route) => route.isFirst);
                        });
                      } else {
                        setState(() { _errorMessage = response.data['message'] ?? 'Failed to reset password.'; });
                      }
                    } catch (e) {
                      setState(() { _errorMessage = 'Failed to reset password. Please try again.'; });
                    } finally {
                      setState(() { _isLoading = false; });
                    }
                  }
                ),
                if (_isLoading) ...[
                  SizedBox(height: 16),
                  CircularProgressIndicator(),
                ],
                if (_errorMessage != null) ...[
                  SizedBox(height: 16),
                  Text(_errorMessage!, style: TextStyle(color: Colors.red)),
                ],
                if (_successMessage != null) ...[
                  SizedBox(height: 16),
                  Text(_successMessage!, style: TextStyle(color: Colors.green)),
                ],
              ],
            ),
          ),
        )
      ),
    );
  }
}