import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/components/label_text.dart';
import 'package:cityvet_app/main_layout.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/login_view_model.dart';
import 'package:cityvet_app/views/forgot_pass_view.dart';
import 'package:cityvet_app/views/signup_view.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

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
                                Text('Login',
                                  style: TextStyle(
                                    fontFamily: Config.primaryFont,
                                    fontSize: Config.fontBig,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Config.heightMedium,
                          LabelText(label: 'Email ', isRequired: true),
                          TextField(
                            controller: _emailController,
                            focusNode: _emailNode,
                            decoration: InputDecoration(
                              filled: true,
                              fillColor: _isEmailFocused ? Colors.transparent : Config.secondaryColor,
                              enabledBorder: _getBorder(
                                loginViewModel.fieldErrors['email'] != null ? Colors.red : Colors.transparent,
                              ),
                              focusedBorder: _getBorder(
                                loginViewModel.fieldErrors['email'] != null ? Colors.red : Config.primaryColor,
                              ),
                              contentPadding: Config.paddingTextfield,
                            ),
                          ),
                          if (loginViewModel.fieldErrors['email'] != null)
                            Padding(
                              padding: const EdgeInsets.only(top: 8.0),
                              child: Text(
                                loginViewModel.fieldErrors['email'].join(', '),
                                style: const TextStyle(color: Colors.red, fontSize: 12),
                              ),
                            ),
                          Config.heightMedium,
                          LabelText(label: 'Password ', isRequired: true),
                          TextField(
                            controller: _passwordController,
                            focusNode: _passwordNode,
                            obscureText: _isObscured,
                            decoration: InputDecoration(
                              filled: true,
                              fillColor: _isPassFocused ? Colors.transparent : Config.secondaryColor,
                              enabledBorder: _getBorder(
                                loginViewModel.fieldErrors['password'] != null ? Colors.red : Colors.transparent,
                              ),
                              focusedBorder: _getBorder(
                                loginViewModel.fieldErrors['password'] != null ? Colors.red : Config.primaryColor,
                              ),
                              contentPadding: Config.paddingTextfield,
                              suffixIcon: IconButton(
                                padding: const EdgeInsetsDirectional.only(end: 12),
                                onPressed: () => setState(() => _isObscured = !_isObscured),
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
                                loginViewModel.fieldErrors['password'].join(', '),
                                style: const TextStyle(color: Colors.red, fontSize: 12),
                              ),
                            ),
                          Config.heightSmall,
                          Align(
                            alignment: Alignment.centerRight,
                            child: TextButton(
                              onPressed: () {
                                Navigator.of(context).push(
                                  MaterialPageRoute(builder: (_) => const ForgotPassView()),
                                );
                              },
                              child: Text('Forgot Password',
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

                                if (loginViewModel.error?.isNotEmpty ?? false) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(content: Text(loginViewModel.error!)),
                                  );
                                }

                                if (loginViewModel.isLogin) {
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
                              Text('Don\'t have an account?',
                                style: TextStyle(
                                  fontFamily: Config.primaryFont,
                                  fontSize: Config.fontSmall,
                                ),
                              ),
                              TextButton(
                                onPressed: () {
                                  Navigator.of(context).pushReplacement(
                                    MaterialPageRoute(builder: (_) => const SignupView()),
                                  );
                                },
                                child: Text('Sign up',
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
                if (loginViewModel.isLoading)
                  Container(
                    width: double.infinity,
                    height: double.infinity,
                    color: Colors.black.withAlpha(100),
                    child: const Center(
                      child: CircularProgressIndicator(color: Color(0xFFDDDDDD)),
                    ),
                  )
              ],
            ),
          );
        },
      ),
    );
  }
}
