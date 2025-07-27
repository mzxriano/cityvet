import 'package:intl/intl.dart';

class ActivityModel {
  final String reason;
  final String details;
  final String barangay;
  final DateTime date;
  final DateTime time;

  ActivityModel({
    required this.reason,
    required this.details,
    required this.barangay,
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

    return ActivityModel(
      reason: json['reason'] ?? '',
      details: json['details'] ?? '',
      barangay: json['barangay'] ?? '',
      date: parsedDate,  
      time: combinedDateTime,  
    );
  }
}
