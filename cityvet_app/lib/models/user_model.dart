import 'package:cityvet_app/models/barangay_model.dart';

class UserModel {
  final int? id;
  final String? role;
  final String? firstName;
  final String? lastName;
  final String? email;
  final String? phoneNumber;
  final String? birthDate;
  final BarangayModel? barangay;
  final String? street;
  final String? imageUrl;
  final String? imagePublicId;

  UserModel({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.email,
    required this.phoneNumber,
    required this.birthDate,
    required this.barangay,
    required this.street,
    this.role,
    this.imageUrl,
    this.imagePublicId,
  });

  // Convert from JSON (after fetching user )
  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'],
      role: json['role'],
      firstName: json['first_name'],
      lastName: json['last_name'],
      email: json['email'],
      phoneNumber: json['phone_number'],
      birthDate: json['birth_date'],
      barangay: json['barangay'] != null 
      ? BarangayModel.fromJson(json['barangay']) 
      : null,
      street: json['street'],
      imageUrl: json['image_url'],
      imagePublicId: json['image_public_id']
    );
  }

  // Convert to JSON (if needed, to update user )
  Map<String, dynamic> toJson() {
    return {
      if (id != null) 'id': id,
      if(role != null) 'role' : role,
      if (firstName != null) 'first_name': firstName,
      if (lastName != null) 'last_name': lastName,
      if (email != null) 'email': email,
      if (phoneNumber != null) 'phone_number': phoneNumber,
      if (birthDate != null) 'birth_date': birthDate,
      if (barangay != null) 'barangay_id': barangay?.id,
      if (street != null) 'street': street,
      if (imageUrl != null) 'image_url': imageUrl,
      if (imagePublicId != null) 'image_public_id': imagePublicId,
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
