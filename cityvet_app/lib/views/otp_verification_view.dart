import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/utils/text.dart';
import 'package:cityvet_app/views/reset_pass_view.dart';
import 'package:flutter/material.dart';
import 'package:cityvet_app/services/auth_service.dart';

class OtpVerificationView extends StatefulWidget {
  final String email;
  const OtpVerificationView({super.key, required this.email});

  @override
  State<OtpVerificationView> createState() => _OtpVerificationViewState();
}

class _OtpVerificationViewState extends State<OtpVerificationView> {
  final TextEditingController _otpController = TextEditingController();
  bool _isLoading = false;
  String? _errorMessage;

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
                children: [
                  SizedBox(
                    width: 200,
                    child: TextField(
                      controller: _otpController,
                      keyboardType: TextInputType.number,
                      maxLength: 6,
                      decoration: InputDecoration(
                        labelText: 'Enter OTP',
                        border: OutlineInputBorder(),
                        counterText: '',
                      ),
                    ),
                  ),
                ],
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
                onPressed: () async {
                  final otp = _otpController.text.trim();
                  if (otp.length != 6) {
                    setState(() { _errorMessage = 'Please enter the 6-digit OTP.'; });
                    return;
                  }
                  setState(() { _isLoading = true; _errorMessage = null; });
                  try {
                    final response = await AuthService().verifyOtp(email: widget.email, otp: otp);
                    if (response.statusCode == 200) {
                      Navigator.of(context).push(MaterialPageRoute(
                        builder: (context) => ResetPassView(email: widget.email, otp: otp),
                      ));
                    } else {
                      setState(() { _errorMessage = response.data['message'] ?? 'Invalid OTP.'; });
                    }
                  } catch (e) {
                    setState(() { _errorMessage = 'Failed to verify OTP. Please try again.'; });
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
            ],
          ),
        ),
      ),
    );
  }
}