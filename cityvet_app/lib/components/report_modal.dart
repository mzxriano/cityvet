import 'package:flutter/material.dart';
import 'package:cityvet_app/utils/config.dart';

class ReportModal extends StatefulWidget {
  final String title;
  final Function(String reason) onReport;

  const ReportModal({
    Key? key,
    required this.title,
    required this.onReport,
  }) : super(key: key);

  @override
  State<ReportModal> createState() => _ReportModalState();
}

class _ReportModalState extends State<ReportModal> {
  String? _selectedReason;
  final TextEditingController _customReasonController = TextEditingController();
  bool _isSubmitting = false;

  final List<String> _reportReasons = [
    'Spam or unwanted content',
    'Inappropriate or offensive content',
    'Harassment or bullying',
    'False information',
    'Violence or dangerous content',
    'Hate speech',
    'Adult content',
    'Copyright violation',
    'Other',
  ];

  @override
  void dispose() {
    _customReasonController.dispose();
    super.dispose();
  }

  void _handleReport() async {
    if (_selectedReason == null || 
        (_selectedReason == 'Other' && _customReasonController.text.trim().isEmpty)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select or enter a reason for reporting')),
      );
      return;
    }

    setState(() {
      _isSubmitting = true;
    });

    try {
      final reason = _selectedReason == 'Other' 
          ? _customReasonController.text.trim()
          : _selectedReason!;
      
      await widget.onReport(reason);
      
      if (mounted) {
        Navigator.of(context).pop();
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Report submitted successfully'),
            backgroundColor: Colors.green,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to submit report: $e')),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isSubmitting = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Container(
        width: double.infinity,
        constraints: const BoxConstraints(maxWidth: 400, maxHeight: 600),
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              children: [
                Icon(Icons.flag, color: Colors.red[600]),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    widget.title,
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: 18,
                      fontWeight: FontWeight.w600,
                      color: Colors.grey[800],
                    ),
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close),
                  onPressed: () => Navigator.of(context).pop(),
                  padding: EdgeInsets.zero,
                  constraints: const BoxConstraints(),
                ),
              ],
            ),
            
            const SizedBox(height: 16),
            
            Text(
              'Why are you reporting this content?',
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: 14,
                color: Colors.grey[600],
              ),
            ),
            
            const SizedBox(height: 16),
            
            // Report reasons
            Flexible(
              child: SingleChildScrollView(
                child: Column(
                  children: _reportReasons.map((reason) {
                    return RadioListTile<String>(
                      title: Text(
                        reason,
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: 14,
                        ),
                      ),
                      value: reason,
                      groupValue: _selectedReason,
                      onChanged: (value) {
                        setState(() {
                          _selectedReason = value;
                        });
                      },
                      contentPadding: EdgeInsets.zero,
                      dense: true,
                    );
                  }).toList(),
                ),
              ),
            ),
            
            // Custom reason input
            if (_selectedReason == 'Other') ...[
              const SizedBox(height: 16),
              TextField(
                controller: _customReasonController,
                decoration: InputDecoration(
                  labelText: 'Please specify',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                ),
                maxLines: 3,
                style: TextStyle(fontFamily: Config.primaryFont),
              ),
            ],
            
            const SizedBox(height: 20),
            
            // Action buttons
            Row(
              children: [
                Expanded(
                  child: TextButton(
                    onPressed: _isSubmitting ? null : () => Navigator.of(context).pop(),
                    child: Text(
                      'Cancel',
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        color: Colors.grey[600],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _isSubmitting ? null : _handleReport,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red[600],
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    child: _isSubmitting
                        ? const SizedBox(
                            height: 16,
                            width: 16,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                            ),
                          )
                        : Text(
                            'Report',
                            style: TextStyle(
                              fontFamily: Config.primaryFont,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

// Helper function to show report modal
void showReportModal(
  BuildContext context, {
  required String title,
  required Function(String reason) onReport,
}) {
  showDialog(
    context: context,
    barrierDismissible: false,
    builder: (context) => ReportModal(
      title: title,
      onReport: onReport,
    ),
  );
}
