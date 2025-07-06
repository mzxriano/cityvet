class BarangayModel {
  final int id;
  final String name;

  BarangayModel({
    required this.id,
    required this.name,
  });

  factory BarangayModel.fromJson(Map<String, dynamic> json){
    return BarangayModel(
      id: json['id'],
      name: json['name']
    );
  }
}