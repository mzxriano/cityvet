import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/components/text_field.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/signup_view_model.dart';
import 'package:cityvet_app/views/login_view.dart';
import 'package:flutter/material.dart';

class SignupView extends StatefulWidget {
  const SignupView({super.key});

  @override
  State<SignupView> createState() => _SignupViewState();
}

class _SignupViewState extends State<SignupView> {

  final TextEditingController _firstNameController = TextEditingController();
  final TextEditingController _lastNameController = TextEditingController();
  final TextEditingController _phoneNumberController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _confirmPasswordController = TextEditingController();

  final FocusNode _firstNameNode = FocusNode();
  final FocusNode _lastNameNode = FocusNode();
  final FocusNode _phoneNumberNode = FocusNode();
  final FocusNode _emailNode = FocusNode();
  final FocusNode _passwordNode = FocusNode();
  final FocusNode _confirmPasswordNode = FocusNode();

  bool _isFirstNameFocused = false;
  bool _isLastNameFocudes = false;
  bool _isPhoneNumberFocused = false;
  bool _isEmailFocused = false;
  bool _isPasswordFocused = false;
  bool _isConfirmPasswordFocused = false;
  bool _isPasswordObscured = true;
  bool _isConfirmPasswordObscured = true;

  @override
  void initState() {
    super.initState();

    _firstNameNode.addListener(() {
      setState(() {
        _isFirstNameFocused = _firstNameNode.hasFocus;
      });
    });

    _lastNameNode.addListener(() {
      setState(() {
        _isLastNameFocudes = _lastNameNode.hasFocus;
      });
    });

    _phoneNumberNode.addListener(() {
      setState(() {
        _isPhoneNumberFocused = _phoneNumberNode.hasFocus;
      });
    });

    _emailNode.addListener(() {
      setState(() {
        _isEmailFocused = _emailNode.hasFocus;
      });
    });

    _passwordNode.addListener(() {
      setState(() {
        _isPasswordFocused = _passwordNode.hasFocus;
      });
    });

    _confirmPasswordNode.addListener(() {
      setState(() {
        _isConfirmPasswordFocused = _confirmPasswordNode.hasFocus;
      });
    });

  }

  @override
  void dispose() {
    super.dispose();

    _firstNameController.dispose();
    _lastNameController.dispose();
    _phoneNumberController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();

    _firstNameNode.dispose();
    _lastNameNode.dispose();
    _phoneNumberNode.dispose();
    _emailNode.dispose();
    _passwordNode.dispose();
    _confirmPasswordNode.dispose();
  }


  @override
  Widget build(BuildContext context) {

    final signup = SignupViewModel();

    Map<String, String?> _fieldErrors = {
    'firstName': null,
    'lastName': null,
    'phoneNumber': null,
    'email': null,
    'password': null,
    'passwordConfirmation': null,
  };

    Config().init(context);
    return Scaffold(
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
                      Config.primaryLogo,
                      Config.heightSmall,
                      Text(
                        'Sign up',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontBig,
                        ),
                      )
                    ],
                  ),
                ),
                Config.heightMedium,

                // First Name text field
                Text(
                  'First Name',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                  ),
                ),
                CustomTextField(
                  controller: _firstNameController,
                  node: _firstNameNode,
                  isObscured: false,
                  isFocused: _isFirstNameFocused,
                  errorText: _fieldErrors['firstName'],
                ),
                if(_fieldErrors['firstName'] != null)
                  Text(
                    _fieldErrors['firstName'] ?? 'First Name must not be empty',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontXS,
                      color: Colors.red,
                    ),
                  ),

                Config.heightMedium,

                // Last Name text field
                Text(
                  'Last Name',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                  ),
                ),
                CustomTextField(
                  controller: _lastNameController,
                  node: _lastNameNode,
                  isObscured: false,
                  isFocused: _isLastNameFocudes,
                  errorText: _fieldErrors['lastName'],
                ),
                if(_fieldErrors['lastName'] != null)
                  Text(
                    _fieldErrors['lastName'] ?? 'Last Name must not be empty',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontXS,
                      color: Colors.red,
                    ),
                  ),

                Config.heightMedium,

                // Phone Number text field
                Text(
                  'Phone Number',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                  ),
                ),
                CustomTextField(
                  controller: _phoneNumberController,
                  node: _phoneNumberNode,
                  isObscured: false,
                  isFocused: _isPhoneNumberFocused,
                  errorText: _fieldErrors['phoneNumber'],
                ),
                if(_fieldErrors['phoneNumber'] != null)
                  Text(
                    _fieldErrors['phoneNumber'] ?? 'Phone Number must not be empty',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontXS,
                      color: Colors.red,
                    ),
                  ),
                Config.heightMedium,

                // Email text field
                Text(
                  'Email',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                  ),
                ),
                CustomTextField(
                  controller: _emailController,
                  node: _emailNode,
                  isObscured: false,
                  isFocused: _isEmailFocused,
                  errorText: _fieldErrors['email'],
                ),
                if(_fieldErrors['email'] != null)
                  Text(
                    _fieldErrors['email'] ?? 'Email must not be empty',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontXS,
                      color: Colors.red,
                    ),
                  ),
                Config.heightMedium,

                // Password text field
                Text(
                  'Password',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                  ),
                ),
                CustomTextField(
                  controller: _passwordController,
                  node: _passwordNode,
                  isObscured: _isPasswordObscured,
                  isFocused: _isPasswordFocused,
                  errorText: _fieldErrors['password'],
                  suffixIcon: IconButton(
                    padding: const EdgeInsetsDirectional.only(end: 12),
                    onPressed: () {
                      setState(() {
                        _isPasswordObscured = !_isPasswordObscured;
                      });
                    },
                    icon: Icon(_isPasswordObscured ? Icons.visibility : Icons.visibility_off),
                  ),
                ),
                if(_fieldErrors['password'] != null)
                  Text(
                    _fieldErrors['password'] ?? 'Password must not be empty',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontXS,
                      color: Colors.red,
                    ),
                  ),
                Config.heightMedium,

                // Confirm Password text field
                Text(
                  'Confirm Password',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                  ),
                ),
                CustomTextField(
                  controller: _confirmPasswordController,
                  node: _confirmPasswordNode,
                  isObscured: _isConfirmPasswordObscured,
                  isFocused: _isConfirmPasswordFocused,
                  errorText: _fieldErrors['passwordConfirmation'],
                  suffixIcon: IconButton(
                    padding: const EdgeInsetsDirectional.only(end: 12),
                    onPressed: () {
                      setState(() {
                        _isConfirmPasswordObscured = !_isConfirmPasswordObscured;
                      });
                    },
                    icon: Icon(_isConfirmPasswordObscured ? Icons.visibility : Icons.visibility_off),
                  ),
                ),
                if(_fieldErrors['passwordConfirmation'] != null)
                  Text(
                    _fieldErrors['passwordConfirmation'] ?? 'Confirm Password must not be empty',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontXS,
                      color: Colors.red,
                    ),
                  ),
                Config.heightBig,
                Button(
                  width: double.infinity, 
                  title: 'Sign up', 
                  onPressed: () async {
                    final message = await signup.register(
                      firstName: _firstNameController.text, 
                      lastName: _lastNameController.text, 
                      phoneNumber: _phoneNumberController.text, 
                      email: _emailController.text, 
                      password: _passwordController.text, 
                      passwordConfirmation: _confirmPasswordController.text);
                  }
                ),
                Config.heightMedium,
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    Text(
                      'Already have an account?',
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontSmall,
                      ),
                    ),
                    TextButton(
                      onPressed: (){
                        Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (context) => LoginView() ));
                      }, 
                      child: Text(
                        'Login',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
                          color: Config.primaryColor
                        ),
                      )
                    ),
                  ],
                )
              ],
            ),
          ),
        )
      ),
    );
  }
}