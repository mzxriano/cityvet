import 'package:flutter/material.dart';

class RoleWidget {

  Widget operator [](String role) {
    final Map<String, Color> roleColors = {
      'Owner': Color(0xFF36D221),
      'Veterinarian': Color(0xFFEDA726),
      'AEW': Color(0xFFB90336),
    };

    final color = roleColors[role] ?? Colors.grey;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(100),
      ),
      child: Text(
        role,
        style: TextStyle(
          fontFamily: 'Poppins',
          color: Colors.white,
          fontSize: 7,
        ),
      ),
    );
  }
}

