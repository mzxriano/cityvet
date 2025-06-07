import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class CustomTextField extends StatefulWidget {
  final TextEditingController controller;
  final FocusNode node;
  final bool isObscured;

  const CustomTextField({
    super.key,
    required this.controller,
    required this.node,
    required this.isObscured,
    });

  @override
  State<CustomTextField> createState() => _CustomTextFieldState();
}

class _CustomTextFieldState extends State<CustomTextField> {
  @override
  Widget build(BuildContext context) {
    Config().init(context);
    return TextField(
      controller: widget.controller,
      focusNode: widget.node,
      decoration: InputDecoration(
        filled: true,
        fillColor: widget.isObscured ? Colors.transparent 
          : Config.secondaryColor,
        enabledBorder: Config.enabledBorder,
        focusedBorder: Config.focusedBorder,
        contentPadding: Config.paddingTextfield, 
      ),
    );
  }
}