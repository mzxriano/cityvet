import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/models/vaccine_model.dart';
import 'package:cityvet_app/services/api_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class VaccinationPage extends StatefulWidget {
  final AnimalModel animalModel;
  final int? activityId; 

  const VaccinationPage({
    super.key,
    required this.animalModel,
    this.activityId,
  });

  @override
  State<VaccinationPage> createState() => _VaccinationPageState();
}

class _VaccinationPageState extends State<VaccinationPage> with TickerProviderStateMixin {
  List<VaccineModel> vaccines = [];
  List<Map<String, dynamic>> veterinarians = [];
  VaccineModel? selectedVaccine;
  DateTime? selectedDate;
  final TextEditingController doseController = TextEditingController();
  final TextEditingController adminController = TextEditingController();
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;
  bool isLoading = false;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 600),
      vsync: this,
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeInOut),
    );
    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, 0.3),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _animationController, curve: Curves.easeOutBack));
    _animationController.forward();
    fetchVaccines();
    fetchVeterinarians();
  }

  Future<void> fetchVaccines() async {
    if (!mounted) return; 
    
    setState(() { isLoading = true; });
    final token = await AuthStorage().getToken();
    
    if (!mounted) return; 
    
    final api = ApiService();
    try {
      final data = await api.getVaccines(token!);
      
      if (!mounted) return; 
      
      setState(() {
        vaccines = data.map<VaccineModel>((v) => VaccineModel.fromJson(v)).toList();
        isLoading = false;
      });
    } catch (e) {
      if (!mounted) return; 
      
      setState(() { isLoading = false; });
      _showSnackBar('Failed to load vaccines', Colors.red);
    }
  }

  Future<void> fetchVeterinarians() async {
    if (!mounted) return;

    try {
      final token = await AuthStorage().getToken();
      if (!mounted) return;
      
      if (token == null) return;
      
      final fetchedVeterinarians = await ApiService().fetchVeterinarians(token);
      if (!mounted) return;
      
      setState(() {

        veterinarians = List<Map<String, dynamic>>.from(fetchedVeterinarians);
      });
    } catch (e) {
      print('Error fetching veterinarians: $e');
    }
  }

  @override
  void dispose() {
    _animationController.dispose();
    doseController.dispose();
    adminController.dispose();
    super.dispose();
  }

  void _submit() async {
    print(selectedVaccine?.id);
    print(selectedDate);
    print(doseController.text);
    print(adminController.text);

    if (!mounted) return; 

    if (selectedVaccine == null) {
      _showSnackBar('Please select a vaccine', Colors.orange);
      return;
    }
    if (widget.activityId == null && selectedDate == null) {
      _showSnackBar('Please select a date', Colors.orange);
      return;
    }
    if (doseController.text.isEmpty) {
      _showSnackBar('Please enter dose number', Colors.orange);
      return;
    }
    if (adminController.text.isEmpty) {
      _showSnackBar('Please enter administrator', Colors.orange);
      return;
    }
    
    if (!mounted) return;
    
    setState(() { isLoading = true; });
    final token = await AuthStorage().getToken();
     
    if (!mounted) return;
    
    if(token == null) {
      if (mounted) {
        setState(() { isLoading = false; });
        _showSnackBar('Authentication failed. Please log in again.', Colors.red);
      }
      return;
    }

    final api = ApiService();
    try {
      if (widget.activityId != null) {
        await api.attachVaccinesToActivity(
          token,
          widget.activityId!,
          widget.animalModel.id!,
          [
            {
              'id': selectedVaccine!.id,
              'dose': int.tryParse(doseController.text) ?? 1,
              'administrator': adminController.text,
            }
          ],
        );
      } else {
        await api.attachVaccinesToAnimal(
          token,
          widget.animalModel.id!,
          [
            {
              'id': selectedVaccine!.id,
              'dose': int.tryParse(doseController.text) ?? 1,
              'date_given': selectedDate!.toIso8601String().split('T')[0],
              'administrator': adminController.text,
            }
          ],
        );
      }
      
      if (!mounted) return; 
      
      setState(() { isLoading = false; });
      _showSnackBar('Vaccination recorded successfully!', Colors.green);
      
      await Future.delayed(const Duration(milliseconds: 500));
      
      if (mounted) {
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (!mounted) return; 
      
      setState(() { isLoading = false; });
      print('error attach vaccine $e');
      
      String errorMessage = 'Failed to record vaccination';
      if (e.toString().contains('422')) {
        errorMessage = 'Invalid vaccination data. Please check your inputs.';
      } else if (e.toString().contains('404')) {
        errorMessage = 'Animal not found. Please try again.';
      } else if (e.toString().contains('401')) {
        errorMessage = 'Authentication failed. Please log in again.';
      } else if (e.toString().contains('500')) {
        errorMessage = 'Server error. Please try again later.';
      }
      
      _showSnackBar(errorMessage, Colors.red);
    }
  }

  void _showSnackBar(String message, Color color) {
    if (!mounted) return; 
    
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(
              color == Colors.green ? Icons.check_circle : Icons.warning,
              color: Colors.white,
            ),
            const SizedBox(width: 8),
            Expanded(child: Text(message)),
          ],
        ),
        backgroundColor: color,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        margin: const EdgeInsets.all(16),
      ),
    );
  }

  Future<void> _selectDate() async {
    if (!mounted) return;
    
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
    );
    
    if (!mounted) return;
    if (picked != null) {
      setState(() => selectedDate = picked);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (!mounted) return const SizedBox.shrink(); 
    
    Config().init(context);

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        elevation: 0,
        backgroundColor: Colors.white,
        leading: IconButton(
          onPressed: () {
            if (mounted) Navigator.pop(context);
          },
          icon: Config.backButtonIcon,
        ),
        title: Text(
          widget.activityId != null 
            ? 'Vaccinate ${widget.animalModel.name} (Activity)'
            : 'Vaccinate ${widget.animalModel.name}',
          style: TextStyle(
            color: Config.tertiaryColor,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
      ),
      body: FadeTransition(
        opacity: _fadeAnimation,
        child: SlideTransition(
          position: _slideAnimation,
          child: SingleChildScrollView(
            padding: Config.paddingScreen,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header with pet icon
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        Config.primaryColor.withOpacity(0.1),
                        Config.primaryColor.withOpacity(0.05),
                      ],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: Config.primaryColor.withOpacity(0.2),
                      width: 1,
                    ),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Config.primaryColor.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(
                          Icons.pets,
                          color: Config.primaryColor,
                          size: 32,
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Patient Information',
                              style: TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.w600,
                                color: Config.tertiaryColor,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Review details before vaccination',
                              style: TextStyle(
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

                // Animal Information Card
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.05),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildInfoRow('Name', widget.animalModel.name, Icons.badge),
                      _buildInfoRow('Species', widget.animalModel.type, Icons.category),
                      _buildInfoRow('Breed', widget.animalModel.breed!, Icons.pets),
                      _buildInfoRow('Birthdate', '${widget.animalModel.birthDate ?? 'Unknown'} (${widget.animalModel.ageString})' , Icons.cake),
                      _buildInfoRow('Color', widget.animalModel.color, Icons.palette),
                      _buildInfoRow('Owner', widget.animalModel.owner!, Icons.person, isLast: true),
                    ],
                  ),
                ),

                const SizedBox(height: 24),

                // Vaccination Form Section
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.05),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: Config.primaryColor.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Icon(
                              Icons.medical_services,
                              color: Config.primaryColor,
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Text(
                            'Vaccination Details',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w600,
                              color: Config.tertiaryColor,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),

                      // Vaccine Selection
                      Text(
                        'Select Vaccine',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                          color: Config.tertiaryColor,
                        ),
                      ),
                      const SizedBox(height: 8),
                      DropdownButtonFormField<VaccineModel>(
                        value: selectedVaccine,
                        items: vaccines.map((vaccine) {
                          return DropdownMenuItem<VaccineModel>(
                            value: vaccine,
                            child: Text(vaccine.name),
                          );
                        }).toList(),
                        onChanged: (v) {
                          if (mounted) {
                            setState(() => selectedVaccine = v);
                          }
                        },
                        decoration: const InputDecoration(labelText: 'Select Vaccine'),
                      ),
                      const SizedBox(height: 16),
                      
                      // Dose Input
                      Text(
                        'Dose Number',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                          color: Config.tertiaryColor,
                        ),
                      ),
                      const SizedBox(height: 8),
                      TextFormField(
                        controller: doseController,
                        decoration: const InputDecoration(labelText: 'Dose'),
                        keyboardType: TextInputType.number,
                      ),
                      const SizedBox(height: 16),
                      
                      // Administrator Selection with Search
                      Text(
                        'Administrator',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                          color: Config.tertiaryColor,
                        ),
                      ),
                      const SizedBox(height: 8),
                      SearchableAdministratorField(
                        veterinarians: veterinarians,
                        controller: adminController,
                        onChanged: (value) {
                        },
                      ),
                      const SizedBox(height: 16),
                      
                      // Date Selection or Activity Info
                      if (widget.activityId == null) ...[
                        Text(
                          'Vaccination Date',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                            color: Config.tertiaryColor,
                          ),
                        ),
                        const SizedBox(height: 8),
                        GestureDetector(
                          onTap: _selectDate,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                            decoration: BoxDecoration(
                              border: Border.all(color: Colors.grey[300]!),
                              borderRadius: BorderRadius.circular(12),
                              color: Colors.grey[50],
                            ),
                            child: Row(
                              children: [
                                Icon(
                                  Icons.calendar_today,
                                  color: selectedDate != null ? Config.primaryColor : Colors.grey[600],
                                  size: 20,
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Text(
                                    selectedDate == null 
                                        ? 'Select vaccination date'
                                        : selectedDate!.toLocal().toString().split(' ')[0],
                                    style: TextStyle(
                                      fontSize: 14,
                                      color: selectedDate == null
                                          ? Colors.grey[600]
                                          : Config.tertiaryColor,
                                      fontWeight: selectedDate != null ? FontWeight.w500 : FontWeight.normal,
                                    ),
                                  ),
                                ),
                                Icon(
                                  Icons.arrow_drop_down,
                                  color: Colors.grey[600],
                                ),
                              ],
                            ),
                          ),
                        ),
                      ] else ...[
                        // Show activity info instead of date picker
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Config.primaryColor.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: Config.primaryColor.withOpacity(0.3)),
                          ),
                          child: Row(
                            children: [
                              Icon(Icons.event, color: Config.primaryColor, size: 20),
                              const SizedBox(width: 8),
                              Text(
                                'Vaccination will be linked to current activity',
                                style: TextStyle(
                                  fontSize: Config.fontXS,
                                  color: Config.primaryColor,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                      const SizedBox(height: 20),
                    ],
                  ),
                ),

                const SizedBox(height: 32),

                // Submit Button
                Container(
                  width: double.infinity,
                  height: 56,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [Config.primaryColor, Config.primaryColor.withOpacity(0.8)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: Config.primaryColor.withOpacity(0.3),
                        blurRadius: 8,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Material(
                    color: Colors.transparent,
                    child: InkWell(
                      onTap: isLoading ? null : _submit, 
                      borderRadius: BorderRadius.circular(16),
                      child: Center(
                        child: isLoading
                            ? const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                    ),
                                  ),
                                  SizedBox(width: 12),
                                  Text(
                                    'Recording...',
                                    style: TextStyle(
                                      color: Colors.white,
                                      fontSize: 16,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ],
                              )
                            : const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    Icons.save,
                                    color: Colors.white,
                                    size: 20,
                                  ),
                                  SizedBox(width: 8),
                                  Text(
                                    'Record Vaccination',
                                    style: TextStyle(
                                      color: Colors.white,
                                      fontSize: 16,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ],
                              ),
                      ),
                    ),
                  ),
                ),

                const SizedBox(height: 20),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value, IconData icon, {bool isLast = false}) {
    return Padding(
      padding: EdgeInsets.only(bottom: isLast ? 0 : 16),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: Config.primaryColor.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(
              icon,
              color: Config.primaryColor,
              size: 16,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                    color: Colors.grey[600],
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                    color: Config.tertiaryColor,
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

// Searchable Administrator Field Widget
class SearchableAdministratorField extends StatefulWidget {
  final List<Map<String, dynamic>> veterinarians;
  final TextEditingController controller;
  final Function(String) onChanged;

  const SearchableAdministratorField({
    super.key,
    required this.veterinarians,
    required this.controller,
    required this.onChanged,
  });

  @override
  State<SearchableAdministratorField> createState() => _SearchableAdministratorFieldState();
}

class _SearchableAdministratorFieldState extends State<SearchableAdministratorField>
    with SingleTickerProviderStateMixin {
  final FocusNode _focusNode = FocusNode();
  List<Map<String, dynamic>> filteredVeterinarians = [];
  bool isDropdownOpen = false;
  late AnimationController _dropdownController;
  late Animation<double> _dropdownAnimation;

  @override
  void initState() {
    super.initState();
    filteredVeterinarians = widget.veterinarians;
    widget.controller.addListener(_filterVeterinarians);
    _focusNode.addListener(_onFocusChange);
    
    _dropdownController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );
    _dropdownAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _dropdownController, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    widget.controller.removeListener(_filterVeterinarians);
    _focusNode.removeListener(_onFocusChange);
    _focusNode.dispose();
    _dropdownController.dispose();
    super.dispose();
  }

  void _filterVeterinarians() {
    if (!mounted) return;
    
    final query = widget.controller.text.toLowerCase();
    setState(() {
      filteredVeterinarians = widget.veterinarians
          .where((vet) => _getVetName(vet).toLowerCase().contains(query))
          .toList();
    });
    widget.onChanged(widget.controller.text);
  }

  String _getVetName(Map<String, dynamic> vet) {
    // Handle different possible field names from the API
    return vet.isNotEmpty ? '${vet['first_name']} ${vet['last_name']}' : 'Unknown';
  }

  void _onFocusChange() {
    if (!mounted) return;
    
    if (_focusNode.hasFocus && !isDropdownOpen && widget.veterinarians.isNotEmpty) {
      _showDropdown();
    }
  }

  void _showDropdown() {
    if (!mounted) return;
    
    setState(() {
      isDropdownOpen = true;
      filteredVeterinarians = widget.veterinarians;
    });
    _dropdownController.forward();
  }

  void _hideDropdown() {
    if (!mounted) return;
    
    setState(() {
      isDropdownOpen = false;
    });
    _dropdownController.reverse();
  }

  void _selectVeterinarian(Map<String, dynamic> vet) {
    widget.controller.text = _getVetName(vet);
    _hideDropdown();
    _focusNode.unfocus();
  }

  @override
  Widget build(BuildContext context) {
    if (!mounted) return const SizedBox.shrink();
    
    return Column(
      children: [
        TextFormField(
          controller: widget.controller,
          focusNode: _focusNode,
          decoration: InputDecoration(
            labelText: 'Administrator',
            hintText: 'Type name or select from list',
            prefixIcon: Icon(
              Icons.person,
              color: widget.controller.text.isNotEmpty ? Config.primaryColor : Colors.grey[600],
            ),
            suffixIcon: widget.veterinarians.isNotEmpty 
                ? IconButton(
                    icon: AnimatedRotation(
                      turns: isDropdownOpen ? 0.5 : 0,
                      duration: const Duration(milliseconds: 300),
                      child: Icon(
                        Icons.expand_more,
                        color: Colors.grey[600],
                      ),
                    ),
                    onPressed: () {
                      if (isDropdownOpen) {
                        _hideDropdown();
                        _focusNode.unfocus();
                      } else {
                        _showDropdown();
                        _focusNode.requestFocus();
                      }
                    },
                  )
                : null,
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
          onTap: () {
            if (!isDropdownOpen && widget.veterinarians.isNotEmpty) {
              _showDropdown();
            }
          },
        ),
        AnimatedBuilder(
          animation: _dropdownAnimation,
          builder: (context, child) {
            return ClipRect(
              child: Align(
                alignment: Alignment.topCenter,
                heightFactor: _dropdownAnimation.value,
                child: child,
              ),
            );
          },
          child: isDropdownOpen && widget.veterinarians.isNotEmpty
              ? Container(
                  margin: const EdgeInsets.only(top: 8),
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.grey[300]!),
                    borderRadius: BorderRadius.circular(12),
                    color: Colors.white,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.1),
                        blurRadius: 8,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Container(
                    constraints: const BoxConstraints(maxHeight: 200),
                    child: ListView.builder(
                      shrinkWrap: true,
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      itemCount: filteredVeterinarians.length,
                      itemBuilder: (context, index) {
                        final vet = filteredVeterinarians[index];
                        final vetName = _getVetName(vet);
                        return Container(
                          margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: ListTile(
                            leading: Icon(
                              Icons.person,
                              color: Config.primaryColor,
                              size: 20,
                            ),
                            title: Text(
                              vetName,
                              style: const TextStyle(fontSize: 14),
                            ),
                            subtitle: vet['email'] != null 
                                ? Text(
                                    vet['email'].toString(),
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: Colors.grey[600],
                                    ),
                                  )
                                : null,
                            onTap: () => _selectVeterinarian(vet),
                            dense: true,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                )
              : isDropdownOpen && filteredVeterinarians.isEmpty
                  ? Container(
                      margin: const EdgeInsets.only(top: 8),
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        border: Border.all(color: Colors.grey[300]!),
                        borderRadius: BorderRadius.circular(12),
                        color: Colors.white,
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.1),
                            blurRadius: 8,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Column(
                        children: [
                          Icon(
                            Icons.search_off,
                            color: Colors.grey[400],
                            size: 32,
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'No veterinarians found',
                            style: TextStyle(
                              color: Colors.grey[600],
                              fontSize: 14,
                            ),
                          ),
                        ],
                      ),
                    )
                  : const SizedBox.shrink(),
        ),
      ],
    );
  }
}

// Enhanced Searchable Dropdown Widget (keeping the original for vaccine selection)
class EnhancedSearchableDropdown extends StatefulWidget {
  final List<Map<String, dynamic>> items;
  final Map<String, dynamic>? selectedItem;
  final Function(Map<String, dynamic>?) onChanged;
  final String hintText;
  final String Function(Map<String, dynamic>?) displayText;
  final String searchHint;

  const EnhancedSearchableDropdown({
    super.key,
    required this.items,
    required this.selectedItem,
    required this.onChanged,
    required this.hintText,
    required this.displayText,
    required this.searchHint,
  });

  @override
  State<EnhancedSearchableDropdown> createState() => _EnhancedSearchableDropdownState();
}

class _EnhancedSearchableDropdownState extends State<EnhancedSearchableDropdown>
    with SingleTickerProviderStateMixin {
  final TextEditingController _searchController = TextEditingController();
  final FocusNode _focusNode = FocusNode();
  List<Map<String, dynamic>> filteredItems = [];
  bool isDropdownOpen = false;
  late AnimationController _dropdownController;
  late Animation<double> _dropdownAnimation;

  @override
  void initState() {
    super.initState();
    filteredItems = widget.items;
    _searchController.addListener(_filterItems);
    _dropdownController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );
    _dropdownAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _dropdownController, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _searchController.dispose();
    _focusNode.dispose();
    _dropdownController.dispose();
    super.dispose();
  }

  void _filterItems() {
    if (!mounted) return; 
    
    setState(() {
      filteredItems = widget.items
          .where((item) => item['name']
              .toString()
              .toLowerCase()
              .contains(_searchController.text.toLowerCase()))
          .toList();
    });
  }

  void _toggleDropdown() {
    if (!mounted) return; 
    
    setState(() {
      isDropdownOpen = !isDropdownOpen;
      if (isDropdownOpen) {
        _searchController.clear();
        filteredItems = widget.items;
        _focusNode.requestFocus();
        _dropdownController.forward();
      } else {
        _dropdownController.reverse();
      }
    });
  }

  void _selectItem(Map<String, dynamic> item) {
    widget.onChanged(item);
    if (!mounted) return; 
    
    setState(() {
      isDropdownOpen = false;
    });
    _focusNode.unfocus();
    _dropdownController.reverse();
  }

  @override
  Widget build(BuildContext context) {
    if (!mounted) return const SizedBox.shrink();
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        GestureDetector(
          onTap: _toggleDropdown,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
            decoration: BoxDecoration(
              border: Border.all(
                color: isDropdownOpen ? Config.primaryColor : Colors.grey[300]!,
                width: isDropdownOpen ? 2 : 1,
              ),
              borderRadius: BorderRadius.circular(12),
              color: Colors.grey[50],
            ),
            child: Row(
              children: [
                Icon(
                  Icons.vaccines,
                  color: widget.selectedItem != null ? Config.primaryColor : Colors.grey[600],
                  size: 20,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    widget.displayText(widget.selectedItem),
                    style: TextStyle(
                      fontSize: 14,
                      color: widget.selectedItem == null
                          ? Colors.grey[600]
                          : Config.tertiaryColor,
                      fontWeight: widget.selectedItem != null ? FontWeight.w500 : FontWeight.normal,
                    ),
                  ),
                ),
                AnimatedRotation(
                  turns: isDropdownOpen ? 0.5 : 0,
                  duration: const Duration(milliseconds: 300),
                  child: Icon(
                    Icons.expand_more,
                    color: Colors.grey[600],
                  ),
                ),
              ],
            ),
          ),
        ),
        AnimatedBuilder(
          animation: _dropdownAnimation,
          builder: (context, child) {
            return ClipRect(
              child: Align(
                alignment: Alignment.topCenter,
                heightFactor: _dropdownAnimation.value,
                child: child,
              ),
            );
          },
          child: isDropdownOpen
              ? Container(
                  margin: const EdgeInsets.only(top: 8),
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.grey[300]!),
                    borderRadius: BorderRadius.circular(12),
                    color: Colors.white,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.1),
                        blurRadius: 8,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Column(
                    children: [
                      // Search field
                      Padding(
                        padding: const EdgeInsets.all(12),
                        child: TextField(
                          controller: _searchController,
                          focusNode: _focusNode,
                          decoration: InputDecoration(
                            hintText: widget.searchHint,
                            prefixIcon: Icon(Icons.search, color: Config.primaryColor),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: BorderSide(color: Colors.grey[300]!),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: BorderSide(color: Colors.grey[300]!),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: BorderSide(color: Config.primaryColor),
                            ),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            filled: true,
                            fillColor: Colors.grey[50],
                          ),
                        ),
                      ),
                      // Dropdown items
                      Container(
                        constraints: const BoxConstraints(maxHeight: 200),
                        child: ListView.builder(
                          shrinkWrap: true,
                          itemCount: filteredItems.length,
                          itemBuilder: (context, index) {
                            final item = filteredItems[index];
                            return Container(
                              margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: ListTile(
                                leading: Icon(
                                  Icons.medical_services,
                                  color: Config.primaryColor,
                                  size: 20,
                                ),
                                title: Text(
                                  item['name'],
                                  style: const TextStyle(fontSize: 14),
                                ),
                                onTap: () => _selectItem(item),
                                dense: true,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(8),
                                ),
                              ),
                            );
                          },
                        ),
                      ),
                      if (filteredItems.isEmpty)
                        Padding(
                          padding: const EdgeInsets.all(16.0),
                          child: Column(
                            children: [
                              Icon(
                                Icons.search_off,
                                color: Colors.grey[400],
                                size: 48,
                              ),
                              const SizedBox(height: 8),
                              Text(
                                'No vaccines found',
                                style: TextStyle(
                                  color: Colors.grey[600],
                                  fontSize: 14,
                                ),
                              ),
                            ],
                          ),
                        ),
                    ],
                  ),
                )
              : const SizedBox.shrink(),
        ),
      ],
    );
  }
}