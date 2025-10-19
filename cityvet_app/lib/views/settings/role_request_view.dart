import 'package:cityvet_app/components/role.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:dio/dio.dart';

class RoleRequestView extends StatefulWidget {
  final String currentRole;
  final List<dynamic> approvedRoles;

  const RoleRequestView({
    super.key,
    required this.currentRole,
    required this.approvedRoles,
  });

  @override
  State<RoleRequestView> createState() => _RoleRequestViewState();
}

class _RoleRequestViewState extends State<RoleRequestView> {
  bool _loadingRoles = false;
  bool _requestingRole = false;
  List<dynamic> _availableRoles = [];
  String? _selectedRoleId;
  final TextEditingController _reasonController = TextEditingController();
  String? _error;

  @override
  void initState() {
    super.initState();
    _fetchAvailableRoles();
  }

  @override
  void dispose() {
    _reasonController.dispose();
    super.dispose();
  }

  Future<void> _fetchAvailableRoles() async {
    setState(() => _loadingRoles = true);
    try {
      final token = await AuthStorage().getToken();
      if (token == null || token.isEmpty) {
        if (!mounted) return;
        setState(() => _error = 'Authentication token not found. Please login again.');
        return;
      }
      final response = await Dio().get(
        '${ApiConstant.baseUrl}/role-request/available',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      if (!mounted) return;
      setState(() {
        _availableRoles = response.data['roles'] ?? [];
        _error = null;
      });
    } on DioException catch (e) {
      if (!mounted) return;
      setState(() => _error = 'Failed to fetch available roles: [${e.message}]');
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = 'Failed to fetch available roles: [${e.toString()}]');
    } finally {
      if (!mounted) return;
      setState(() => _loadingRoles = false);
    }
  }

  Future<void> _requestRole() async {
    if (_selectedRoleId == null) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select a role')),
      );
      return;
    }
    setState(() {
      _requestingRole = true;
      _error = null;
    });
    try {
      final token = await AuthStorage().getToken();
      final response = await Dio().post(
        '${ApiConstant.baseUrl}/role-request',
        data: {'role_id': _selectedRoleId, 'reason': _reasonController.text},
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      if (response.data['success'] == true) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Role request submitted successfully!'),
            backgroundColor: Colors.green,
          ),
        );
        setState(() {
          _selectedRoleId = null;
          _reasonController.clear();
        });
        _fetchAvailableRoles();
      }
    } on DioException catch (e) {
      final errorMsg = e.response?.data['error'] ?? e.message ?? 'Failed to request role';
      if (!mounted) return;
      setState(() => _error = errorMsg);
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = 'Failed to request role: $e');
    } finally {
      if (!mounted) return;
      setState(() => _requestingRole = false);
    }
  }

  Widget _getRoleDisplayName(String roleName) {
    final Map<String, String> roleDisplayNames = {
      'pet_owner': 'Pet Owner',
      'livestock_owner': 'Livestock Owner',
      'poultry_owner': 'Poultry Owner',
      'veterinarian': 'Veterinarian',
      'staff': 'Staff',
      'aew': 'AEW',
      'sub_admin': 'Sub Admin',
      'barangay_personnel': 'Barangay Personnel',
      'unknown': 'Unknown',
    };
    return Text(roleDisplayNames[roleName] ?? roleName);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: const Text('Request New Role'),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Current Role Card
            Card(
              elevation: 2,
              child: Padding(
                padding: const EdgeInsets.all(20.0),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.blue.shade100,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(
                        Icons.person,
                        color: Colors.blue.shade700,
                        size: 32,
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Current Role',
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey[600],
                            ),
                          ),
                          const SizedBox(height: 4),
                          RoleWidget()[widget.currentRole],
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 24),

            // Error Banner
            if (_error != null)
              Container(
                margin: const EdgeInsets.only(bottom: 16),
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.red.shade200),
                ),
                child: Row(
                  children: [
                    Icon(Icons.error_outline, color: Colors.red.shade700),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        _error!,
                        style: TextStyle(color: Colors.red.shade700),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close),
                      onPressed: () => setState(() => _error = null),
                      color: Colors.red.shade700,
                    ),
                  ],
                ),
              ),

            // Request New Role Section
            Card(
              elevation: 2,
              child: Padding(
                padding: const EdgeInsets.all(20.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.add_circle_outline, color: Colors.blue.shade700),
                        const SizedBox(width: 8),
                        const Text(
                          'Request Another Role',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    
                    if (_loadingRoles)
                      const Center(
                        child: Padding(
                          padding: EdgeInsets.all(20.0),
                          child: CircularProgressIndicator(),
                        ),
                      )
                    else if (_availableRoles.isEmpty)
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.grey.shade200,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Row(
                          children: [
                            Icon(Icons.info_outline, color: Colors.grey.shade600),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                'No roles available for request.',
                                style: TextStyle(color: Colors.grey.shade700),
                              ),
                            ),
                          ],
                        ),
                      )
                    else ...[
                      Container(
                        decoration: BoxDecoration(
                          border: Border.all(color: Colors.grey.shade300),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: DropdownButtonFormField<String>(
                          value: _selectedRoleId,
                          decoration: const InputDecoration(
                            labelText: 'Select Role',
                            border: InputBorder.none,
                            contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          ),
                          items: _availableRoles
                            .where((role) {
                              final roleName = role['name'];
                              final isCurrent = roleName == widget.currentRole;
                              final isApproved = widget.approvedRoles.any((r) => (r['name'] ?? '') == roleName);
                              return !isCurrent && !isApproved;
                            })
                            .map<DropdownMenuItem<String>>((role) {
                              return DropdownMenuItem<String>(
                                value: role['id'].toString(),
                                child: _getRoleDisplayName(role['name']),
                              );
                            }).toList(),
                          onChanged: (val) => setState(() => _selectedRoleId = val),
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextField(
                        controller: _reasonController,
                        maxLines: 3,
                        decoration: InputDecoration(
                          labelText: 'Reason (optional)',
                          hintText: 'Why do you need this role?',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: _requestingRole ? null : _requestRole,
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            backgroundColor: Config.primaryColor,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                          ),
                          child: _requestingRole
                              ? const SizedBox(
                                  height: 20,
                                  width: 20,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                  ),
                                )
                              : const Text(
                                  'Submit Request',
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                                ),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}