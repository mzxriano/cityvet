import 'package:cityvet_app/models/vaccine_model.dart';

class AnimalModel {
  final int? id;
  final String type;
  final String name;
  final String? owner;
  final String? breed;
  final String? birthDate;
  final String gender;
  final double? weight;
  final double? height;
  final String color;
  final String? code;
  final String? qrCode;
  final String? qrCodeUrl;
  final String? imageUrl;
  final String? imagePublicId;
  final List<AnimalVaccinationModel>? vaccinations;

  AnimalModel({
    this.id, 
    required this.type,
    required this.name,
    required this.breed,
    required this.color,
    required this.gender,
    this.owner,
    this.code,
    this.qrCode,
    this.birthDate,
    this.weight,
    this.height,
    this.qrCodeUrl,
    this.imageUrl,
    this.imagePublicId,
    this.vaccinations,
  });

  factory AnimalModel.fromJson(Map<String, dynamic> json) {
    return AnimalModel(
      id: json['id'],
      type: json['type'],
      name: json['name'],
      breed: json['breed'],
      birthDate: json['birth_date'],
      gender: json['gender'],
      weight: json['weight'] != null ? (json['weight'] as num).toDouble() : null,
      height: json['height'] != null ? (json['height'] as num).toDouble() : null,
      color: json['color'],
      code: json['code'],
      qrCode: json['qr_code_base64'],
      qrCodeUrl: json['qr_code_url'],
      owner: json['owner'],
      imageUrl: json['image_url'],
      imagePublicId: json['image_public_id'],
      vaccinations: json['vaccinations'] != null
          ? (json['vaccinations'] as List)
              .map((v) => AnimalVaccinationModel.fromJson(v))
              .toList()
          : null,
    );
  }

  Map<String, dynamic> toJson({bool includeId = false}) {
    final data = {
      'type': type,
      'name': name,
      'breed': breed,
      'birth_date': birthDate,
      'gender': gender,
      'weight': weight,
      'height': height,
      'color': color,
      'owner': owner,
      'code': code,
      'qr_code_base64': qrCode,
      'qr_code_url': qrCodeUrl,
      'vaccinations': vaccinations?.map((v) => v.toJson()).toList(),
    };

    if (includeId && id != null) {
      data['id'] = id;
    }

    return data;
  }

  /// Calculates age as a human-readable string based on birthDate.
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
