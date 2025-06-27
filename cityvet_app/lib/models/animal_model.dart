class AnimalModel {
  final String type;
  final String name;
  final String? breed;
  final String birthDate;
  final String gender;
  final double weight;
  final double height;
  final String color;

  AnimalModel({
    required this.type,
    required this.name,
    required this.breed,
    required this.birthDate,
    required this.gender,
    required this.weight,
    required this.height,
    required this.color,
  });

  factory AnimalModel.fromJson(Map<String, dynamic> json) {
    return AnimalModel(
      type: json['type'],
      name: json['name'], 
      breed: json['breed'], 
      birthDate: json['birth_date'], 
      gender: json['gender'], 
      weight: json['weight'], 
      height: json['height'], 
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

}