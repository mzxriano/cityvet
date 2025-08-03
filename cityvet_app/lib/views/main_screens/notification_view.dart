import 'package:flutter/material.dart';
import 'package:cityvet_app/models/notification_model.dart';
import 'package:cityvet_app/services/api_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';

class NotificationView extends StatefulWidget {
  const NotificationView({Key? key}) : super(key: key);

  @override
  State<NotificationView> createState() => _NotificationViewState();
}

class _NotificationViewState extends State<NotificationView> {
  List<NotificationModel> notifications = [];
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    fetchNotifications();
  }

  Future<void> fetchNotifications() async {
    if (!mounted) return; // Check mounted before setState
    setState(() { isLoading = true; });
    
    final token = await AuthStorage().getToken();
    final api = ApiService();

    if(token == null) return;
    try {
      final data = await api.getNotifications(token);
      
      // Check mounted after async operation
      if (!mounted) return;
      
      setState(() {
        notifications = data.map<NotificationModel>((n) => NotificationModel.fromJson(n)).toList();
        isLoading = false;
      });
    } catch (e) {
      // Check mounted after async operation
      if (!mounted) return;
      
      setState(() { isLoading = false; });
      print(e);
      
      // Check mounted before showing SnackBar
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                Icon(Icons.error_outline, color: Colors.white, size: 20),
                SizedBox(width: 8),
                Text('Failed to load notifications'),
              ],
            ),
            backgroundColor: Colors.red.shade600,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
        );
      }
    }
  }

  Future<void> markAsRead(String? id) async {
    final token = await AuthStorage().getToken();
    final api = ApiService();

    if(token == null) return;

    try {
      await api.markNotificationAsRead(token, id);
      
      // Check mounted after async operation
      if (!mounted) return;
      
      setState(() {
        notifications = notifications.map((n) => n.id == id ? NotificationModel(
          id: n.id,
          title: n.title,
          body: n.body,
          read: true,
          createdAt: n.createdAt,
        ) : n).toList();
      });
      
      // Check mounted before showing SnackBar
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                Icon(Icons.check_circle_outline, color: Colors.white, size: 20),
                SizedBox(width: 8),
                Text('Notification marked as read'),
              ],
            ),
            backgroundColor: Colors.green.shade600,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            duration: Duration(seconds: 2),
          ),
        );
      }
    } catch (e) {
      // Check mounted before showing SnackBar
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                Icon(Icons.error_outline, color: Colors.white, size: 20),
                SizedBox(width: 8),
                Text('Failed to mark as read'),
              ],
            ),
            backgroundColor: Colors.red.shade600,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
        );
      }
    }
  }

  Future<void> markAllAsRead() async {
    final unreadNotifications = notifications.where((n) => !n.read).toList();
    if (unreadNotifications.isEmpty) return;

    for (final notification in unreadNotifications) {
      // Check mounted before each operation
      if (!mounted) break;
      await markAsRead(notification.id);
    }
  }

  String _formatTime(DateTime? dateTime) {
    if (dateTime == null) return '';
    final now = DateTime.now();
    final difference = now.difference(dateTime);

    if (difference.inMinutes < 1) {
      return 'Just now';
    } else if (difference.inHours < 1) {
      return '${difference.inMinutes}m ago';
    } else if (difference.inDays < 1) {
      return '${difference.inHours}h ago';
    } else if (difference.inDays < 7) {
      return '${difference.inDays}d ago';
    } else {
      return '${dateTime.day}/${dateTime.month}/${dateTime.year}';
    }
  }

  @override
  Widget build(BuildContext context) {
    final unreadCount = notifications.where((n) => !n.read).length;
    
    return Scaffold(
      backgroundColor: Colors.transparent,
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Notifications', style: TextStyle(fontSize: 25, fontWeight: FontWeight.w600)), 
            if (unreadCount > 0)
              Text(
                '$unreadCount unread',
                style: TextStyle(fontSize: 14, color: Colors.grey.shade600, fontWeight: FontWeight.normal),
              ),
          ],
        ),
        backgroundColor: Colors.transparent,
        foregroundColor: Colors.black87,
        elevation: 0,
        shadowColor: Colors.transparent,
        actions: [
          if (unreadCount > 0)
            TextButton(
              onPressed: markAllAsRead,
              child: Text('Mark all read', style: TextStyle(color: Colors.green.shade600, fontSize: 15)), 
            ),
          IconButton(
            icon: Icon(Icons.refresh_rounded, color: Colors.grey.shade700),
            onPressed: fetchNotifications,
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: isLoading
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(strokeWidth: 2.5, color: Colors.green.shade600),
                  SizedBox(height: 16),
                  Text(
                    'Loading notifications...',
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 16), 
                  ),
                ],
              ),
            )
          : notifications.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        padding: EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: Colors.grey.shade100,
                          shape: BoxShape.circle,
                        ),
                        child: Icon(
                          Icons.notifications_none_rounded,
                          size: 48,
                          color: Colors.grey.shade400,
                        ),
                      ),
                      SizedBox(height: 16),
                      Text(
                        'No notifications yet',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w500,
                          color: Colors.grey.shade700,
                        ),
                      ),
                      SizedBox(height: 8),
                      Text(
                        'We\'ll notify you when something happens',
                        style: TextStyle(
                          fontSize: 16,
                          color: Colors.grey.shade500,
                        ),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: fetchNotifications,
                  color: Colors.green.shade600,
                  child: ListView.separated(
                    itemCount: notifications.length,
                    separatorBuilder: (context, index) => SizedBox(height: 12), 
                    itemBuilder: (context, index) {
                      final n = notifications[index];
                      return Container(
                        decoration: BoxDecoration(
                          color: Colors.white, 
                          borderRadius: BorderRadius.circular(12),
                          border: n.read ? Border.all(color: Colors.grey.shade200, width: 1) : Border.all(color: Colors.green.shade200, width: 1.5),
                        ),
                        child: Material(
                          color: Colors.transparent,
                          child: InkWell(
                            borderRadius: BorderRadius.circular(12),
                            onTap: n.read ? null : () => markAsRead(n.id),
                            child: Padding(
                              padding: EdgeInsets.all(15),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.center,
                                children: [
                                  // Notification indicator
                                  Container(
                                    width: 10, 
                                    height: 10,
                                    margin: EdgeInsets.only(top: 4),
                                    decoration: BoxDecoration(
                                      color: n.read ? Colors.transparent : Colors.green.shade600,
                                      shape: BoxShape.circle,
                                    ),
                                  ),
                                  SizedBox(width: 14),
                                  // Content
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          n.title,
                                          style: TextStyle(
                                            fontSize: 17, 
                                            fontWeight: n.read ? FontWeight.w500 : FontWeight.w600,
                                            color: Colors.black87,
                                            height: 1.3,
                                          ),
                                        ),
                                        if (n.body.isNotEmpty) ...[
                                          SizedBox(height: 6), 
                                          Text(
                                            n.body,
                                            style: TextStyle(
                                              fontSize: 16,
                                              color: Colors.grey.shade600,
                                              height: 1.4,
                                            ),
                                            maxLines: 3,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ],
                                        if (n.createdAt != null) ...[
                                          SizedBox(height: 10),
                                          Text(
                                            _formatTime(n.createdAt),
                                            style: TextStyle(
                                              fontSize: 14, 
                                              color: Colors.grey.shade500,
                                            ),
                                          ),
                                        ],
                                      ],
                                    ),
                                  ),
                                  // Action button
                                  if (n.read)
                                    Container(
                                      margin: EdgeInsets.only(left: 8),
                                      child: InkWell(
                                        borderRadius: BorderRadius.circular(20),
                                        onTap: () => markAsRead(n.id),
                                        child: Container(
                                          padding: EdgeInsets.all(8), 
                                          decoration: BoxDecoration(
                                            color: Colors.green.shade50,
                                            shape: BoxShape.circle,
                                          ),
                                          child: Icon(
                                            Icons.check_rounded,
                                            size: 18, 
                                            color: Colors.green.shade600,
                                          ),
                                        ),
                                      ),
                                    ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}