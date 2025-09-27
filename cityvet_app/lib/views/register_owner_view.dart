import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:cityvet_app/utils/auth_storage.dart';

class RegisterOwnerView extends StatefulWidget {
  const RegisterOwnerView({super.key});

  @override
  State<RegisterOwnerView> createState() => _RegisterOwnerViewState();
}

class _RegisterOwnerViewState extends State<RegisterOwnerView> {
  final _formKey = GlobalKey<FormState>();
  final _dio = Dio();
  bool _isLoading = false;

  // Form controllers
  final _firstNameController = TextEditingController();
  final _lastNameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _streetController = TextEditingController();
  final _birthDateController = TextEditingController();

  // Selected values
  String? _selectedGender;
  String? _selectedRole;
  String? _selectedBarangay;
  DateTime? _selectedBirthDate;

  // Data lists
  List<Map<String, dynamic>> _barangays = [];

  final List<String> _genders = ['male', 'female', 'other'];
  final List<Map<String, String>> _roles = [
    {'value': 'pet_owner', 'label': 'Pet Owner'},
    {'value': 'livestock_owner', 'label': 'Livestock Owner'},
    {'value': 'poultry_owner', 'label': 'Poultry Owner'},
  ];

  @override
  void initState() {
    super.initState();
    _fetchBarangays();
  }

  @override
  void dispose() {
    _firstNameController.dispose();
    _lastNameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _streetController.dispose();
    _birthDateController.dispose();
    super.dispose();
  }

  Future<void> _fetchBarangays() async {
    try {
      final token = await AuthStorage().getToken();
      if (token == null) return;

      final response = await _dio.get(
        '${ApiConstant.baseUrl}/barangays',
        options: Options(
          headers: {'Authorization': 'Bearer $token'},
        ),
      );

      if (response.statusCode == 200 && mounted) {
        setState(() {
          _barangays = List<Map<String, dynamic>>.from(response.data['data'] ?? []);
        });
      }
    } catch (e) {
      if (mounted) {
        _showMessage('Failed to load barangays: ${e.toString()}');
      }
    }
  }

  Future<void> _selectBirthDate() async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _selectedBirthDate ?? DateTime(1990),
      firstDate: DateTime(1900),
      lastDate: DateTime.now(),
    );

    if (picked != null && picked != _selectedBirthDate) {
      setState(() {
        _selectedBirthDate = picked;
        _birthDateController.text = "${picked.year}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}";
      });
    }
  }

  Future<void> _registerOwner() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedBirthDate == null) {
      _showMessage('Please select birth date');
      return;
    }
    if (_selectedGender == null) {
      _showMessage('Please select gender');
      return;
    }
    if (_selectedRole == null) {
      _showMessage('Please select owner type');
      return;
    }
    if (_selectedBarangay == null) {
      _showMessage('Please select barangay');
      return;
    }

    setState(() => _isLoading = true);

    try {
      final token = await AuthStorage().getToken();
      if (token == null) {
        _showMessage('Authentication token not found');
        return;
      }

      final data = {
        'first_name': _firstNameController.text.trim(),
        'last_name': _lastNameController.text.trim(),
        'email': _emailController.text.trim(),
        'phone_number': _phoneController.text.trim(),
        'password': _passwordController.text,
        'birth_date': _birthDateController.text,
        'gender': _selectedGender,
        'role': _selectedRole,
        'barangay_id': _selectedBarangay,
        'street': _streetController.text.trim(),
      };

      final response = await _dio.post(
        '${ApiConstant.baseUrl}/register-owner',
        data: data,
        options: Options(
          headers: {
            'Authorization': 'Bearer $token',
            'Content-Type': 'application/json',
          },
        ),
      );

      if (response.statusCode == 201 && mounted) {
        _showSuccessDialog();
      }
    } catch (e) {
      if (mounted) {
        if (e is DioException && e.response?.data != null) {
          final errorData = e.response!.data;
          if (errorData['errors'] != null) {
            final errors = errorData['errors'] as Map<String, dynamic>;
            final firstError = errors.values.first;
            _showMessage(firstError is List ? firstError.first : firstError.toString());
          } else {
            _showMessage(errorData['message'] ?? 'Registration failed');
          }
        } else {
          _showMessage('Registration failed: ${e.toString()}');
        }
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _showSuccessDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Icon(Icons.check_circle, color: Colors.green[600], size: 28),
            const SizedBox(width: 12),
            const Text('Success!'),
          ],
        ),
        content: const Text('Animal owner registered successfully!'),
        actions: [
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop(); // Close dialog
              Navigator.of(context).pop(); // Go back to previous screen
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Config.primaryColor,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            child: const Text('OK', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  void _showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: message.contains('Success') || message.contains('successfully') 
            ? Colors.green 
            : Colors.red,
      ),
    );
  }

  void _clearForm() {
    _firstNameController.clear();
    _lastNameController.clear();
    _emailController.clear();
    _phoneController.clear();
    _passwordController.clear();
    _confirmPasswordController.clear();
    _streetController.clear();
    _birthDateController.clear();
    setState(() {
      _selectedGender = null;
      _selectedRole = null;
      _selectedBarangay = null;
      _selectedBirthDate = null;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Register Animal Owner',
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontSize: Config.fontMedium,
            fontWeight: FontWeight.w600,
          ),
        ),
        backgroundColor: Config.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          TextButton(
            onPressed: _clearForm,
            child: const Text(
              'Clear',
              style: TextStyle(color: Colors.white),
            ),
          ),
        ],
      ),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Config.primaryColor.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.person_add_outlined,
                      color: Config.primaryColor,
                      size: 28,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'New Animal Owner Registration',
                            style: TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: 18,
                              fontWeight: FontWeight.w600,
                              color: Config.primaryColor,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Create a new account for pet, livestock, or poultry owners',
                            style: TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: 14,
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              
              const SizedBox(height: 24),

              // Personal Information Section
              _buildSectionHeader('Personal Information'),
              const SizedBox(height: 16),

              Row(
                children: [
                  Expanded(
                    child: _buildTextField(
                      controller: _firstNameController,
                      label: 'First Name',
                      icon: Icons.person_outline,
                      validator: (value) {
                        if (value?.isEmpty ?? true) return 'First name is required';
                        return null;
                      },
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: _buildTextField(
                      controller: _lastNameController,
                      label: 'Last Name',
                      icon: Icons.person_outline,
                      validator: (value) {
                        if (value?.isEmpty ?? true) return 'Last name is required';
                        return null;
                      },
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 16),

              _buildTextField(
                controller: _emailController,
                label: 'Email Address',
                icon: Icons.email_outlined,
                keyboardType: TextInputType.emailAddress,
                validator: (value) {
                  if (value?.isEmpty ?? true) return 'Email is required';
                  if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value!)) {
                    return 'Please enter a valid email';
                  }
                  return null;
                },
              ),

              const SizedBox(height: 16),

              _buildTextField(
                controller: _phoneController,
                label: 'Phone Number',
                icon: Icons.phone_outlined,
                keyboardType: TextInputType.phone,
                validator: (value) {
                  if (value?.isEmpty ?? true) return 'Phone number is required';
                  if (!RegExp(r'^09\d{9}$').hasMatch(value!)) {
                    return 'Please enter a valid Philippine mobile number (09xxxxxxxxx)';
                  }
                  return null;
                },
              ),

              const SizedBox(height: 16),

              Row(
                children: [
                  Expanded(
                    child: _buildDateField(),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: _buildDropdownField(
                      value: _selectedGender,
                      label: 'Gender',
                      icon: Icons.wc_outlined,
                      items: _genders.map((gender) => DropdownMenuItem(
                        value: gender,
                        child: Text(gender.toUpperCase()),
                      )).toList(),
                      onChanged: (value) => setState(() => _selectedGender = value),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 24),

              // Account Information Section
              _buildSectionHeader('Account Information'),
              const SizedBox(height: 16),

              _buildDropdownField(
                value: _selectedRole,
                label: 'Owner Type',
                icon: Icons.category_outlined,
                items: _roles.map((role) => DropdownMenuItem(
                  value: role['value'],
                  child: Text(role['label']!),
                )).toList(),
                onChanged: (value) => setState(() => _selectedRole = value),
              ),

              const SizedBox(height: 16),

              Row(
                children: [
                  Expanded(
                    child: _buildTextField(
                      controller: _passwordController,
                      label: 'Password',
                      icon: Icons.lock_outline,
                      obscureText: true,
                      validator: (value) {
                        if (value?.isEmpty ?? true) return 'Password is required';
                        if (value!.length < 8) return 'Password must be at least 8 characters';
                        return null;
                      },
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: _buildTextField(
                      controller: _confirmPasswordController,
                      label: 'Confirm Password',
                      icon: Icons.lock_outline,
                      obscureText: true,
                      validator: (value) {
                        if (value?.isEmpty ?? true) return 'Please confirm password';
                        if (value != _passwordController.text) return 'Passwords do not match';
                        return null;
                      },
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 24),

              // Address Information Section
              _buildSectionHeader('Address Information'),
              const SizedBox(height: 16),

              _buildDropdownField(
                value: _selectedBarangay,
                label: 'Barangay',
                icon: Icons.location_on_outlined,
                items: _barangays.map((barangay) => DropdownMenuItem(
                  value: barangay['id'].toString(),
                  child: Text(barangay['name']),
                )).toList(),
                onChanged: (value) => setState(() => _selectedBarangay = value),
              ),

              const SizedBox(height: 16),

              _buildTextField(
                controller: _streetController,
                label: 'Street Address',
                icon: Icons.home_outlined,
                validator: (value) {
                  if (value?.isEmpty ?? true) return 'Street address is required';
                  return null;
                },
              ),

              const SizedBox(height: 32),

              // Submit Button
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _registerOwner,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Config.primaryColor,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    elevation: 0,
                  ),
                  child: _isLoading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        )
                      : Text(
                          'Register Animal Owner',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                ),
              ),

              const SizedBox(height: 16),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Text(
      title,
      style: TextStyle(
        fontFamily: Config.primaryFont,
        fontSize: 18,
        fontWeight: FontWeight.w600,
        color: Config.primaryColor,
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    TextInputType? keyboardType,
    bool obscureText = false,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      obscureText: obscureText,
      validator: validator,
      style: TextStyle(
        fontFamily: Config.primaryFont,
        fontSize: 16,
      ),
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, color: Config.primaryColor),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Config.primaryColor, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Colors.red),
        ),
        filled: true,
        fillColor: Colors.grey[50],
      ),
    );
  }

  Widget _buildDateField() {
    return TextFormField(
      controller: _birthDateController,
      readOnly: true,
      onTap: _selectBirthDate,
      validator: (value) {
        if (value?.isEmpty ?? true) return 'Birth date is required';
        return null;
      },
      style: TextStyle(
        fontFamily: Config.primaryFont,
        fontSize: 16,
      ),
      decoration: InputDecoration(
        labelText: 'Birth Date',
        prefixIcon: Icon(Icons.calendar_today_outlined, color: Config.primaryColor),
        suffixIcon: Icon(Icons.arrow_drop_down, color: Colors.grey[600]),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Config.primaryColor, width: 2),
        ),
        filled: true,
        fillColor: Colors.grey[50],
      ),
    );
  }

  Widget _buildDropdownField<T>({
    required T? value,
    required String label,
    required IconData icon,
    required List<DropdownMenuItem<T>> items,
    required void Function(T?) onChanged,
  }) {
    return DropdownButtonFormField<T>(
      value: value,
      items: items,
      onChanged: onChanged,
      validator: (value) {
        if (value == null) return '$label is required';
        return null;
      },
      style: TextStyle(
        fontFamily: Config.primaryFont,
        fontSize: 16,
        color: Colors.black,
      ),
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, color: Config.primaryColor),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Config.primaryColor, width: 2),
        ),
        filled: true,
        fillColor: Colors.grey[50],
      ),
    );
  }
}
