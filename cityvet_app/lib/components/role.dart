import 'package:cityvet_app/utils/role_constant.dart';
import 'package:flutter/material.dart';

class RoleWidget {

  Widget operator [](String role) {
    final Map<String, Color> roleColors = {
      Role.petOwner: Color.fromARGB(255, 255, 0, 149),
      Role.livestockOwner: Color.fromARGB(255, 25, 0, 255),
      Role.poultryOwner: Color.fromARGB(255, 200, 255, 0),
      Role.veterinarian: Color.fromARGB(255, 237, 168, 38),
      Role.staff: Color.fromARGB(255, 237, 168, 38),
      Role.aew: Color.fromARGB(255, 185, 3, 54),
      'unknown': Colors.grey,
    };

    final color = roleColors[role] ?? Colors.grey;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 5),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(100),
      ),
      child: Text(
        role,
        style: TextStyle(
          fontFamily: 'Poppins',
          color: Colors.white,
          fontSize: 15,
        ),
      ),
    );
  }
}

