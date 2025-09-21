import 'package:flutter/material.dart';
import 'package:cityvet_app/models/notification_model.dart';
import 'package:cityvet_app/services/api_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:intl/intl.dart';

class NotificationPreviewPage extends StatefulWidget {
  final NotificationModel notification;

  const NotificationPreviewPage({
    super.key,
    required this.notification,
  });

  @override
  State<NotificationPreviewPage> createState() => _NotificationPreviewPageState();
}

class _NotificationPreviewPageState extends State<NotificationPreviewPage> {
  late NotificationModel currentNotification;
  bool isMarkingAsRead = false;

  @override
  void initState() {
    super.initState();
    currentNotification = widget.notification;
    
    // Automatically mark as read when page opens if it's unread
    if (!currentNotification.read) {
      _markAsRead();
    }
  }

  Future<void> _markAsRead() async {
    if (isMarkingAsRead) return;
    
    setState(() {
      isMarkingAsRead = true;
    });

    try {
      final token = await AuthStorage().getToken();
      if (token == null) return;

      final api = ApiService();
      await api.markNotificationAsRead(token, currentNotification.id);
      
      // Update the local state
      setState(() {
        currentNotification = NotificationModel(
          id: currentNotification.id,
          title: currentNotification.title,
          body: currentNotification.body,
          read: true,
          createdAt: currentNotification.createdAt,
        );
      });
    } catch (e) {
      print('Error marking notification as read: $e');
    } finally {
      setState(() {
        isMarkingAsRead = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: const Text(
          'Notification',
          style: TextStyle(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.green[600],
        foregroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios),
          onPressed: () {
            // Return true if notification was marked as read
            final wasMarkedAsRead = currentNotification.read && !widget.notification.read;
            Navigator.of(context).pop(wasMarkedAsRead);
          },
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Status indicator
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: currentNotification.read 
                    ? Colors.grey[100] 
                    : Colors.green[50],
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: currentNotification.read 
                      ? Colors.grey[300]! 
                      : Colors.green[200]!,
                ),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    currentNotification.read 
                        ? Icons.mark_email_read_outlined 
                        : Icons.mark_email_unread_outlined,
                    size: 16,
                    color: currentNotification.read 
                        ? Colors.grey[600] 
                        : Colors.green[600],
                  ),
                  const SizedBox(width: 6),
                  Text(
                    currentNotification.read ? 'Read' : 'Unread',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w500,
                      color: currentNotification.read 
                          ? Colors.grey[600] 
                          : Colors.green[600],
                    ),
                  ),
                ],
              ),
            ),
            
            const SizedBox(height: 20),
            
            // Main notification card
            Container(
              width: double.infinity,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.05),
                    blurRadius: 10,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Notification icon and timestamp
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: Colors.green[50],
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(
                            Icons.notifications_active,
                            color: Colors.green[600],
                            size: 24,
                          ),
                        ),
                        const Spacer(),
                        Text(
                          _formatDateTime(currentNotification.createdAt),
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey[500],
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                    
                    const SizedBox(height: 20),
                    
                    // Title
                    Text(
                      currentNotification.title,
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w700,
                        color: Colors.black87,
                        height: 1.2,
                      ),
                    ),
                    
                    const SizedBox(height: 16),
                    
                    // Body content
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.grey[50],
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.grey[200]!),
                      ),
                      child: Text(
                        currentNotification.body,
                        style: const TextStyle(
                          fontSize: 16,
                          color: Colors.black87,
                          height: 1.5,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
    super.dispose();
  }

  String _formatDateTime(DateTime dateTime) {
    final now = DateTime.now();
    final difference = now.difference(dateTime);

    if (difference.inDays == 0) {
      return DateFormat('h:mm a').format(dateTime);
    } else if (difference.inDays == 1) {
      return 'Yesterday';
    } else if (difference.inDays < 7) {
      return DateFormat('EEEE').format(dateTime);
    } else {
      return DateFormat('MMM d').format(dateTime);
    }
  }
}
