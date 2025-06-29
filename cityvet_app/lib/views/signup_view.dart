import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/components/label_text.dart';
import 'package:cityvet_app/components/text_field.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/utils/text.dart';
import 'package:cityvet_app/viewmodels/signup_view_model.dart';
import 'package:cityvet_app/views/login_view.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

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
  final TextEditingController _streetController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _confirmPasswordController = TextEditingController();

  final FocusNode _firstNameNode = FocusNode();
  final FocusNode _lastNameNode = FocusNode();
  final FocusNode _phoneNumberNode = FocusNode();
  final FocusNode _emailNode = FocusNode();
  final FocusNode _streetNode = FocusNode();
  final FocusNode _passwordNode = FocusNode();
  final FocusNode _confirmPasswordNode = FocusNode();

  bool _isFirstNameFocused = false;
  bool _isLastNameFocused = false;
  bool _isPhoneNumberFocused = false;
  bool _isEmailFocused = false;
  bool _isStreetFocused = false;
  bool _isPasswordFocused = false;
  bool _isConfirmPasswordFocused = false;

  bool _isLoading = false;

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
    _phoneNumberController.dispose();
    _emailController.dispose();
    _streetController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();

    _firstNameNode.dispose();
    _lastNameNode.dispose();
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

    return ChangeNotifierProvider<SignupViewModel>(
      create: (_) => SignupViewModel(),
      child: Consumer<SignupViewModel>(
        builder: (context, signup, _) {
          return Scaffold(
            body: Stack(
              children: [
                SafeArea(
                  child: SingleChildScrollView(
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
                              ),
                            ],
                          ),
                        ),
                        Config.heightMedium,

                        // First Name
                        LabelText(label: 'First Name ', isRequired: true),
                        CustomTextField(
                          controller: _firstNameController,
                          node: _firstNameNode,
                          textInputType: TextInputType.name,
                          isObscured: false,
                          isFocused: _isFirstNameFocused,
                          errorText: signup.getFieldError('first_name'),
                        ),
                        Config.heightMedium,

                        // Last Name
                        LabelText(label: 'Last Name ', isRequired: true),
                        CustomTextField(
                          controller: _lastNameController,
                          node: _lastNameNode,
                          textInputType: TextInputType.name,
                          isObscured: false,
                          isFocused: _isLastNameFocused,
                          errorText: signup.getFieldError('last_name'),
                        ),
                        Config.heightMedium,

                        // Birth Date
                        LabelText(label: 'Birth Date ', isRequired: true),
                        InkWell(
                          onTap: () async {
                            final date = await showDatePicker(
                              context: context,
                              initialDate: DateTime.now(),
                              firstDate: DateTime(1950),
                              lastDate: DateTime.now(),
                            );
                            if (date != null) {    
                              signup.setBirthDate(date);
                            }
                          },
                          child: InputDecorator(
                            decoration: InputDecoration(labelText: '', errorText: signup.getFieldError('birth_date')),
                            child: Text(
                              signup.selectedDate != null
                                  ? '${signup.selectedDate!.month}/${signup.selectedDate!.day}/${signup.selectedDate!.year}'
                                  : 'Select Date',
                              style: TextStyle(
                                color: signup.selectedDate != null ? Colors.black : Colors.grey,
                              ),
                            ),
                          ),
                        ),
                        Config.heightMedium,

                        // Phone Number
                        LabelText(label: 'Phone Number ', isRequired: true),
                        CustomTextField(
                          controller: _phoneNumberController,
                          node: _phoneNumberNode,
                          textInputType: TextInputType.phone,
                          isObscured: false,
                          isFocused: _isPhoneNumberFocused,
                          errorText: signup.getFieldError('phone_number'),
                        ),
                        Config.heightMedium,

                        // Email
                        LabelText(label: 'Email ', isRequired: true),
                        CustomTextField(
                          controller: _emailController,
                          node: _emailNode,
                          textInputType: TextInputType.emailAddress,
                          isObscured: false,
                          isFocused: _isEmailFocused,
                          errorText: signup.getFieldError('email'),
                        ),
                        Config.heightMedium,

                        // Barangay Dropdown
                        LabelText(label: 'Barangay ', isRequired: true),
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
                                  color: signup.getFieldError('barangay') != null ? Colors.red : Colors.transparent,
                                ),
                                borderRadius: const BorderRadius.all(Radius.circular(10)),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderSide: BorderSide(
                                  color: signup.getFieldError('barangay') != null ? Colors.red : Config.primaryColor,
                                  width: 2,
                                ),
                                borderRadius: const BorderRadius.all(Radius.circular(10)),
                              ),
                            ),
                            value: signup.selectedBarangay,
                            items: AppText.barangay.map((String barangay) {
                              return DropdownMenuItem<String>(
                                value: barangay,
                                child: Text(barangay, style: TextStyle(fontFamily: Config.primaryFont)),
                              );
                            }).toList(),
                            onChanged: (value) {
                              signup.setBarangay(value);
                            },
                          ),
                        ),
                        if (signup.getFieldError('barangay') != null) ...[
                          const SizedBox(height: 4),
                          Text(
                            signup.getFieldError('barangay').toString(),
                            style: const TextStyle(color: Colors.red, fontSize: 12),
                          ),
                        ],
                        Config.heightMedium,

                        // Street (only if barangay selected)
                        if (signup.selectedBarangay != null && signup.selectedBarangay!.isNotEmpty) ...[
                          LabelText(label: 'Street ', isRequired: true),
                          CustomTextField(
                            controller: _streetController,
                            node: _streetNode,
                            textInputType: TextInputType.streetAddress,
                            isObscured: false,
                            isFocused: _isStreetFocused,
                            errorText: signup.getFieldError('street'),
                          ),
                          Config.heightMedium,
                        ],

                        // Password
                        LabelText(label: 'Password ', isRequired: true),
                        CustomTextField(
                          controller: _passwordController,
                          node: _passwordNode,
                          textInputType: TextInputType.text,
                          isObscured: signup.isPasswordObscured,
                          isFocused: _isPasswordFocused,
                          errorText: signup.getFieldError('password'),
                          suffixIcon: IconButton(
                            padding: const EdgeInsetsDirectional.only(end: 12),
                            onPressed: () {
                              signup.setPasswordObscured(!signup.isPasswordObscured);
                            },
                            icon: Icon(signup.isPasswordObscured ? Icons.visibility : Icons.visibility_off),
                          ),
                        ),
                        Config.heightMedium,

                        // Confirm Password
                        LabelText(label: 'Confirm Password ', isRequired: true),
                        CustomTextField(
                          controller: _confirmPasswordController,
                          node: _confirmPasswordNode,
                          textInputType: TextInputType.text,
                          isObscured: signup.isConfirmPasswordObscured,
                          isFocused: _isConfirmPasswordFocused,
                          errorText: signup.getFieldError('confirm_password'),
                          suffixIcon: IconButton(
                            padding: const EdgeInsetsDirectional.only(end: 12),
                            onPressed: () {
                              signup.setConfirmPasswordObscured(!signup.isConfirmPasswordObscured);
                            },
                            icon: Icon(signup.isConfirmPasswordObscured ? Icons.visibility : Icons.visibility_off),
                          ),
                        ),
                        Config.heightBig,

                        // Sign Up Button
                        Button(
                          width: double.infinity,
                          title: 'Sign up',
                          onPressed: () async {
                            signup.clearErrors();

                            if (signup.formattedBDate == null) {
                              signup.setFieldErrors({'birth_date': ['Please select a birth date.']});
                              return;
                            }

                            setState(() => _isLoading = true);

                            await signup.register(
                              firstName: _firstNameController.text,
                              lastName: _lastNameController.text,
                              birthDate: signup.formattedBDate!,
                              phoneNumber: _phoneNumberController.text.trim(),
                              email: _emailController.text.trim(),
                              password: _passwordController.text.trim(),
                              passwordConfirmation: _confirmPasswordController.text.trim(),
                            );

                            setState(() => _isLoading = false);

                            if (signup.fieldErrors.isNotEmpty) return;

                            if (signup.error?.isNotEmpty ?? false) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text(signup.error!)),
                              );
                            }

                            if (signup.success) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text(signup.successMessage.toString())),
                              );
                              Navigator.pushReplacement(
                                context,
                                MaterialPageRoute(builder: (context) => const LoginView()),
                              );
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
                                  MaterialPageRoute(builder: (context) => const LoginView()),
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

                if (_isLoading)
                  Container(
                    color: Colors.black.withValues(alpha: 0.5, red: 0, green: 0, blue: 0),
                    child: const Center(
                      child: CircularProgressIndicator(color: Color(0xFFDDDDDD)),
                    ),
                  ),
              ],
            ),
          );
        },
      ),
    );
  }
}
