class AewModel {
  final int id;
  final String name;
  final String position;
  final String barangay;
  final String contact;
  final String email;
  final String specialization;

  AewModel({
    required this.id,
    required this.name,
    required this.position,
    required this.barangay,
    required this.contact,
    required this.email,
    required this.specialization,
  });

  factory AewModel.fromJson(Map<String, dynamic> json) {
    return AewModel(
      id: json['id'],
      name: json['name'],
      position: json['position'],
      barangay: json['barangay'],
      contact: json['contact'],
      email: json['email'],
      specialization: json['specialization'],
    );
  }
}