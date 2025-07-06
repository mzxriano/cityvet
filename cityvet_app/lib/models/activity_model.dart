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
    return ActivityModel(
      reason: json['reason'], 
      details: json['details'], 
      barangay: json['barangay'],
      date: DateTime.parse(json['date']), 
      time: DateTime.parse(json['time'])); 
  }
  
}