import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/components/email_verification_page.dart';
import 'package:cityvet_app/components/label_text.dart';
import 'package:cityvet_app/components/text_field.dart';
import 'package:cityvet_app/utils/config.dart';
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
  final TextEditingController _suffixController = TextEditingController();
  final TextEditingController _phoneNumberController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _streetController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _confirmPasswordController = TextEditingController();

  final FocusNode _firstNameNode = FocusNode();
  final FocusNode _lastNameNode = FocusNode();
  final FocusNode _suffixNode = FocusNode();
  final FocusNode _phoneNumberNode = FocusNode();
  final FocusNode _emailNode = FocusNode();
  final FocusNode _streetNode = FocusNode();
  final FocusNode _passwordNode = FocusNode();
  final FocusNode _confirmPasswordNode = FocusNode();

  bool _isFirstNameFocused = false;
  bool _isLastNameFocused = false;
  bool _isSuffixFocused = false;
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
    _suffixNode.addListener(() {
      setState(() {
        _isSuffixFocused = _suffixNode.hasFocus;
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
    _suffixController.dispose();
    _phoneNumberController.dispose();
    _emailController.dispose();
    _streetController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();

    _firstNameNode.dispose();
    _lastNameNode.dispose();
    _suffixNode.dispose();
    _phoneNumberNode.dispose();
    _emailNode.dispose();
    _streetNode.dispose();
    _passwordNode.dispose();
    _confirmPasswordNode.dispose();

    super.dispose();
  }

  // Privacy Protection Dialog
  void _showPrivacyProtectionDialog(SignupViewModel signup) {
    bool isAgreed = false;
    
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(15),
              ),
              title: Row(
                children: [
                  Icon(
                    Icons.privacy_tip,
                    color: Config.primaryColor,
                    size: 28,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'Privacy Protection',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.black87,
                    ),
                  ),
                ],
              ),
              content: Container(
                constraints: const BoxConstraints(maxHeight: 400),
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Before proceeding, please review and agree to our Privacy Protection policy.',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
                          color: Colors.black87,
                        ),
                      ),
                      const SizedBox(height: 16),
                      Container(
                        height: 200,
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: Colors.grey[300]!),
                        ),
                        child: const SingleChildScrollView(
                          child: Text(
                            'Privacy Protection Policy\n\n',
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.black87,
                              height: 1.5,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Checkbox(
                            value: isAgreed,
                            onChanged: (bool? value) {
                              setDialogState(() {
                                isAgreed = value ?? false;
                              });
                            },
                            activeColor: Config.primaryColor,
                          ),
                          Expanded(
                            child: Padding(
                              padding: const EdgeInsets.only(top: 12),
                              child: Text(
                                'I have read and agree to the Privacy Protection policy.',
                                style: TextStyle(
                                  fontFamily: Config.primaryFont,
                                  fontSize: Config.fontSmall,
                                  color: Colors.black87,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () {
                    Navigator.of(context).pop();
                    setState(() => _isLoading = false);
                  },
                  child: Text(
                    'Cancel',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      color: Colors.grey[600],
                      fontSize: Config.fontSmall,
                    ),
                  ),
                ),
                ElevatedButton(
                  onPressed: isAgreed
                      ? () async {
                          Navigator.of(context).pop();
                          setState(() => _isLoading = true);

                          await signup.register(
                            firstName: _firstNameController.text,
                            lastName: _lastNameController.text,
                            birthDate: signup.formattedBDate!,
                            barangay_id: int.parse(signup.selectedBarangay!),
                            street: _streetController.text.trim(),
                            phoneNumber: _phoneNumberController.text.trim(),
                            email: _emailController.text.trim(),
                            password: _passwordController.text.trim(),
                            passwordConfirmation: _confirmPasswordController.text.trim(),
                          );

                          setState(() => _isLoading = false);

                          if (signup.fieldErrors.isNotEmpty) {
                            // Show errors if any
                            return;
                          }

                          if (signup.error?.isNotEmpty ?? false) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(content: Text(signup.error!)),
                            );
                            return;
                          }

                          if (signup.success) {
                            _proceedToEmailVerification();
                          }
                        }
                      : null,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Config.primaryColor,
                    disabledBackgroundColor: Colors.grey[300],
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                  child: Text(
                    'Agree & Continue',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      color: isAgreed ? Colors.white : Colors.grey[600],
                      fontSize: Config.fontSmall,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _proceedToEmailVerification() {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Consumer<SignupViewModel>(
          builder: (context, signup, _) {
            return Text(signup.successMessage.toString());
          },
        ),
      ),
    );
    
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(
        builder: (context) => EmailVerificationPage(
          email: _emailController.text,
        ),
      ),
    );
    
    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    return ChangeNotifierProvider<SignupViewModel>(
      create: (_) => SignupViewModel()..fetchBarangays(),
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
                        RichText(
                          text: TextSpan(
                            style: TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: 30,
                              fontWeight: FontWeight.bold,
                              color: Colors.black,  
                            ),
                            children: [
                              TextSpan(text: 'Getting started\nwith '),
                              TextSpan(
                                text: 'CityVet',
                                style: TextStyle(color: Config.primaryColor),
                              ),
                            ],
                          ),
                        ),
                        Config.heightBig,

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

                        // Suffix (Jr., Sr., III, etc.)
                        LabelText(label: 'Suffix', isRequired: false),
                        CustomTextField(
                          controller: _suffixController,
                          node: _suffixNode,
                          textInputType: TextInputType.name,
                          isObscured: false,
                          isFocused: _isSuffixFocused,
                          errorText: signup.getFieldError('suffix'),
                          //hintText: 'Jr., Sr., III, etc. (Optional)',
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
                        Container(
                          decoration: BoxDecoration(
                            color: Config.secondaryColor,
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(
                              color: signup.getFieldError('phone_number') != null 
                                  ? Colors.red 
                                  : (_isPhoneNumberFocused ? Config.primaryColor : Colors.transparent),
                              width: _isPhoneNumberFocused ? 2 : 1,
                            ),
                          ),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 16),
                                decoration: BoxDecoration(
                                  border: Border(
                                    right: BorderSide(color: Colors.grey.withOpacity(0.3), width: 1),
                                  ),
                                ),
                                child: Row(
                                  children: [
                                    const Text(
                                      '🇵🇭',
                                      style: TextStyle(fontSize: 18),
                                    ),
                                    const SizedBox(width: 8),
                                    const Text(
                                      '+63',
                                      style: TextStyle(
                                        fontFamily: Config.primaryFont,
                                        fontSize: Config.fontSmall,
                                        fontWeight: FontWeight.w500,
                                        color: Colors.black87,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              // Phone number input
                              Expanded(
                                child: TextField(
                                  controller: _phoneNumberController,
                                  focusNode: _phoneNumberNode,
                                  keyboardType: TextInputType.phone,
                                  style: const TextStyle(
                                    fontFamily: Config.primaryFont,
                                    fontSize: Config.fontSmall,
                                  ),
                                  decoration: InputDecoration(
                                    hintText: 'Enter phone number',
                                    hintStyle: TextStyle(
                                      fontFamily: Config.primaryFont,
                                      fontSize: Config.fontSmall,
                                      color: Colors.grey[500],
                                    ),
                                    border: InputBorder.none,
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 16),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        if (signup.getFieldError('phone_number') != null) ...[
                          const SizedBox(height: 4),
                          Text(
                            signup.getFieldError('phone_number').toString(),
                            style: const TextStyle(color: Colors.red, fontSize: 12),
                          ),
                        ],
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
                            items: signup.barangays?.map((barangay) {
                              return DropdownMenuItem<String>(
                                value: barangay.id.toString(), 
                                child: Text(barangay.name),    
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
                          errorText: signup.getFieldError('confirmPassword'),
                          suffixIcon: IconButton(
                            padding: const EdgeInsetsDirectional.only(end: 12),
                            onPressed: () {
                              signup.setConfirmPasswordObscured(!signup.isConfirmPasswordObscured);
                            },
                            icon: Icon(signup.isConfirmPasswordObscured ? Icons.visibility : Icons.visibility_off),
                          ),
                        ),
                        Config.heightBig,

                        Button(
                          width: double.infinity,
                          title: 'Sign up',
                          onPressed: () async {
                            signup.clearErrors();

                            if (signup.formattedBDate == null) {
                              signup.setFieldErrors({'birth_date': ['Please select a birth date.']});
                              return;
                            }

                            if (signup.selectedBarangay == null || signup.selectedBarangay!.isEmpty) {
                              signup.setFieldErrors({'barangay': ['Please select a barangay.']});
                              return;
                            }

                            _showPrivacyProtectionDialog(signup);
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

class TrianglePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white
      ..style = PaintingStyle.fill;

    final path = Path()
      ..moveTo(0, 0)
      ..lineTo(size.width, size.height / 2)
      ..lineTo(0, size.height)
      ..close();

    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}