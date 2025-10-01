class IncidentModel {
  final int? id;
  final String victimName;
  final int age;
  final String species;
  final String biteProvocation;
  final double latitude;
  final double longitude;
  final String locationAddress;
  final DateTime incidentTime;
  final String? remarks;
  final String? photoPath;
  final DateTime? reportedAt;
  final String? reportedBy;
  final String status; // 'pending', 'under_review', 'confirmed', 'disputed'
  final String? confirmedBy;
  final DateTime? confirmedAt;

  IncidentModel({
    this.id,
    required this.victimName,
    required this.age,
    required this.species,
    required this.biteProvocation,
    required this.latitude,
    required this.longitude,
    required this.locationAddress,
    required this.incidentTime,
    this.remarks,
    this.photoPath,
    this.reportedAt,
    this.reportedBy,
    this.status = 'pending',
    this.confirmedBy,
    this.confirmedAt,
  });

  factory IncidentModel.fromJson(Map<String, dynamic> json) {
    return IncidentModel(
      id: json['id'],
      victimName: json['victim_name'] ?? '',
      age: json['age'] ?? 0,
      species: json['species'] ?? '',
      biteProvocation: json['bite_provocation'] ?? '',
      latitude: double.tryParse(json['latitude']?.toString() ?? '0') ?? 0.0,
      longitude: double.tryParse(json['longitude']?.toString() ?? '0') ?? 0.0,
      locationAddress: json['location_address'] ?? '',
      incidentTime: json['incident_time'] != null
          ? DateTime.parse(json['incident_time'])
          : DateTime.now(),
      remarks: json['remarks'],
      photoPath: json['photo_path'],
      reportedAt: json['reported_at'] != null
          ? DateTime.parse(json['reported_at'])
          : null,
      reportedBy: json['reported_by'],
      status: json['status'] ?? 'pending',
      confirmedBy: json['confirmed_by'],
      confirmedAt: json['confirmed_at'] != null
          ? DateTime.parse(json['confirmed_at'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'victim_name': victimName,
      'age': age,
      'species': species,
      'bite_provocation': biteProvocation,
      'latitude': latitude,
      'longitude': longitude,
      'location_address': locationAddress,
      'incident_time': incidentTime.toIso8601String(),
      'remarks': remarks,
      'photo_path': photoPath,
      'reported_at': reportedAt?.toIso8601String(),
      'reported_by': reportedBy,
      'status': status,
      'confirmed_by': confirmedBy,
      'confirmed_at': confirmedAt?.toIso8601String(),
    };
  }

  IncidentModel copyWith({
    int? id,
    String? victimName,
    int? age,
    String? species,
    String? biteProvocation,
    double? latitude,
    double? longitude,
    String? locationAddress,
    DateTime? incidentTime,
    String? remarks,
    String? photoPath,
    DateTime? reportedAt,
    String? reportedBy,
    String? status,
    String? confirmedBy,
    DateTime? confirmedAt,
  }) {
    return IncidentModel(
      id: id ?? this.id,
      victimName: victimName ?? this.victimName,
      age: age ?? this.age,
      species: species ?? this.species,
      biteProvocation: biteProvocation ?? this.biteProvocation,
      latitude: latitude ?? this.latitude,
      longitude: longitude ?? this.longitude,
      locationAddress: locationAddress ?? this.locationAddress,
      incidentTime: incidentTime ?? this.incidentTime,
      remarks: remarks ?? this.remarks,
      photoPath: photoPath ?? this.photoPath,
      reportedAt: reportedAt ?? this.reportedAt,
      reportedBy: reportedBy ?? this.reportedBy,
      status: status ?? this.status,
      confirmedBy: confirmedBy ?? this.confirmedBy,
      confirmedAt: confirmedAt ?? this.confirmedAt,
    );
  }
  
  // Helper methods to check status
  bool get isPending => status == 'pending';
  bool get isUnderReview => status == 'under_review';
  bool get isConfirmed => status == 'confirmed';
  bool get isDisputed => status == 'disputed';
  
  String get statusDisplayName {
    switch (status) {
      case 'pending':
        return 'Pending Review';
      case 'under_review':
        return 'Under Review';
      case 'confirmed':
        return 'Confirmed';
      case 'disputed':
        return 'Disputed';
      default:
        return 'Unknown';
    }
  }
}
