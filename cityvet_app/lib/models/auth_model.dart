class AuthModel {

  final String firstName;
  final String lastName;
  final String birthDate;
  final String phoneNumber;
  final String email;
  final String password;

  AuthModel({
    required this.firstName,
    required this.lastName,
    required this.birthDate,
    required this.phoneNumber,
    required this.email,
    required this.password,
  });

  factory AuthModel.fromJson(Map<String, dynamic> json) {
    return AuthModel(
      firstName: json['first_name'], 
      lastName: json['last_name'], 
      birthDate: json['birth_date'],
      phoneNumber: json['phone_number'], 
      email: json['email'], 
      password: json['password']);
  }


  Map<String, dynamic> toJson() => {
    'first_name' : firstName,
    'last_name' : lastName,
    'birth_date' : birthDate,
    'phone_number' : phoneNumber,
    'email' : email,
    'password' : password
  };

}