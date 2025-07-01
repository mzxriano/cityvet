class AnimalModel {
  final String type;
  final String name;
  final String? breed;
  final String? birthDate;
  final String gender;
  final double? weight;
  final double? height;
  final String color;

  AnimalModel({
    required this.type,
    required this.name,
    required this.breed,
    this.birthDate,
    required this.gender,
    this.weight,
    this.height,
    required this.color,
  });

  factory AnimalModel.fromJson(Map<String, dynamic> json) {
    return AnimalModel(
      type: json['type'],
      name: json['name'], 
      breed: json['breed'], 
      birthDate: json['birth_date'], 
      gender: json['gender'], 
      weight: json['weight'] != null ? (json['weight'] as num).toDouble() : null, 
      height: json['height'] != null ? (json['height'] as num).toDouble() : null, 
      color: json['color'],
    );
  }
  
  Map<String, dynamic> toJson() => {
    'type': type,
    'name': name,
    'breed': breed,
    'birth_date': birthDate,
    'gender': gender,
    'weight': weight,
    'height': height,
    'color': color,
  };

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
