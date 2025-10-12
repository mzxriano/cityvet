import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class ActivityDialog {
  static void show(BuildContext context, dynamic activity) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Column(
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: const BoxDecoration(
                  color: Color(0xFF8ED968),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.event,
                  color: Colors.white,
                  size: 32,
                ),
              ),
              const SizedBox(height: 12),
              Text(
                activity.reason,
                style: const TextStyle(
                  fontFamily: Config.primaryFont,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.center,
              ),
              Text(
                'Activity Details',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: Config.fontSmall,
                  color: Colors.grey[600],
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              _buildDetailRow(Icons.description_outlined, 'Details', activity.details),
              _buildDetailRow(Icons.calendar_today_outlined, 'Date', 
                  DateFormat('MMMM d, yyyy').format(activity.date)),
              _buildDetailRow(Icons.access_time_outlined, 'Time', 
                  DateFormat('h:mm a').format(activity.time)),
              _buildDetailRow(Icons.location_on_outlined, 'Location', 
                  activity.barangay.toString()),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text(
                'Close',
                style: TextStyle(fontFamily: Config.primaryFont),
              ),
            ),
          ],
        );
      },
    );
  }

  static Widget _buildDetailRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 18, color: Colors.grey),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: const TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontSmall,
                    color: Colors.grey,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                Text(
                  value,
                  style: const TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontSmall,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
