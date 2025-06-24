import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class CustomTextField extends StatefulWidget {
  final TextEditingController controller;
  final FocusNode node;
  final bool isObscured;
  final bool isFocused;
  final String? errorText;
  final Widget? suffixIcon;

  const CustomTextField({
    super.key,
    required this.controller,
    required this.node,
    required this.isObscured,
    this.isFocused = false,
    this.errorText,
    this.suffixIcon,
  });

  @override
  State<CustomTextField> createState() => _CustomTextFieldState();
}

class _CustomTextFieldState extends State<CustomTextField> {
  @override
  Widget build(BuildContext context) {
    Config().init(context);
    final hasError = widget.errorText != null;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        TextField(
          controller: widget.controller,
          focusNode: widget.node,
          obscureText: widget.isObscured,
          decoration: InputDecoration(
            filled: true,
            fillColor: widget.isFocused ? Colors.transparent : Config.secondaryColor,
            enabledBorder: OutlineInputBorder(
              borderSide: BorderSide(color: hasError ? Colors.red : Colors.transparent),
              borderRadius: BorderRadius.all(Radius.circular(10)),
            ),
            focusedBorder: OutlineInputBorder(
              borderSide: BorderSide(color: hasError ? Colors.red : Config.primaryColor, width: 2),
              borderRadius: BorderRadius.all(Radius.circular(10)),
            ),
            contentPadding: Config.paddingTextfield,
            suffixIcon: widget.suffixIcon,
          ),
        ),
        if (hasError) ...[
          const SizedBox(height: 4),
          Text(
            widget.errorText!,
            style: const TextStyle(color: Colors.red, fontSize: 12),
          ),
        ]
      ],
    );
  }
}
