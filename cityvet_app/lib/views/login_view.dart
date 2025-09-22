import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/components/email_verification_page.dart';
import 'package:cityvet_app/components/label_text.dart';
import 'package:cityvet_app/components/status_modal.dart';
import 'package:cityvet_app/main_layout.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/login_view_model.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:cityvet_app/views/force_password_change_view.dart';
import 'package:cityvet_app/views/forgot_pass_view.dart';
import 'package:cityvet_app/views/signup_view.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cityvet_app/services/fcm_service.dart';

class LoginView extends StatefulWidget {
  const LoginView({super.key});

  @override
  State<LoginView> createState() => _LoginViewState();
}

class _LoginViewState extends State<LoginView> {
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();

  final FocusNode _emailNode = FocusNode();
  final FocusNode _passwordNode = FocusNode();

  bool _isEmailFocused = false;
  bool _isPassFocused = false;
  bool _isObscured = true;

  @override
  void initState() {
    super.initState();

    _emailNode.addListener(() {
      setState(() => _isEmailFocused = _emailNode.hasFocus);
    });

    _passwordNode.addListener(() {
      setState(() => _isPassFocused = _passwordNode.hasFocus);
    });
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _emailNode.dispose();
    _passwordNode.dispose();
    super.dispose();
  }

  OutlineInputBorder _getBorder(Color color) {
    return OutlineInputBorder(
      borderRadius: BorderRadius.circular(8),
      borderSide: BorderSide(color: color, width: 1.5),
    );
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    return ChangeNotifierProvider<LoginViewModel>(
      create: (_) => LoginViewModel(),
      child: Consumer<LoginViewModel>(
        builder: (context, loginViewModel, _) {
          return Scaffold(
            body: Stack(
              children: [
                SafeArea(
                  child: SingleChildScrollView(
                    child: ConstrainedBox(
                      constraints: BoxConstraints(
                        minHeight: MediaQuery.of(context).size.height -
                            MediaQuery.of(context).padding.top -
                            MediaQuery.of(context).padding.bottom,
                      ),
                      child: IntrinsicHeight(
                        child: Padding(
                          padding: Config.paddingScreen,
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
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
                                        TextSpan(text: 'Login\nto '),
                                        TextSpan(
                                          text: 'CityVet',
                                          style: TextStyle(color: Config.primaryColor),
                                        ),
                                      ],
                                    ),
                                  ),
                                  Config.primaryLogo
                                ],
                              ),
                              Config.heightMedium,
                              LabelText(label: 'Email ', isRequired: true),
                              TextField(
                                controller: _emailController,
                                focusNode: _emailNode,
                                style: TextStyle(
                                  fontFamily: Config.primaryFont
                                ),
                                decoration: InputDecoration(
                                  filled: true,
                                  fillColor: _isEmailFocused
                                      ? Colors.transparent
                                      : Config.secondaryColor,
                                  enabledBorder: _getBorder(
                                    loginViewModel.fieldErrors['email'] != null
                                        ? Colors.red
                                        : Colors.transparent,
                                  ),
                                  focusedBorder: _getBorder(
                                    loginViewModel.fieldErrors['email'] != null
                                        ? Colors.red
                                        : Config.primaryColor,
                                  ),
                                  contentPadding: Config.paddingTextfield,
                                ),
                              ),
                              if (loginViewModel.fieldErrors['email'] != null)
                                Padding(
                                  padding: const EdgeInsets.only(top: 8.0),
                                  child: Text(
                                    loginViewModel.fieldErrors['email']
                                        .join(', '),
                                    style: const TextStyle(
                                        color: Colors.red, fontSize: 12),
                                  ),
                                ),
                              Config.heightMedium,
                              LabelText(label: 'Password ', isRequired: true),
                              TextField(
                                controller: _passwordController,
                                focusNode: _passwordNode,
                                obscureText: _isObscured,
                                style: TextStyle(
                                  fontFamily: Config.primaryFont
                                ),
                                decoration: InputDecoration(
                                  filled: true,
                                  fillColor: _isPassFocused
                                      ? Colors.transparent
                                      : Config.secondaryColor,
                                  enabledBorder: _getBorder(
                                    loginViewModel.fieldErrors['password'] !=
                                            null
                                        ? Colors.red
                                        : Colors.transparent,
                                  ),
                                  focusedBorder: _getBorder(
                                    loginViewModel.fieldErrors['password'] !=
                                            null
                                        ? Colors.red
                                        : Config.primaryColor,
                                  ),
                                  contentPadding: Config.paddingTextfield,
                                  suffixIcon: IconButton(
                                    padding:
                                        const EdgeInsetsDirectional.only(end: 12),
                                    onPressed: () => setState(
                                        () => _isObscured = !_isObscured),
                                    icon: _isObscured
                                        ? const Icon(Icons.visibility)
                                        : const Icon(Icons.visibility_off),
                                  ),
                                ),
                              ),
                              if (loginViewModel.fieldErrors['password'] != null)
                                Padding(
                                  padding: const EdgeInsets.only(top: 8.0),
                                  child: Text(
                                    loginViewModel.fieldErrors['password']
                                        .join(', '),
                                    style: const TextStyle(
                                        color: Colors.red, fontSize: 12),
                                  ),
                                ),
                              Config.heightSmall,
                              Align(
                                alignment: Alignment.centerRight,
                                child: TextButton(
                                  onPressed: () {
                                    Navigator.of(context).push(
                                      MaterialPageRoute(
                                          builder: (_) =>
                                              const ForgotPassView()),
                                    );
                                  },
                                  child: Text(
                                    'Forgot Password',
                                    style: TextStyle(
                                      fontFamily: Config.primaryFont,
                                      fontSize: Config.fontSmall,
                                      color: Colors.red,
                                    ),
                                  ),
                                ),
                              ),
                              Config.heightMedium,
                              Button(
                                width: double.infinity,
                                title: 'Login',
                                onPressed: () async {
                                  if (!loginViewModel.isLoading) {
                                    loginViewModel.fieldErrors.clear();

                                    await loginViewModel.login(
                                      _emailController.text.trim(),
                                      _passwordController.text.trim(),
                                    );

                                    if (loginViewModel.error != null) {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        SnackBar(content: Text(loginViewModel.message))
                                      );
                                    }

                                    if(loginViewModel.isEmailVerified == false && loginViewModel.error == 'email_not_verified') {
                                      Navigator.push(context, MaterialPageRoute(
                                        builder: (_) => EmailVerificationPage(email: _emailController.text)
                                      ));
                                    }

                                    if (loginViewModel.isLogin) {
                                      final userVM = Provider.of<UserViewModel>(context, listen: false);
                                      userVM.setUser(loginViewModel.user!);

                                      final userStatus = loginViewModel.user?.status?.toLowerCase();
                                      if (userStatus == 'pending') {
                                        await StatusModal.show(
                                          context,
                                          title: 'Account Pending',
                                          message: 'Your account is still pending approval. Please wait for admin verification.',
                                          barrierDismissible: true,
                                        );
                                        return;
                                      }
                                      if (userStatus == 'rejected') {
                                        await StatusModal.show(
                                          context,
                                          title: 'Account Rejected',
                                          message: 'Your account has been rejected. Please contact support for more information.',
                                          barrierDismissible: true,
                                        );
                                        return;
                                      }
                                      if (userStatus == 'banned') {
                                        await StatusModal.show(
                                          context,
                                          title: 'Account Banned',
                                          message: 'Your account has been banned. Please contact support for more information.',
                                          barrierDismissible: true,
                                        );
                                        return;
                                      }
                                      if (userStatus == 'inactive') {
                                        // Allow login, but show modal after login
                                        WidgetsBinding.instance.addPostFrameCallback((_) {
                                          StatusModal.show(
                                            context,
                                            title: 'Account Inactive',
                                            message: 'Your account is inactive. Some features may be limited.',
                                            barrierDismissible: true,
                                          );
                                        });
                                      }

                                      // Check for force password change before FCM initialization
                                      if (loginViewModel.user?.forcePasswordChange == true) {
                                        Navigator.of(context).pushReplacement(
                                          MaterialPageRoute(builder: (_) => const ForcePasswordChangeView()),
                                        );
                                        return; 
                                      }

                                      // Initialize FCM after login
                                      if(loginViewModel.user?.id != null) {
                                        await FcmService().initialize(userId: loginViewModel.user!.id!);
                                      } else {
                                        print('Failed to initialize firebase');
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          SnackBar(content: Text('Failed to initialize firebase.'))
                                        );
                                      }

                                      Navigator.of(context).pushReplacement(
                                        MaterialPageRoute(builder: (_) => const MainLayout()),
                                      );
                                    }
                                  } else {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      const SnackBar(content: Text('Login failed, Please try again!')),
                                    );
                                  }
                                },
                              ),
                              Config.heightMedium,
                              Row(
                                mainAxisAlignment: MainAxisAlignment.end,
                                children: [
                                  Text(
                                    'Don\'t have an account?',
                                    style: TextStyle(
                                      fontFamily: Config.primaryFont,
                                      fontSize: Config.fontSmall,
                                    ),
                                  ),
                                  TextButton(
                                    onPressed: () {
                                      Navigator.of(context).pushReplacement(
                                        MaterialPageRoute(
                                            builder: (_) => const SignupView()),
                                      );
                                    },
                                    child: Text(
                                      'Sign up',
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
                  ),
                ),
                if (loginViewModel.isLoading)
                  Container(
                    width: double.infinity,
                    height: double.infinity,
                    color: Colors.black.withAlpha(100),
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
