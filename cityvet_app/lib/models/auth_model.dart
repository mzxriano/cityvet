class AuthModel {

  final String firstName;
  final String lastName;
  final String birthDate;
  final String phoneNumber;
  final String email;
  final String password;
  final int barangay_id;
  final String street;

  AuthModel({
    required this.firstName,
    required this.lastName,
    required this.birthDate,
    required this.phoneNumber,
    required this.email,
    required this.password,
    required this.barangay_id,
    required this.street,
  });

  factory AuthModel.fromJson(Map<String, dynamic> json) {
    return AuthModel(
      firstName: json['first_name'], 
      lastName: json['last_name'], 
      birthDate: json['birth_date'],
      phoneNumber: json['phone_number'], 
      email: json['email'],
      barangay_id: json['barangay_id'],
      street: json['street'], 
      password: json['password']);
  }


  Map<String, dynamic> toJson() => {
    'first_name' : firstName,
    'last_name' : lastName,
    'birth_date' : birthDate,
    'phone_number' : phoneNumber,
    'email' : email,
    'barangay_id': barangay_id,
    'street': street,
    'password' : password
  };

}