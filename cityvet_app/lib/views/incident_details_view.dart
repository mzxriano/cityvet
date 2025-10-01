import 'package:cityvet_app/models/incident_model.dart';
import 'package:cityvet_app/services/incident_service.dart';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class IncidentDetailsView extends StatefulWidget {
  final IncidentModel incident;
  final bool canManageStatus;

  const IncidentDetailsView({
    super.key,
    required this.incident,
    this.canManageStatus = false,
  });

  @override
  State<IncidentDetailsView> createState() => _IncidentDetailsViewState();
}

class _IncidentDetailsViewState extends State<IncidentDetailsView> {
  final IncidentService _incidentService = IncidentService();
  bool _isUpdating = false;
  late IncidentModel _currentIncident;

  @override
  void initState() {
    super.initState();
    _currentIncident = widget.incident;
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);
    
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        backgroundColor: const Color(0xFF8ED968),
        foregroundColor: Colors.white,
        title: const Text(
          'Incident Details',
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontWeight: FontWeight.w600,
          ),
        ),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Status Card
            _buildStatusCard(),
            const SizedBox(height: 16),
            
            // Victim Information Card
            _buildVictimInfoCard(),
            const SizedBox(height: 16),
            
            // Incident Details Card
            _buildIncidentDetailsCard(),
            const SizedBox(height: 16),
            
            // Location Card
            _buildLocationCard(),
            const SizedBox(height: 16),
            
            // Photo Card (if available)
            if (_currentIncident.photoPath != null) ...[
              _buildPhotoCard(),
              const SizedBox(height: 16),
            ],
            
            // Action Buttons (if user can manage status)
            if (widget.canManageStatus && (_currentIncident.isPending || _currentIncident.status == 'under_review'))
              _buildActionButtons(),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusCard() {
    Color statusColor;
    IconData statusIcon;
    
    switch (_currentIncident.status) {
      case 'pending':
        statusColor = Colors.orange;
        statusIcon = Icons.pending;
        break;
      case 'under_review':
        statusColor = Colors.blue;
        statusIcon = Icons.search;
        break;
      case 'confirmed':
        statusColor = Colors.red;
        statusIcon = Icons.check_circle;
        break;
      case 'disputed':
        statusColor = Colors.purple;
        statusIcon = Icons.report_problem;
        break;
      default:
        statusColor = Colors.grey;
        statusIcon = Icons.help;
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.1),
            spreadRadius: 1,
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: statusColor.withOpacity(0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(
              statusIcon,
              color: statusColor,
              size: 32,
            ),
          ),
          const SizedBox(height: 12),
          Text(
            _currentIncident.statusDisplayName,
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontMedium,
              fontWeight: FontWeight.w600,
              color: statusColor,
            ),
          ),
          if (_currentIncident.confirmedBy != null) ...[
            const SizedBox(height: 8),
            Text(
              'Confirmed by: ${_currentIncident.confirmedBy}',
              style: const TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontSmall,
                color: Colors.grey,
              ),
            ),
          ],
          if (_currentIncident.confirmedAt != null) ...[
            const SizedBox(height: 4),
            Text(
              'On: ${DateFormat('MMM d, yyyy • h:mm a').format(_currentIncident.confirmedAt!)}',
              style: const TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontSmall,
                color: Colors.grey,
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildVictimInfoCard() {
    return _buildInfoCard(
      title: 'Victim Information',
      icon: Icons.person,
      children: [
        _buildDetailRow('Name', _currentIncident.victimName),
        _buildDetailRow('Age', '${_currentIncident.age} years old'),
        _buildDetailRow('Species', _currentIncident.species),
      ],
    );
  }

  Widget _buildIncidentDetailsCard() {
    return _buildInfoCard(
      title: 'Incident Details',
      icon: Icons.report,
      children: [
        _buildDetailRow('Date & Time', DateFormat('MMM d, yyyy • h:mm a').format(_currentIncident.incidentTime)),
        _buildDetailRow('Bite Provocation', _currentIncident.biteProvocation),
        if (_currentIncident.remarks != null && _currentIncident.remarks!.isNotEmpty)
          _buildDetailRow('Remarks', _currentIncident.remarks!),
        if (_currentIncident.reportedBy != null)
          _buildDetailRow('Reported By', _currentIncident.reportedBy!),
        if (_currentIncident.reportedAt != null)
          _buildDetailRow('Reported At', DateFormat('MMM d, yyyy • h:mm a').format(_currentIncident.reportedAt!)),
      ],
    );
  }

  Widget _buildLocationCard() {
    return _buildInfoCard(
      title: 'Location',
      icon: Icons.location_on,
      children: [
        _buildDetailRow('Address', _currentIncident.locationAddress),
        _buildDetailRow('Coordinates', '${_currentIncident.latitude.toStringAsFixed(6)}, ${_currentIncident.longitude.toStringAsFixed(6)}'),
      ],
    );
  }

  Widget _buildPhotoCard() {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.1),
            spreadRadius: 1,
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                const Icon(Icons.photo, color: Color(0xFF8ED968)),
                const SizedBox(width: 8),
                const Text(
                  'Photo Evidence',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
          ClipRRect(
            borderRadius: const BorderRadius.vertical(bottom: Radius.circular(12)),
            child: Image.network(
              'http://${ApiConstant.baseIp}:8000/storage/${_currentIncident.photoPath}',
              width: double.infinity,
              fit: BoxFit.cover,
              errorBuilder: (context, error, stackTrace) {
                return Container(
                  height: 200,
                  color: Colors.grey[200],
                  child: const Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.error, color: Colors.grey, size: 48),
                        SizedBox(height: 8),
                        Text('Failed to load image'),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoCard({
    required String title,
    required IconData icon,
    required List<Widget> children,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.1),
            spreadRadius: 1,
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: const Color(0xFF8ED968)),
              const SizedBox(width: 8),
              Text(
                title,
                style: const TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: Config.fontMedium,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          ...children,
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              label,
              style: const TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontSmall,
                color: Colors.grey,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontSmall,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionButtons() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.1),
            spreadRadius: 1,
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            _getActionTitle(),
            style: const TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontMedium,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 16),
          ..._buildStatusSpecificActions(),
          if (_isUpdating) ...[
            const SizedBox(height: 16),
            const Center(
              child: CircularProgressIndicator(),
            ),
          ],
        ],
      ),
    );
  }

  String _getActionTitle() {
    switch (_currentIncident.status) {
      case 'pending':
        return 'Start Investigation';
      case 'under_review':
        return 'Complete Investigation';
      case 'confirmed':
        return 'Incident Confirmed';
      case 'disputed':
        return 'Incident Disputed';
      default:
        return 'Review Actions';
    }
  }

  List<Widget> _buildStatusSpecificActions() {
    switch (_currentIncident.status) {
      case 'pending':
        // Barangay personnel can start investigation
        return [
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: _isUpdating ? null : () => _updateStatus('under_review'),
              icon: const Icon(Icons.search),
              label: const Text('Start Investigation'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.blue,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
            ),
          ),
        ];

      case 'under_review':
        // After investigation, can confirm or dispute
        return [
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: _isUpdating ? null : () => _updateStatus('confirmed'),
              icon: const Icon(Icons.check_circle),
              label: const Text('Confirm Incident'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
            ),
          ),
          const SizedBox(height: 8),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: _isUpdating ? null : () => _updateStatus('disputed'),
              icon: const Icon(Icons.report_problem),
              label: const Text('Dispute Incident'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.purple,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
            ),
          ),
        ];
      

      
      case 'confirmed':
        // No actions available - incident is final
        return [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.green.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.green.withOpacity(0.3)),
            ),
            child: Row(
              children: [
                Icon(Icons.check_circle, color: Colors.green[700]),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Incident Confirmed',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontWeight: FontWeight.w600,
                          color: Colors.green[700],
                        ),
                      ),
                      Text(
                        'This incident is now visible to all users',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
                          color: Colors.green[600],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ];
      
      case 'disputed':
        // No actions available - incident is disputed
        return [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.purple.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.purple.withOpacity(0.3)),
            ),
            child: Row(
              children: [
                Icon(Icons.report_problem, color: Colors.purple[700]),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Incident Disputed',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontWeight: FontWeight.w600,
                          color: Colors.purple[700],
                        ),
                      ),
                      Text(
                        'This incident requires further investigation',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
                          color: Colors.purple[600],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ];
      
      default:
        return [];
    }
  }

  Future<void> _updateStatus(String newStatus) async {
    // Show confirmation dialog
    String actionText;
    String confirmText;
    
    switch (newStatus) {
      case 'under_review':
        actionText = 'put this incident under review';
        confirmText = 'This will move the incident to review status.';
        break;
      case 'confirmed':
        actionText = 'confirm this incident';
        confirmText = 'This will mark the incident as confirmed and notify relevant authorities.';
        break;
      case 'disputed':
        actionText = 'dispute this incident';
        confirmText = 'This will mark the incident as disputed due to lack of evidence or false information.';
        break;
      default:
        return;
    }

    bool? confirmed = await showDialog<bool>(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: Text('Confirm Action', style: TextStyle(fontFamily: Config.primaryFont)),
          content: Text(
            'Are you sure you want to $actionText?\n\n$confirmText',
            style: const TextStyle(fontFamily: Config.primaryFont),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              child: const Text('Cancel', style: TextStyle(fontFamily: Config.primaryFont)),
            ),
            ElevatedButton(
              onPressed: () => Navigator.of(context).pop(true),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF8ED968),
              ),
              child: const Text('Confirm', style: TextStyle(fontFamily: Config.primaryFont)),
            ),
          ],
        );
      },
    );

    if (confirmed != true) return;

    setState(() {
      _isUpdating = true;
    });

    try {
      print('🔄 Updating incident ${_currentIncident.id} to status: $newStatus');
      
      final result = await _incidentService.updateIncidentStatus(
        incidentId: _currentIncident.id!,
        status: newStatus,
      );

      print('📊 Update result: $result');

      if (result['success']) {
        // Fetch updated incident details
        final detailsResult = await _incidentService.getIncidentDetails(_currentIncident.id!);
        if (detailsResult['success']) {
          setState(() {
            _currentIncident = detailsResult['data'];
          });
        }

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(result['message'] ?? 'Status updated successfully'),
              backgroundColor: const Color(0xFF8ED968),
            ),
          );
        }
      } else {
        print('❌ Update failed: ${result['message']}');
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(result['message'] ?? 'Failed to update status'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    } catch (e) {
      print('💥 Exception during update: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error: ${e.toString()}'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isUpdating = false;
        });
      }
    }
  }
}
