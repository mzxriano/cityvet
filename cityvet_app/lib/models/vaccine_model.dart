class VaccineModel {
  final int id;
  final String name;
  final String? description;
  final int? stock;
  final String? imageUrl;
  final String? imagePublicId;
  final String? protectAgainst;
  final String? affected;
  final String? schedule;
  final String? expirationDate;

  VaccineModel({
    required this.id,
    required this.name,
    this.description,
    this.stock,
    this.imageUrl,
    this.imagePublicId,
    this.protectAgainst,
    this.affected,
    this.schedule,
    this.expirationDate,
  });

  factory VaccineModel.fromJson(Map<String, dynamic> json) {
    return VaccineModel(
      id: json['id'],
      name: json['name'],
      description: json['description'],
      stock: json['stock'],
      imageUrl: json['image_url'],
      imagePublicId: json['image_public_id'],
      protectAgainst: json['protect_against'],
      affected: json['affected'],
      schedule: json['schedule'],
      expirationDate: json['expiration_date'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'stock': stock,
      'image_url': imageUrl,
      'image_public_id': imagePublicId,
      'protect_against': protectAgainst,
      'affected': affected,
      'schedule': schedule,
      'expiration_date': expirationDate,
    };
  }
}

class AnimalVaccinationModel {
  final int id;
  final VaccineModel vaccine;
  final String dateGiven;
  final int dose;
  final String? administrator;

  AnimalVaccinationModel({
    required this.id,
    required this.vaccine,
    required this.dateGiven,
    required this.dose,
    required this.administrator,
  });

  factory AnimalVaccinationModel.fromJson(Map<String, dynamic> json) {
    return AnimalVaccinationModel(
      id: json['id'],
      vaccine: VaccineModel.fromJson(json['vaccine']),
      dateGiven: json['date_given'] ?? '',
      dose: json['dose'] is int ? json['dose'] : int.tryParse(json['dose'].toString()) ?? 1,
      administrator: json['administrator'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'vaccine': vaccine.toJson(),
      'date_given': dateGiven,
      'dose': dose,
      'administrator': administrator,
    };
  }
} 