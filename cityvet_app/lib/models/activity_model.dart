import 'package:cityvet_app/models/barangay_model.dart';
import 'package:intl/intl.dart';

class ActivityModel {
  final int? id;
  final String reason;
  final String details;
  final List<BarangayModel> barangays;
  final DateTime date;
  final DateTime time;

  ActivityModel({
    this.id,
    required this.reason,
    required this.details,
    required this.barangays,
    required this.date,
    required this.time,
  });

  factory ActivityModel.fromJson(Map<String, dynamic> json) {
    final DateFormat dateFormat = DateFormat("yyyy-MM-dd");
    final DateFormat timeFormat = DateFormat("HH:mm");

    DateTime parsedDate = dateFormat.parse(json['date']);  
    DateTime parsedTime = timeFormat.parse(json['time']);  

    // Combine date and time to avoid formatting error
    DateTime combinedDateTime = DateTime(
      parsedDate.year,
      parsedDate.month,
      parsedDate.day,
      parsedTime.hour,
      parsedTime.minute,
    );

    final List<BarangayModel> parsedBarangays = (json['barangays'] as List<dynamic>?)
        ?.map((bJson) => BarangayModel.fromJson(bJson))
        .toList() ?? [];

return ActivityModel(
      id: json['id'],
      reason: json['reason'] ?? '',
      details: json['details'] ?? '',
      barangays: parsedBarangays,
      date: parsedDate,  
      time: combinedDateTime,
    );
  }
}
