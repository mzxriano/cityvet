import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/views/login_view.dart';
import 'package:cityvet_app/views/settings/role_request_view.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:dio/dio.dart';
import 'package:cityvet_app/components/role.dart';

class SettingsView extends StatefulWidget {
  const SettingsView({super.key});

  @override
  State<SettingsView> createState() => _SettingsViewState();
}

class _SettingsViewState extends State<SettingsView> {
  bool _loadingApprovedRoles = false;
  bool _switchingRole = false;
  List<dynamic> _approvedRoles = [];
  String? _error;

  bool _isLoading = false;

  final TextEditingController _currentPasswordController = TextEditingController();
  final TextEditingController _newPasswordController = TextEditingController();
  final TextEditingController _confirmPasswordController = TextEditingController();
  bool _showCurrentPassword = false;
  bool _showNewPassword = false;
  bool _showConfirmPassword = false;

  @override
  void initState() {
    super.initState();
    _fetchApprovedRoles();
  }

  @override
  void dispose() {
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _fetchApprovedRoles() async {
    setState(() => _loadingApprovedRoles = true);
    try {
      final token = await AuthStorage().getToken();
      final response = await Dio().get(
        '${ApiConstant.baseUrl}/role-request/approved',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      if (!mounted) return;
      setState(() {
        _approvedRoles = response.data['roles'] ?? [];
        _error = null;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = 'Failed to fetch approved roles: $e');
    } finally {
      if (!mounted) return;
      setState(() => _loadingApprovedRoles = false);
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
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Role switched successfully!'),
            backgroundColor: Colors.green,
          ),
        );
        await _fetchApprovedRoles();
        if (mounted) {
          await Provider.of<UserViewModel>(context, listen: false).fetchUser();
          if (Navigator.canPop(context)) {
            Navigator.pop(context);
          }
        }
      }
    } on DioException catch (e) {
      final errorMsg = e.response?.data['error'] ?? e.message ?? 'Failed to switch role';
      if (!mounted) return;
      setState(() => _error = errorMsg);
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = 'Failed to switch role: $e');
    } finally {
      if (!mounted) return;
      setState(() => _switchingRole = false);
    }
  }

  void _showChangePasswordModal() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
        child: SingleChildScrollView(
          child: Container(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Change Password',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close),
                      onPressed: () => Navigator.pop(context),
                    ),
                  ],
                ),
                const SizedBox(height: 20),
                TextField(
                  controller: _currentPasswordController,
                  obscureText: !_showCurrentPassword,
                  decoration: InputDecoration(
                    labelText: 'Current Password',
                    hintText: 'Enter your current password',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                    suffixIcon: IconButton(
                      icon: Icon(
                        _showCurrentPassword
                            ? Icons.visibility
                            : Icons.visibility_off,
                      ),
                      onPressed: () {
                        setState(() => _showCurrentPassword = !_showCurrentPassword);
                      },
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _newPasswordController,
                  obscureText: !_showNewPassword,
                  decoration: InputDecoration(
                    labelText: 'New Password',
                    hintText: 'Enter your new password',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                    suffixIcon: IconButton(
                      icon: Icon(
                        _showNewPassword
                            ? Icons.visibility
                            : Icons.visibility_off,
                      ),
                      onPressed: () {
                        setState(() => _showNewPassword = !_showNewPassword);
                      },
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _confirmPasswordController,
                  obscureText: !_showConfirmPassword,
                  decoration: InputDecoration(
                    labelText: 'Confirm Password',
                    hintText: 'Confirm your new password',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                    suffixIcon: IconButton(
                      icon: Icon(
                        _showConfirmPassword
                            ? Icons.visibility
                            : Icons.visibility_off,
                      ),
                      onPressed: () {
                        setState(() => _showConfirmPassword = !_showConfirmPassword);
                      },
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Password change coming soon!')),
                      );
                    },
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      backgroundColor: Config.primaryColor,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    child: const Text(
                      'Change Password',
                      style: TextStyle(fontSize: Config.fontMedium, fontWeight: Config.fontW600, color: Colors.white),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // Added confirmation dialog before logout
  Future<void> _showLogoutConfirmation() async {
    final bool? shouldLogout = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          title: Row(
            children: [
              Icon(
                Icons.logout,
                color: Config.primaryColor,
                size: 24,
              ),
              const SizedBox(width: 12),
              Text(
                'Confirm Logout',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                  color: Config.primaryColor,
                ),
              ),
            ],
          ),
          content: Text(
            'Are you sure you want to logout? You will need to sign in again to access your account.',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: 16,
              color: Colors.grey[700],
              height: 1.4,
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              style: TextButton.styleFrom(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              child: Text(
                'Cancel',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: 16,
                  fontWeight: FontWeight.w500,
                  color: Colors.grey[600],
                ),
              ),
            ),
            ElevatedButton(
              onPressed: () => Navigator.of(context).pop(true),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red[600],
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                elevation: 0,
              ),
              child: Text(
                'Logout',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        );
      },
    );

    // If user confirmed logout, proceed with logout
    if (shouldLogout == true) {
      await _handleLogout();
    }
  }

  Future<void> _handleLogout() async {
    if (_isLoading) return;

    setState(() => _isLoading = true);

    try {
      final storage = AuthStorage();
      final token = await storage.getToken();

      if (token == null) {
        _showMessage('No token found. Please log in again.');
        return;
      }

      final response = await Dio().post(
        '${ApiConstant.baseUrl}/user/logout',
        options: Options(
          headers: {'Authorization': 'Bearer $token'},
        ),
      );

      if (response.statusCode == 200) {
        await storage.deleteToken();
        
        if (mounted) {
          _showMessage(response.data['message'] ?? 'Logged out successfully');
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (_) => const LoginView()),
          );
        }
      } else {
        _showMessage(response.data['error'] ?? 'Unknown error occurred.');
      }
    } catch (e) {
      _showMessage('Failed to log out: ${e.toString()}');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _showMessage(String message) {
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(message)),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final userViewModel = Provider.of<UserViewModel>(context);
    final user = userViewModel.user;
    final currentRole = user?.role ?? 'No Role';

    List<dynamic> switchableRoles = List.from(_approvedRoles);
    if (user != null && user.role != null && !_approvedRoles.any((r) => (r['name'] ?? '').toString() == user.role.toString())) {
      switchableRoles.insert(0, {
        'id': user.role,
        'name': user.role,
        'fromCurrent': true,
      });
    }

    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: const Text('Settings'),
        elevation: 0,
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await _fetchApprovedRoles();
        },
        child: Column(
          children: [
            Expanded(
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [

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

              // Request New Role Card
              Card(
                elevation: 2,
                child: Padding(
                  padding: const EdgeInsets.all(20.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.add_circle_outline, color: Config.primaryColor),
                          const SizedBox(width: 8),
                          const Text(
                            'Request New Role',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text(
                        'Need additional access? Request a new role to access different features.',
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey[600],
                        ),
                      ),
                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) => RoleRequestView(
                                  currentRole: currentRole,
                                  approvedRoles: _approvedRoles,
                                ),
                              ),
                            );
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Config.primaryColor,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                          ),
                          child: const Text(
                            'Request Role',
                            style: TextStyle(fontSize: Config.fontMedium, fontWeight: Config.fontW600, color: Colors.white),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 24),

              // Account Settings Section
              Card(
                elevation: 2,
                child: Padding(
                  padding: const EdgeInsets.all(20.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.lock, color: Config.primaryColor),
                          const SizedBox(width: 8),
                          const Text(
                            'Account Password',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: _showChangePasswordModal,
                          icon: const Icon(Icons.password, color: Colors.white),
                          label: const Text('Change Password', style: TextStyle(
                            fontSize: Config.fontMedium,
                            fontWeight: Config.fontW600,
                            color: Colors.white,
                          ),),
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            backgroundColor: Config.primaryColor,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 24),

              // Switch Role Section
              Card(
                elevation: 2,
                child: Padding(
                  padding: const EdgeInsets.all(20.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.swap_horiz, color: Config.primaryColor),
                          const SizedBox(width: 8),
                          const Text(
                            'Switch Role',
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
                        ...switchableRoles.map((role) {
                          final roleName = role['name'] ?? 'Unknown';
                          final roleId = (role['id'] ?? role['name']).toString();
                          final isCurrent = role['fromCurrent'] == true || roleName == currentRole;
                          return Container(
                            margin: const EdgeInsets.only(bottom: 12),
                            decoration: BoxDecoration(
                              border: Border.all(color: Colors.grey.shade300),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: ListTile(
                              leading: CircleAvatar(
                                backgroundColor: isCurrent ? Colors.green.shade100 : Config.primaryColor,
                                child: Icon(
                                  isCurrent ? Icons.check : Icons.person,
                                  color: Colors.green.shade700,
                                ),
                              ),
                              title: Flexible(
                                child: Row(
                                  children: [
                                    RoleWidget()[roleName],
                                  ],
                                ),
                              ),
                              subtitle: !isCurrent ? const Text('Approved') : null,
                              trailing: isCurrent
                                  ? const Text('Active', style: TextStyle(color: Colors.blue, fontWeight: FontWeight.bold))
                                  : ElevatedButton(
                                      onPressed: _switchingRole
                                          ? null
                                          : () => _switchRole(roleId, roleName),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Config.primaryColor,
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

              const SizedBox(height: 24),
                  ],
                ),
              ),
            ),
            // Logout Button - Pinned to Bottom
            Container(
              padding: const EdgeInsets.all(16.0),
              decoration: BoxDecoration(
                color: Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 8,
                    offset: const Offset(0, -2),
                  ),
                ],
              ),
              child: SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: _showLogoutConfirmation,
                  icon: const Icon(Icons.logout, color: Colors.white),
                  label: const Text('Logout', style: TextStyle(
                    fontSize: Config.fontMedium,
                    fontWeight: Config.fontW600,
                    color: Colors.white,
                  ),),
                  style: ElevatedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    backgroundColor: Colors.red,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}