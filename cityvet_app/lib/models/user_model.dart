class UserModel {
  final String? firstName;
  final String? lastName;
  final String? email;
  final String? phoneNumber;
  final String? barangay;
  final String? street;

  UserModel({
    required this.firstName,
    required this.lastName,
    required this.email,
    required this.phoneNumber,
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
      barangay: json['barangay'],
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
      //if (barangay != null) 'barangay': barangay,
      if (street != null) 'street': street,
    };
  }
}
