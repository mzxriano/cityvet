import 'package:cityvet_app/models/animal_model.dart';

class AnimalArchiveModel {
  final int id;
  final int animalId;
  final int userId;
  final String archiveType; // 'deceased' or 'deleted'
  final String? reason;
  final String? notes;
  final String archiveDate;
  final String archivedAt;
  final String archivedBy;
  final AnimalModel animal; // The animal data from snapshot

  AnimalArchiveModel({
    required this.id,
    required this.animalId,
    required this.userId,
    required this.archiveType,
    this.reason,
    this.notes,
    required this.archiveDate,
    required this.archivedAt,
    required this.archivedBy,
    required this.animal,
  });

  factory AnimalArchiveModel.fromJson(Map<String, dynamic> json) {
    return AnimalArchiveModel(
      id: json['archive_id'],
      animalId: json['animal']['id'],
      userId: json['user_id'] ?? 0,
      archiveType: json['archive_type'],
      reason: json['reason'],
      notes: json['notes'],
      archiveDate: json['archive_date'],
      archivedAt: json['archived_at'],
      archivedBy: json['archived_by'] ?? 'Unknown',
      animal: AnimalModel(
        id: json['animal']['id'],
        type: json['animal']['type'],
        name: json['animal']['name'],
        breed: json['animal']['breed'] ?? '',
        color: json['animal']['color'] ?? '',
        gender: json['animal']['gender'],
        birthDate: json['animal']['birth_date'],
        weight: json['animal']['weight']?.toDouble(),
        height: json['animal']['height']?.toDouble(),
        uniqueSpot: json['animal']['unique_spot'],
        knownConditions: json['animal']['known_conditions'],
        owner: json['animal']['owner'],
        code: json['animal']['code'],
        imageUrl: json['animal']['image_url'],
        imagePublicId: json['animal']['image_public_id'],
      ),
    );
  }

  /// Check if this is a deceased archive
  bool get isDeceased => archiveType == 'deceased';

  /// Check if this is a deleted archive
  bool get isDeleted => archiveType == 'deleted';

  /// Get formatted archive date
  String get formattedArchiveDate {
    try {
      final date = DateTime.parse(archiveDate);
      return '${date.day}/${date.month}/${date.year}';
    } catch (e) {
      return archiveDate;
    }
  }

  /// Get formatted archived timestamp
  String get formattedArchivedAt {
    try {
      final date = DateTime.parse(archivedAt);
      return '${date.day}/${date.month}/${date.year} ${date.hour}:${date.minute.toString().padLeft(2, '0')}';
    } catch (e) {
      return archivedAt;
    }
  }
}
