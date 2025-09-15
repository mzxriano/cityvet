import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../viewmodels/user_view_model.dart';
import 'login_view.dart';

class ForcePasswordChangeView extends StatefulWidget {
  const ForcePasswordChangeView({Key? key}) : super(key: key);

  @override
  State<ForcePasswordChangeView> createState() =>
      _ForcePasswordChangeViewState();
}

class _ForcePasswordChangeViewState extends State<ForcePasswordChangeView> {
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  final FocusNode _newPassNode = FocusNode();
  final FocusNode _confirmPassNode = FocusNode();

  bool _isNewPassFocused = false;
  bool _isConfirmPassFocused = false;
  bool _isNewPassObscured = true;
  bool _isConfirmPassObscured = true;
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _newPassNode.addListener(() {
      setState(() => _isNewPassFocused = _newPassNode.hasFocus);
    });
    _confirmPassNode.addListener(() {
      setState(() => _isConfirmPassFocused = _confirmPassNode.hasFocus);
    });
  }

  @override
  void dispose() {
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    _newPassNode.dispose();
    _confirmPassNode.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    return Scaffold(
      appBar: AppBar(
        automaticallyImplyLeading: false,
        title: const Text('Change Password'),
      ),
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
                      Image.asset(
                        'assets/images/reset_pass.png',
                        width: 200,
                        height: 200,
                      ),
                      Config.heightSmall,
                      Text(
                        'Change Your Password',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontBig,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      Config.heightSmall,
                      Text(
                        'For security reasons, you must change your password before continuing.',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
                          color: Colors.red,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                ),
                Config.heightBig,
                Text(
                  'New Password',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                  ),
                ),
                TextField(
                  controller: _newPasswordController,
                  focusNode: _newPassNode,
                  obscureText: _isNewPassObscured,
                  decoration: InputDecoration(
                    filled: true,
                    fillColor:
                        _isNewPassFocused ? Colors.transparent : Config.secondaryColor,
                    enabledBorder: Config.enabledBorder,
                    focusedBorder: Config.focusedBorder,
                    contentPadding: Config.paddingTextfield,
                    suffixIcon: IconButton(
                      padding: const EdgeInsetsDirectional.only(end: 12),
                      onPressed: () =>
                          setState(() => _isNewPassObscured = !_isNewPassObscured),
                      icon: _isNewPassObscured
                          ? const Icon(Icons.visibility)
                          : const Icon(Icons.visibility_off),
                    ),
                  ),
                ),
                Config.heightMedium,
                Text(
                  'Confirm Password',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                  ),
                ),
                TextField(
                  controller: _confirmPasswordController,
                  focusNode: _confirmPassNode,
                  obscureText: _isConfirmPassObscured,
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: _isConfirmPassFocused
                        ? Colors.transparent
                        : Config.secondaryColor,
                    enabledBorder: Config.enabledBorder,
                    focusedBorder: Config.focusedBorder,
                    contentPadding: Config.paddingTextfield,
                    suffixIcon: IconButton(
                      padding: const EdgeInsetsDirectional.only(end: 12),
                      onPressed: () => setState(
                          () => _isConfirmPassObscured = !_isConfirmPassObscured),
                      icon: _isConfirmPassObscured
                          ? const Icon(Icons.visibility)
                          : const Icon(Icons.visibility_off),
                    ),
                  ),
                ),
                Config.heightMedium,
                Button(
                  width: double.infinity,
                  title: 'Change Password',
                  onPressed: _handleSubmit,
                ),
                if (_isLoading) ...[
                  const SizedBox(height: 16),
                  const Center(child: CircularProgressIndicator()),
                ],
                if (_errorMessage != null) ...[
                  const SizedBox(height: 16),
                  Text(_errorMessage!,
                      style: const TextStyle(color: Colors.red)),
                ]
              ],
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _handleSubmit() async {
    final newPassword = _newPasswordController.text.trim();
    final confirmPassword = _confirmPasswordController.text.trim();

    if (newPassword.isEmpty || confirmPassword.isEmpty) {
      setState(() => _errorMessage = 'Please fill in all fields.');
      return;
    }
    if (newPassword.length < 8) {
      setState(() => _errorMessage = 'Password must be at least 8 characters.');
      return;
    }
    if (newPassword != confirmPassword) {
      setState(() => _errorMessage = 'Passwords do not match.');
      return;
    }

    setState(() => _isLoading = true);
    try {
      await context.read<UserViewModel>().changePassword(newPassword);
      if (mounted) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const LoginView()),
        );
      }
    } catch (e) {
      setState(() => _errorMessage = e.toString());
    } finally {
      setState(() => _isLoading = false);
    }
  }
}
