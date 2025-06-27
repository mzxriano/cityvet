import 'package:flutter/material.dart';

class AnimalTypeWidget {

  Widget operator [](String role) {
    final Map<String, Color> roleColors = {
      'Pet': Color(0xFFFF2BBF),
      'Livestock': Color(0xFF1800F4),
    };

    final color = roleColors[role] ?? Colors.grey;

    return Container(
      width: 80,
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(100),
      ),
      child: Center(
        child: Text(
          role,
          style: TextStyle(
            fontFamily: 'Poppins',
            color: Colors.white,
            fontSize: 10,
          ),
        ),
      ),
    );
  }
}

