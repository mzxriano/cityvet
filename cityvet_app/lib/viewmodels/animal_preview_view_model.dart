import 'package:flutter/material.dart';

class AnimalPreviewViewModel extends ChangeNotifier {
  String? _ageString;

  String? get ageString => _ageString;

  void calculateAge(String date) {
    try {
      final birthDate = DateTime.parse(date);
      final now = DateTime.now();

      int years = now.year - birthDate.year;
      int months = now.month - birthDate.month;

      if (now.day < birthDate.day) {
        months--;
      }
      if (months < 0) {
        years--;
        months += 12;
      }

      if (years <= 0 && months <= 0) {
        _ageString = 'Less than a month old';
      } else if (years <= 0) {
        _ageString = '$months ${months == 1 ? 'month' : 'months'} old';
      } else {
        _ageString = '$years ${years == 1 ? 'year' : 'years'} old';
      }

      notifyListeners();
    } catch (e) {
      _ageString = null;
      notifyListeners();
    }
  }
}
