import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/utils/text.dart';
import 'package:cityvet_app/views/otp_verification_view.dart';
import 'package:flutter/material.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/services/auth_service.dart';

class ForgotPassView extends StatefulWidget {
  const ForgotPassView({super.key});

  @override
  State<ForgotPassView> createState() => _ForgotPassViewState();
}

class _ForgotPassViewState extends State<ForgotPassView> {

  final TextEditingController _forgotPassController = TextEditingController();
  final FocusNode _forgotPassNode = FocusNode();
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>(); 
  bool _isForgotPassFocused = false;
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _forgotPassNode.addListener(() {
      setState(() {
        _isForgotPassFocused = _forgotPassNode.hasFocus;
      });
    });
  }

  // Email or Phone validation function
  String? _validateInput(String? value) {
    if (value == null || value.isEmpty) {
      return 'Please enter your email.';
    }
    // Regex to validate email
    final emailRegExp = RegExp(r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$');
    if (emailRegExp.hasMatch(value)) {
      return null;
    }
    // Regex to validate phone number
    final phoneRegExp = RegExp(r'^\d{10}$');
    if (phoneRegExp.hasMatch(value)) {
      return null;
    }
    return 'Please enter a valid email.';
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
            child: Form(
              key: _formKey, 
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
                  TextFormField(
                    controller: _forgotPassController,
                    focusNode: _forgotPassNode,
                    decoration: InputDecoration(
                      hintText: 'Enter your Email',
                      filled: true,
                      fillColor: _isForgotPassFocused ? Colors.transparent 
                        : Config.secondaryColor,
                      enabledBorder: Config.enabledBorder,
                      focusedBorder: Config.focusedBorder,
                      errorBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                        borderSide: BorderSide(color: Colors.red, width: 2),
                      ),
                      focusedErrorBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                        borderSide: BorderSide(color: Colors.red, width: 2),
                      ),
                      contentPadding: Config.paddingTextfield, 
                      errorStyle: TextStyle(
                        fontSize: 12,
                        color: Colors.red,
                        fontWeight: FontWeight.bold,
                      ),
                      prefixIcon: Icon(
                        Icons.mail,
                        color: Colors.grey,
                      ),
                    ),
                    validator: _validateInput, 
                  ),
                  Config.heightMedium,
                  Button(
                    width: double.infinity, 
                    title: 'Send Code', 
                    onPressed: () async {
                      if (_formKey.currentState?.validate() ?? false) {
                        setState(() { _isLoading = true; _errorMessage = null; });
                        try {
                          final response = await AuthService().forgotPassword(_forgotPassController.text.trim());
                          if (response.statusCode == 200) {
                            Navigator.of(context).push(MaterialPageRoute(
                              builder: (context) => OtpVerificationView(email: _forgotPassController.text.trim()),
                            ));
                          } else {
                            setState(() { _errorMessage = response.data['message'] ?? 'Failed to send OTP.'; });
                          }
                        } catch (e) {
                          setState(() { _errorMessage = 'Failed to send OTP. Please try again.'; });
                        } finally {
                          setState(() { _isLoading = false; });
                        }
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
        ),
      ),
    );
  }
}
