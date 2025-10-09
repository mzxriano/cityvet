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
  final String? uniqueSpot;
  final String? knownConditions;
  final String? code;
  final String? qrCode;
  final String? qrCodeUrl;
  final String? imageUrl;
  final String? imagePublicId;
  final List<AnimalVaccinationModel>? vaccinations;
  // Removed status fields - now handled by archive system

  AnimalModel({
    this.id, 
    required this.type,
    required this.name,
    required this.breed,
    required this.color,
    required this.gender,
    this.uniqueSpot,
    this.knownConditions,
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
      uniqueSpot: json['unique_spot'],
      knownConditions: json['known_conditions'],
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
      'unique_spot': uniqueSpot,
      'known_conditions': knownConditions,
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

  /// Since we're using archive system, animals are always "alive" in main database

  /// Creates a copy of this animal with updated fields
  AnimalModel copyWith({
    int? id,
    String? type,
    String? name,
    String? breed,
    String? birthDate,
    String? gender,
    double? weight,
    double? height,
    String? color,
    String? uniqueSpot,
    String? knownConditions,
    String? owner,
    String? code,
    String? qrCode,
    String? qrCodeUrl,
    String? imageUrl,
    String? imagePublicId,
    List<AnimalVaccinationModel>? vaccinations,
  }) {
    return AnimalModel(
      id: id ?? this.id,
      type: type ?? this.type,
      name: name ?? this.name,
      breed: breed ?? this.breed,
      birthDate: birthDate ?? this.birthDate,
      gender: gender ?? this.gender,
      weight: weight ?? this.weight,
      height: height ?? this.height,
      color: color ?? this.color,
      uniqueSpot: uniqueSpot ?? this.uniqueSpot,
      knownConditions: knownConditions ?? this.knownConditions,
      owner: owner ?? this.owner,
      code: code ?? this.code,
      qrCode: qrCode ?? this.qrCode,
      qrCodeUrl: qrCodeUrl ?? this.qrCodeUrl,
      imageUrl: imageUrl ?? this.imageUrl,
      imagePublicId: imagePublicId ?? this.imagePublicId,
      vaccinations: vaccinations ?? this.vaccinations,
    );
  }
}
