import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:dio/dio.dart';

class SettingsView extends StatefulWidget {
  const SettingsView({super.key});

  @override
  State<SettingsView> createState() => _SettingsViewState();
}

class _SettingsViewState extends State<SettingsView> {
  bool _loadingRoles = false;
  bool _loadingApprovedRoles = false;
  bool _requestingRole = false;
  bool _switchingRole = false;
  List<dynamic> _availableRoles = [];
  List<dynamic> _approvedRoles = [];
  String? _selectedRoleId;
  final TextEditingController _reasonController = TextEditingController();
  String? _error;

  @override
  void initState() {
    super.initState();
    _fetchAvailableRoles();
    _fetchApprovedRoles();
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
        setState(() => _error = 'Authentication token not found. Please login again.');
        return;
      }
      
      final response = await Dio().get(
        '${ApiConstant.baseUrl}/role-request/available',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      
      setState(() {
        _availableRoles = response.data['roles'] ?? [];
        _error = null;
      });
    } on DioException catch (e) {
      setState(() => _error = 'Failed to fetch available roles: ${e.message}');
    } catch (e) {
      setState(() => _error = 'Failed to fetch available roles: $e');
    } finally {
      setState(() => _loadingRoles = false);
    }
  }

  Future<void> _fetchApprovedRoles() async {
    setState(() => _loadingApprovedRoles = true);
    try {
      final token = await AuthStorage().getToken();
      final response = await Dio().get(
        '${ApiConstant.baseUrl}/role-request/approved',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      setState(() {
        _approvedRoles = response.data['roles'] ?? [];
        _error = null;
      });
    } catch (e) {
      setState(() => _error = 'Failed to fetch approved roles: $e');
    } finally {
      setState(() => _loadingApprovedRoles = false);
    }
  }

  Future<void> _requestRole() async {
    if (_selectedRoleId == null) {
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
      setState(() => _error = errorMsg);
    } catch (e) {
      setState(() => _error = 'Failed to request role: $e');
    } finally {
      setState(() => _requestingRole = false);
    }
  }

  Future<void> _switchRole(String roleId, String roleName) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Switch Role'),
        content: Text('Are you sure you want to switch to $roleName?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Switch'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    setState(() => _switchingRole = true);
    try {
      final token = await AuthStorage().getToken();
      final response = await Dio().post(
        '${ApiConstant.baseUrl}/role-request/switch',
        data: {'role_id': roleId},
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      
      if (response.data['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Role switched successfully!'),
            backgroundColor: Colors.green,
          ),
        );
        _fetchApprovedRoles();
        // Refresh user data
        if (mounted) {
          //Provider.of<UserViewModel>(context, listen: false).fetchUser();
        }
      }
    } on DioException catch (e) {
      final errorMsg = e.response?.data['error'] ?? e.message ?? 'Failed to switch role';
      setState(() => _error = errorMsg);
    } catch (e) {
      setState(() => _error = 'Failed to switch role: $e');
    } finally {
      setState(() => _switchingRole = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final userViewModel = Provider.of<UserViewModel>(context);
    final user = userViewModel.user;
    final currentRole = user?.role ?? 'No Role';
    
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: const Text('Role Management'),
        elevation: 0,
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await Future.wait([
            _fetchAvailableRoles(),
            _fetchApprovedRoles(),
          ]);
        },
        child: SingleChildScrollView(
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
                            Text(
                              currentRole,
                              style: const TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
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
                            items: _availableRoles.map<DropdownMenuItem<String>>((role) {
                              return DropdownMenuItem<String>(
                                value: role['id'].toString(),
                                child: Text(role['name'] ?? 'Unknown'),
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
                                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                  ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 24),

              // Approved Roles Section
              Card(
                elevation: 2,
                child: Padding(
                  padding: const EdgeInsets.all(20.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.swap_horiz, color: Colors.green.shade700),
                          const SizedBox(width: 8),
                          const Text(
                            'Switch to Approved Role',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      
                      if (_loadingApprovedRoles)
                        const Center(
                          child: Padding(
                            padding: EdgeInsets.all(20.0),
                            child: CircularProgressIndicator(),
                          ),
                        )
                      else if (_approvedRoles.isEmpty)
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
                                  'No approved roles available to switch.',
                                  style: TextStyle(color: Colors.grey.shade700),
                                ),
                              ),
                            ],
                          ),
                        )
                      else
                        ..._approvedRoles.map((role) {
                          final roleName = role['name'] ?? 'Unknown';
                          final roleId = role['id'].toString();
                          
                          return Container(
                            margin: const EdgeInsets.only(bottom: 12),
                            decoration: BoxDecoration(
                              border: Border.all(color: Colors.grey.shade300),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: ListTile(
                              leading: CircleAvatar(
                                backgroundColor: Colors.green.shade100,
                                child: Icon(Icons.check, color: Colors.green.shade700),
                              ),
                              title: Text(
                                roleName,
                                style: const TextStyle(
                                  fontWeight: FontWeight.w600,
                                  fontSize: 16,
                                ),
                              ),
                              subtitle: const Text('Approved'),
                              trailing: ElevatedButton(
                                onPressed: _switchingRole
                                    ? null
                                    : () => _switchRole(roleId, roleName),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.green,
                                  foregroundColor: Colors.white,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                ),
                                child: _switchingRole
                                    ? const SizedBox(
                                        height: 16,
                                        width: 16,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2,
                                          valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                        ),
                                      )
                                    : const Text('Switch'),
                              ),
                            ),
                          );
                        }).toList(),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}