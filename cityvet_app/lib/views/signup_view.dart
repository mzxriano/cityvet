import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/utils/config.dart';
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
                      Image.asset('assets/images/logo.png'),
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
                TextField(
                  controller: _firstNameController,
                  focusNode: _firstNameNode,
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: _isFirstNameFocused ? Colors.transparent 
                      : Config.secondaryColor,
                    enabledBorder: Config.enabledBorder,
                    focusedBorder: Config.focusedBorder,
                    contentPadding: Config.paddingTextfield, 
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
                TextField(
                  controller: _lastNameController,
                  focusNode: _lastNameNode,
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: _isLastNameFocudes ? Colors.transparent 
                      : Config.secondaryColor,
                    enabledBorder: Config.enabledBorder,
                    focusedBorder: Config.focusedBorder,
                    contentPadding: Config.paddingTextfield, 
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
                TextField(
                  controller: _phoneNumberController,
                  focusNode: _phoneNumberNode,
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: _isPhoneNumberFocused ? Colors.transparent 
                      : Config.secondaryColor,
                    enabledBorder: Config.enabledBorder,
                    focusedBorder: Config.focusedBorder,
                    contentPadding: Config.paddingTextfield, 
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
                TextField(
                  controller: _emailController,
                  focusNode: _emailNode,
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: _isEmailFocused ? Colors.transparent 
                      : Config.secondaryColor,
                    enabledBorder: Config.enabledBorder,
                    focusedBorder: Config.focusedBorder,
                    contentPadding: Config.paddingTextfield, 
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
                TextField(
                  controller: _passwordController,
                  focusNode: _passwordNode,
                  obscureText: _isPasswordObscured,
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: _isPasswordFocused ? Colors.transparent 
                      : Config.secondaryColor,
                    enabledBorder: Config.enabledBorder, 
                    focusedBorder: Config.focusedBorder,
                    contentPadding: Config.paddingTextfield,
                    suffixIcon: IconButton(
                      padding: const EdgeInsetsDirectional.only(end: 12),
                      onPressed: () {
                        setState(() {
                          _isPasswordObscured = !_isPasswordObscured;
                        });
                      }, 
                      icon: _isPasswordObscured ? const Icon(Icons.visibility)
                        : const Icon(Icons.visibility_off)
                    ),
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
                TextField(
                  controller: _confirmPasswordController,
                  focusNode: _confirmPasswordNode,
                  obscureText: _isConfirmPasswordObscured,
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: _isConfirmPasswordFocused ? Colors.transparent 
                      : Config.secondaryColor,
                    enabledBorder: Config.enabledBorder, 
                    focusedBorder: Config.focusedBorder,
                    contentPadding: Config.paddingTextfield,
                    suffixIcon: IconButton(
                      padding: const EdgeInsetsDirectional.only(end: 12),
                      onPressed: () {
                        setState(() {
                          _isConfirmPasswordObscured = !_isConfirmPasswordObscured;
                        });
                      }, 
                      icon: _isConfirmPasswordObscured ? const Icon(Icons.visibility)
                        : const Icon(Icons.visibility_off)
                    ),
                  ),
                ),
                Config.heightBig,
                Button(
                  width: double.infinity, 
                  title: 'Sign up', 
                  onPressed: (){}
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