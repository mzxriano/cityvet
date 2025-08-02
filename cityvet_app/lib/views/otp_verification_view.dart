import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/views/reset_pass_view.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:cityvet_app/services/auth_service.dart';

class OtpVerificationView extends StatefulWidget {
  final String email;
  const OtpVerificationView({super.key, required this.email});

  @override
  State<OtpVerificationView> createState() => _OtpVerificationViewState();
}

class _OtpVerificationViewState extends State<OtpVerificationView> {
  final List<TextEditingController> _otpControllers = List.generate(4, (index) => TextEditingController());
  final List<FocusNode> _focusNodes = List.generate(4, (index) => FocusNode());
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void dispose() {
    for (var controller in _otpControllers) {
      controller.dispose();
    }
    for (var focusNode in _focusNodes) {
      focusNode.dispose();
    }
    super.dispose();
  }

  String get _otpCode {
    return _otpControllers.map((controller) => controller.text).join();
  }

  void _onOtpChanged(String value, int index) {
    if (value.isNotEmpty && index < 3) {
      _focusNodes[index + 1].requestFocus();
    } else if (value.isEmpty && index > 0) {
      _focusNodes[index - 1].requestFocus();
    }
    setState(() {
      _errorMessage = null;
    });
  }

  Future<void> _verifyOtp() async {
    final otp = _otpCode;
    if (otp.length != 4) {
      setState(() { 
        _errorMessage = 'Please enter the complete 6-digit OTP.'; 
      });
      return;
    }
    
    setState(() { 
      _isLoading = true; 
      _errorMessage = null; 
    });
    
    try {
      final response = await AuthService().verifyOtp(
        email: widget.email, 
        otp: otp
      );
      
      if (response.statusCode == 200) {
        Navigator.of(context).push(MaterialPageRoute(
          builder: (context) => ResetPassView(
            email: widget.email, 
            otp: otp
          ),
        ));
      } else {
        setState(() { 
          _errorMessage = response.data['message'] ?? 'Invalid OTP. Please try again.'; 
        });
      }
    } catch (e) {
      setState(() { 
        _errorMessage = 'Failed to verify OTP. Please check your connection and try again.'; 
      });
    } finally {
      setState(() { 
        _isLoading = false; 
      });
    }
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
        backgroundColor: Colors.transparent,
        elevation: 0,
      ),
      body: SafeArea(
        child: Padding(
          padding: Config.paddingScreen,
          child: Column(
            children: [
              Expanded(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // Logo
                    Config.primaryLogo,
                    Config.heightMedium,
                    
                    // Title
                    Text(
                      'OTP Verification',
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontBig,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    Config.heightSmall,
                    
                    // Subtitle
                    Text(
                      'Please enter the code we just sent to email',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontSmall,
                        color: Config.tertiaryColor,
                      ),
                    ),
                    Config.heightBig,
                    
                    // OTP Input Boxes
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                      children: List.generate(4, (index) {
                        return Container(
                          width: 60,
                          height: 60,
                          decoration: BoxDecoration(
                            border: Border.all(
                              color: _otpControllers[index].text.isNotEmpty 
                                ? Config.primaryColor 
                                : Colors.grey.shade300,
                              width: 2,
                            ),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: TextField(
                            controller: _otpControllers[index],
                            focusNode: _focusNodes[index],
                            keyboardType: TextInputType.number,
                            textAlign: TextAlign.center,
                            maxLength: 1,
                            style: TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              fontFamily: Config.primaryFont,
                            ),
                            inputFormatters: [
                              FilteringTextInputFormatter.digitsOnly,
                            ],
                            decoration: InputDecoration(
                              border: InputBorder.none,
                              counterText: '',
                              contentPadding: EdgeInsets.zero,
                            ),
                            onChanged: (value) => _onOtpChanged(value, index),
                            onTap: () {
                              _otpControllers[index].selection = TextSelection.fromPosition(
                                TextPosition(offset: _otpControllers[index].text.length),
                              );
                            },
                            onSubmitted: (value) {
                              if (index < 3 && value.isNotEmpty) {
                                _focusNodes[index + 1].requestFocus();
                              }
                            },
                          ),
                        );
                      }),
                    ),
                    Config.heightBig,
                    
                    // Resend Code
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          'Didn\'t get a code? ',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontSmall,
                            color: Config.tertiaryColor,
                          ),
                        ),
                        TextButton(
                          onPressed: () async {
                            // Add resend functionality here
                            try {
                              // Call your resend OTP service
                              // await AuthService().resendOtp(email: widget.email);
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text('OTP resent successfully')),
                              );
                            } catch (e) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text('Failed to resend OTP')),
                              );
                            }
                          }, 
                          style: TextButton.styleFrom(
                            padding: EdgeInsets.zero,
                            minimumSize: Size.zero,
                            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          ),
                          child: Text(
                            'Resend',
                            style: TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: Config.fontSmall,
                              color: Config.primaryColor,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              
              // Error Message
              if (_errorMessage != null) ...[
                Container(
                  width: double.infinity,
                  padding: EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                  margin: EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.red.shade200),
                  ),
                  child: Text(
                    _errorMessage!,
                    style: TextStyle(
                      color: Colors.red.shade700,
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontSmall,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
              ],
              
              // Verify Button
              Button(
                width: double.infinity, 
                title: _isLoading ? 'Verifying...' : 'Verify', 
                onPressed: () {
                  _verifyOtp();
                }
              ),
              
              // Loading Indicator
              if (_isLoading) ...[
                SizedBox(height: 16),
                CircularProgressIndicator(
                  valueColor: AlwaysStoppedAnimation<Color>(Config.primaryColor),
                ),
              ],
              
              SizedBox(height: 32), // Bottom padding
            ],
          ),
        ),
      ),
    );
  }
}