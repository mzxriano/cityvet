import 'package:cityvet_app/services/auth_service.dart';
import 'package:cityvet_app/views/login_view.dart';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';

class EmailVerificationPage extends StatelessWidget {
  final String email;

  const EmailVerificationPage({required this.email});

  Future<void> resendVerificationEmail(BuildContext context) async {
    try {
      final response = await AuthService.resendVerification(email);
      
      // Check response status or data
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Verification email resent successfully!'),
              backgroundColor: Colors.green,
            ),
          );
        }
      } else if (response.statusCode == 302) {
        // Handle redirect - might indicate the user is already verified
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Your account may already be verified. Please try logging in.'),
              backgroundColor: Colors.orange,
            ),
          );
        }
      } else {
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Failed to resend. Status: ${response.statusCode}'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    } on DioException catch (dioError) {
      print('DioException resending verification email: ${dioError.message}');
      print('Status Code: ${dioError.response?.statusCode}');
      print('Response Data: ${dioError.response?.data}');
      
      String errorMessage = 'Failed to resend verification email.';
      
      if (dioError.response?.statusCode == 302) {
        errorMessage = 'Your account may already be verified. Please try logging in.';
      } else if (dioError.response?.statusCode == 404) {
        errorMessage = 'Verification service not found. Please contact support.';
      } else if (dioError.response?.statusCode == 422) {
        errorMessage = 'Invalid email address or verification request.';
      } else if (dioError.response?.statusCode == 429) {
        errorMessage = 'Too many requests. Please wait before trying again.';
      }
      
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(errorMessage),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      print('General error resending verification email: $e');
      
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('An unexpected error occurred. Please try again.'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFF8ED968), Color(0xFFA5E37C)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: Padding(
          padding: EdgeInsets.all(16.0),
          child: Center(
            child: SingleChildScrollView(
              child: Card(
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                elevation: 8,
                child: Padding(
                  padding: EdgeInsets.all(20.0),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: <Widget>[
                      // Email Icon
                      Icon(
                        Icons.mail_outline,
                        size: 80,
                        color: Colors.green[700],
                      ),
                      SizedBox(height: 20),

                      // Heading Text
                      Text(
                        'Check your inbox!',
                        style: TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                          color: Colors.green[700],
                        ),
                        textAlign: TextAlign.center,
                      ),
                      SizedBox(height: 10),

                      // Information Text
                      Text(
                        'A verification link has been sent to:',
                        style: TextStyle(fontSize: 16, color: Colors.black54),
                        textAlign: TextAlign.center,
                      ),
                      SizedBox(height: 8),
                      Text(
                        email,
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Colors.black87,
                        ),
                        textAlign: TextAlign.center,
                      ),
                      SizedBox(height: 20),

                      // Instruction Text
                      Text(
                        'Please check your inbox and click the verification link to activate your account.',
                        style: TextStyle(fontSize: 16, color: Colors.black54),
                        textAlign: TextAlign.center,
                      ),
                      SizedBox(height: 30),

                      // Resend Verification Email Button
                      ElevatedButton(
                        onPressed: () {
                          resendVerificationEmail(context);
                        },
                        child: const Text('Resend Verification Email'),
                        style: ElevatedButton.styleFrom(
                          foregroundColor: Colors.blueAccent,
                          padding: EdgeInsets.symmetric(horizontal: 30, vertical: 12),
                          textStyle: TextStyle(fontSize: 16),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                      ),
                      SizedBox(height: 20),

                      // Back to Login Button
                      TextButton(
                        onPressed: () {
                          // Navigate back to login screen (or anywhere else)
                          Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => LoginView()));
                        },
                        child: Text(
                          'Back to Login',
                          style: TextStyle(
                            color: Colors.grey, // Consistent with the gradient color
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
