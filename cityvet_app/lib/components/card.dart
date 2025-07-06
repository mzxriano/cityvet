import 'package:flutter/material.dart';

class CustomCard extends StatefulWidget {
  final double width;
  final Color color;
  final Widget widget;
  final GestureTapCallback? function;

  const CustomCard({
    super.key,
    required this.width,
    required this.color,
    required this.widget,
    this.function,
  });

  @override
  State<CustomCard> createState() => CustomCardState();
}

class CustomCardState extends State<CustomCard> {
  @override
  Widget build(BuildContext context) {
    return Container(
      width: widget.width,
      constraints: const BoxConstraints(
        maxWidth: 700,
      ),
      decoration: BoxDecoration(
        color: widget.color,
        borderRadius: BorderRadius.circular(25),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 25.0, vertical: 15.0),
        child: GestureDetector(
          onTap: widget.function,
          child: widget.widget,
        ),
      ),
    );
  }
}
