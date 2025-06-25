import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/components/text_field.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/utils/text.dart';
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
  final TextEditingController _bDateController = TextEditingController();
  final TextEditingController _phoneNumberController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _streetController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _confirmPasswordController = TextEditingController();

  final FocusNode _firstNameNode = FocusNode();
  final FocusNode _lastNameNode = FocusNode();
  final FocusNode _bDateNode = FocusNode();
  final FocusNode _phoneNumberNode = FocusNode();
  final FocusNode _emailNode = FocusNode();
  final FocusNode _streetNode = FocusNode();
  final FocusNode _passwordNode = FocusNode();
  final FocusNode _confirmPasswordNode = FocusNode();

  bool _isFirstNameFocused = false;
  bool _isLastNameFocused = false;
  bool _isbDateFocused = false;
  bool _isPhoneNumberFocused = false;
  bool _isEmailFocused = false;
  bool _isStreetFocused = false;
  bool _isPasswordFocused = false;
  bool _isConfirmPasswordFocused = false;
  bool _isPasswordObscured = true;
  bool _isConfirmPasswordObscured = true;

  bool _isLoading = false;

  final signup = SignupViewModel();

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
        _isLastNameFocused = _lastNameNode.hasFocus;
      });
    });

    _bDateNode.addListener(() {
      setState(() {
        _isbDateFocused = _bDateNode.hasFocus;
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

    _streetNode.addListener(() {
      setState(() {
        _isStreetFocused = _streetNode.hasFocus;
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
    _firstNameController.dispose();
    _lastNameController.dispose();
    _bDateController.dispose();
    _phoneNumberController.dispose();
    _emailController.dispose();
    _streetController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();

    _firstNameNode.dispose();
    _lastNameNode.dispose();
    _bDateNode.dispose();
    _phoneNumberNode.dispose();
    _emailNode.dispose();
    _streetNode.dispose();
    _passwordNode.dispose();
    _confirmPasswordNode.dispose();

    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    return Scaffold(
      body: Stack(
        children: [
          SafeArea(
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

                    // First Name
                    Text('First Name', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
                    ValueListenableBuilder<Map<String, String?>>(
                      valueListenable: signup.fieldErrors,
                      builder: (context, fieldErrors, _) {
                        return CustomTextField(
                          controller: _firstNameController,
                          node: _firstNameNode,
                          textInputType: TextInputType.name,
                          isObscured: false,
                          isFocused: _isFirstNameFocused,
                          errorText: fieldErrors['first_name'],
                        );
                      },
                    ),
                    Config.heightMedium,

                    // Last Name
                    Text('Last Name', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
                    ValueListenableBuilder<Map<String, String?>>(
                      valueListenable: signup.fieldErrors,
                      builder: (context, fieldErrors, _) {
                        return CustomTextField(
                          controller: _lastNameController,
                          node: _lastNameNode,
                          textInputType: TextInputType.name,
                          isObscured: false,
                          isFocused: _isLastNameFocused,
                          errorText: fieldErrors['last_name'],
                        );
                      },
                    ),
                    Config.heightMedium,

                    // Age
                    Text('Birth Date', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
                    ValueListenableBuilder<Map<String, String?>>(
                      valueListenable: signup.fieldErrors,
                      builder: (context, fieldErrors, _) {
                        return CustomTextField(
                          controller: _bDateController,
                          node: _bDateNode,
                          textInputType: TextInputType.datetime,
                          isObscured: false,
                          isFocused: _isbDateFocused,
                          errorText: fieldErrors['birth_date'],
                        );
                      },
                    ),
                    Config.heightMedium,

                    // Phone Number
                    Text('Phone Number', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
                    ValueListenableBuilder<Map<String, String?>>(
                      valueListenable: signup.fieldErrors,
                      builder: (context, fieldErrors, _) {
                        return CustomTextField(
                          controller: _phoneNumberController,
                          node: _phoneNumberNode,
                          textInputType: TextInputType.phone,
                          isObscured: false,
                          isFocused: _isPhoneNumberFocused,
                          errorText: fieldErrors['phone_number'],
                        );
                      },
                    ),
                    Config.heightMedium,

                    // Email
                    Text('Email', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
                    ValueListenableBuilder<Map<String, String?>>(
                      valueListenable: signup.fieldErrors,
                      builder: (context, fieldErrors, _) {
                        return CustomTextField(
                          controller: _emailController,
                          node: _emailNode,
                          textInputType: TextInputType.emailAddress,
                          isObscured: false,
                          isFocused: _isEmailFocused,
                          errorText: fieldErrors['email'],
                        );
                      },
                    ),
                    Config.heightMedium,

                    Text('Barangay', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
                    ValueListenableBuilder<String?>(
                      valueListenable: signup.selectedBarangay,
                      builder: (context, selectedBarangay, child) {
                        final hasError = signup.fieldErrors.value['barangay'] != null;
                        final errorText = signup.fieldErrors.value['barangay'];

                        return Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            DropdownButtonHideUnderline(
                              child: DropdownButtonFormField<String>(
                                decoration: InputDecoration(
                                  filled: true,
                                  fillColor: Config.secondaryColor,
                                  contentPadding: Config.paddingTextfield,
                                  border: const OutlineInputBorder(
                                    borderRadius: BorderRadius.all(Radius.circular(10)),
                                  ),
                                  enabledBorder: OutlineInputBorder(
                                    borderSide: BorderSide(
                                      color: hasError ? Colors.red : Colors.transparent,
                                    ),
                                    borderRadius: const BorderRadius.all(Radius.circular(10)),
                                  ),
                                  focusedBorder: OutlineInputBorder(
                                    borderSide: BorderSide(
                                      color: hasError ? Colors.red : Config.primaryColor,
                                      width: 2,
                                    ),
                                    borderRadius: const BorderRadius.all(Radius.circular(10)),
                                  ),
                                ),
                                value: selectedBarangay,
                                items: AppText.barangay.map((String barangay) {
                                  return DropdownMenuItem<String>(
                                    value: barangay,
                                    child: Text(barangay ,style: TextStyle(fontFamily: Config.primaryFont),),
                                  );
                                }).toList(),
                                onChanged: (value) {
                                  signup.selectedBarangay.value = value;
                                },
                              ),
                            ),
                            if (hasError) ...[
                              const SizedBox(height: 4),
                              Text(
                                errorText!,
                                style: const TextStyle(color: Colors.red, fontSize: 12),
                              ),
                            ]
                          ],
                        );
                      },
                    ),
                    Config.heightMedium,

                    ValueListenableBuilder<String?>(
                      valueListenable: signup.selectedBarangay,
                      builder: (context, selectedBarangay, child) {
                        if (selectedBarangay != null && selectedBarangay.isNotEmpty) {
                          return Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Street',
                                style: TextStyle(
                                  fontFamily: Config.primaryFont,
                                  fontSize: Config.fontMedium,
                                ),
                              ),
                              ValueListenableBuilder<Map<String, String?>>(
                                valueListenable: signup.fieldErrors,
                                builder: (context, fieldErrors, _) {
                                  return CustomTextField(
                                    controller: _streetController, 
                                    node: _streetNode,
                                    textInputType: TextInputType.streetAddress,
                                    isObscured: false,
                                    isFocused: _isStreetFocused,
                                    errorText: fieldErrors['street'],
                                  );
                                },
                              ),
                              Config.heightMedium,
                            ],
                          );
                        }
                        return const SizedBox.shrink(); 
                      },
                    ),

                    // Password
                    Text('Password', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
                    ValueListenableBuilder<Map<String, String?>>(
                      valueListenable: signup.fieldErrors,
                      builder: (context, fieldErrors, _) {
                        return CustomTextField(
                          controller: _passwordController,
                          node: _passwordNode,
                          textInputType: TextInputType.text,
                          isObscured: _isPasswordObscured,
                          isFocused: _isPasswordFocused,
                          errorText: fieldErrors['password'],
                          suffixIcon: IconButton(
                            padding: const EdgeInsetsDirectional.only(end: 12),
                            onPressed: () {
                              setState(() {
                                _isPasswordObscured = !_isPasswordObscured;
                              });
                            },
                            icon: Icon(_isPasswordObscured ? Icons.visibility : Icons.visibility_off),
                          ),
                        );
                      },
                    ),
                    Config.heightMedium,

                    // Confirm Password
                    Text('Confirm Password', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
                    ValueListenableBuilder<Map<String, String?>>(
                      valueListenable: signup.fieldErrors,
                      builder: (context, fieldErrors, _) {
                        return CustomTextField(
                          controller: _confirmPasswordController,
                          node: _confirmPasswordNode,
                          textInputType: TextInputType.text,
                          isObscured: _isConfirmPasswordObscured,
                          isFocused: _isConfirmPasswordFocused,
                          errorText: fieldErrors['password_confirmation'],
                          suffixIcon: IconButton(
                            padding: const EdgeInsetsDirectional.only(end: 12),
                            onPressed: () {
                              setState(() {
                                _isConfirmPasswordObscured = !_isConfirmPasswordObscured;
                              });
                            },
                            icon: Icon(_isConfirmPasswordObscured ? Icons.visibility : Icons.visibility_off),
                          ),
                        );
                      },
                    ),
                    Config.heightBig,

                    // Sign Up Button
                    Button(
                      width: double.infinity,
                      title: 'Sign up',
                      onPressed: () async {

                        setState(() {
                          _isLoading = true;
                        });

                        signup.fieldErrors.value.clear();

                        final message = await signup.register(
                          firstName: _firstNameController.text,
                          lastName: _lastNameController.text,
                          birthDate: _bDateController.text,
                          phoneNumber: _phoneNumberController.text,
                          email: _emailController.text,
                          password: _passwordController.text,
                          passwordConfirmation: _confirmPasswordController.text,
                        );

                        setState(() {
                          _isLoading = false;
                        });

                        print(signup.fieldErrors.value.isNotEmpty);

                        if (signup.fieldErrors.value.isNotEmpty) return;

                        if (message != null) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text(message)),
                          );
                          Navigator.pushReplacement(context, MaterialPageRoute(builder: (context) => LoginView()));
                        }
                      },
                    ),
                    Config.heightMedium,

                    // Footer
                    Row(
                      mainAxisAlignment: MainAxisAlignment.end,
                      children: [
                        Text(
                          'Already have an account?',
                          style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontSmall),
                        ),
                        TextButton(
                          onPressed: () {
                            Navigator.of(context).pushReplacement(
                              MaterialPageRoute(builder: (context) => LoginView()),
                            );
                          },
                          child: Text(
                            'Login',
                            style: TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: Config.fontSmall,
                              color: Config.primaryColor,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),

          if (_isLoading)
            Container(
              color: Colors.black.withValues(alpha: 0.5, red: 0, blue: 0, green: 0),
              child: const Center(
                child: CircularProgressIndicator(color: Color(0xFFDDDDDD)),
              ),
            ),
        ],
      ),
    );
  }
}
