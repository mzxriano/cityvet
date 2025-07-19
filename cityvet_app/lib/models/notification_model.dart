class NotificationModel {
  final String? id;
  final String title;
  final String body;
  final bool read;
  final DateTime createdAt;

  NotificationModel({
    required this.id,
    required this.title,
    required this.body,
    required this.read,
    required this.createdAt,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: json['id'],
      title: json['title'],
      body: json['body'],
      read: json['read'] == true || json['read'] == 1,
      createdAt: DateTime.parse(json['created_at']),
    );
  }
} 