import 'activity_model.dart';

class VaccinatedAnimalModel {
  final int id;
  final String name;
  final String type;
  final String? breed;
  final String color;
  final String gender;
  final String owner;
  final String? ownerPhone;
  final List<VaccinationRecordModel> vaccinations;

  VaccinatedAnimalModel({
    required this.id,
    required this.name,
    required this.type,
    this.breed,
    required this.color,
    required this.gender,
    required this.owner,
    this.ownerPhone,
    required this.vaccinations,
  });

  factory VaccinatedAnimalModel.fromJson(Map<String, dynamic> json) {
    return VaccinatedAnimalModel(
      id: json['id'],
      name: json['name'],
      type: json['type'],
      breed: json['breed'],
      color: json['color'],
      gender: json['gender'],
      owner: json['owner'],
      ownerPhone: json['owner_phone'],
      vaccinations: (json['vaccinations'] as List)
          .map((v) => VaccinationRecordModel.fromJson(v))
          .toList(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'type': type,
      'breed': breed,
      'color': color,
      'gender': gender,
      'owner': owner,
      'owner_phone': ownerPhone,
      'vaccinations': vaccinations.map((v) => v.toJson()).toList(),
    };
  }
}

class VaccinationRecordModel {
  final String vaccineName;
  final int dose;
  final String dateGiven;
  final String? administrator;

  VaccinationRecordModel({
    required this.vaccineName,
    required this.dose,
    required this.dateGiven,
    this.administrator,
  });

  factory VaccinationRecordModel.fromJson(Map<String, dynamic> json) {
    return VaccinationRecordModel(
      vaccineName: json['vaccine_name'],
      dose: json['dose'],
      dateGiven: json['date_given'],
      administrator: json['administrator'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'vaccine_name': vaccineName,
      'dose': dose,
      'date_given': dateGiven,
      'administrator': administrator,
    };
  }
}

class ActivityVaccinationReportModel {
  final String date;
  final int totalVaccinatedAnimals;
  final List<VaccinatedAnimalModel> vaccinatedAnimals;
  final ActivityModel? activity;

  ActivityVaccinationReportModel({
    required this.date,
    required this.totalVaccinatedAnimals,
    required this.vaccinatedAnimals,
    this.activity,
  });

  factory ActivityVaccinationReportModel.fromJson(Map<String, dynamic> json) {
    return ActivityVaccinationReportModel(
      date: json['date'],
      totalVaccinatedAnimals: json['total_vaccinated_animals'],
      vaccinatedAnimals: (json['vaccinated_animals'] as List)
          .map((v) => VaccinatedAnimalModel.fromJson(v))
          .toList(),
      activity: json['activity'] != null 
          ? ActivityModel.fromJson(json['activity']) 
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'date': date,
      'total_vaccinated_animals': totalVaccinatedAnimals,
      'vaccinated_animals': vaccinatedAnimals.map((v) => v.toJson()).toList(),
      'activity': activity != null ? {
        'reason': activity!.reason,
        'details': activity!.details,
        'barangay': activity!.barangays,
        'date': activity!.date.toIso8601String().split('T')[0],
        'time': '${activity!.time.hour.toString().padLeft(2, '0')}:${activity!.time.minute.toString().padLeft(2, '0')}',
      } : null,
    };
  }
} 