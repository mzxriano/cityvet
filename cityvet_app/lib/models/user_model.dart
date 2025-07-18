import 'package:cityvet_app/models/barangay_model.dart';

class UserModel {
  final String? firstName;
  final String? lastName;
  final String? email;
  final String? phoneNumber;
  final String? birthDate;
  final BarangayModel? barangay;
  final String? street;

  UserModel({
    required this.firstName,
    required this.lastName,
    required this.email,
    required this.phoneNumber,
    required this.birthDate,
    required this.barangay,
    required this.street
  });

  // Convert from JSON (after fetching user )
  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      firstName: json['first_name'],
      lastName: json['last_name'],
      email: json['email'],
      phoneNumber: json['phone_number'],
      birthDate: json['birth_date'],
      barangay: json['barangay'] != null 
      ? BarangayModel.fromJson(json['barangay']) 
      : null,
      street: json['street'],
    );
  }

  // Convert to JSON (if needed, to update user )
  Map<String, dynamic> toJson() {
    return {
      if (firstName != null) 'first_name': firstName,
      if (lastName != null) 'last_name': lastName,
      if (email != null) 'email': email,
      if (phoneNumber != null) 'phone_number': phoneNumber,
      if (birthDate != null) 'birth_date': birthDate,
      if (barangay != null) 'barangay_id': barangay?.id,
      if (street != null) 'street': street,
    };
  }

    String get ageString {
    if (birthDate == null) return 'No specified birthdate';

    try {
      final birth = DateTime.parse(birthDate!);
      final now = DateTime.now();
      int years = now.year - birth.year;
      int months = now.month - birth.month;

      if (now.day < birth.day) months--;
      if (months < 0) {
        years--;
        months += 12;
      }

      if (years <= 0 && months <= 0) {
        return 'Less than a month old';
      } else if (years <= 0) {
        return '$months ${months == 1 ? 'month' : 'months'} old';
      } else {
        return '$years ${years == 1 ? 'year' : 'years'} old';
      }
    } catch (_) {
      return 'Invalid birthdate';
    }
  }
}
