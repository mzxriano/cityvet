import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/models/vaccine_model.dart';
import 'package:cityvet_app/services/api_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class VaccinationPage extends StatefulWidget {
  final AnimalModel animalModel;
  // Remove vaccines param, fetch from backend
  // final List<Map<String, dynamic>> vaccines;

  const VaccinationPage({
    super.key,
    required this.animalModel,
  });

  @override
  State<VaccinationPage> createState() => _VaccinationPageState();
}

class _VaccinationPageState extends State<VaccinationPage> with TickerProviderStateMixin {
  List<VaccineModel> vaccines = [];
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
  }

  Future<void> fetchVaccines() async {
    setState(() { isLoading = true; });
    final token = await AuthStorage().getToken();
    final api = ApiService();
    try {
      final data = await api.getVaccines(token!);
      setState(() {
        vaccines = data.map<VaccineModel>((v) => VaccineModel.fromJson(v)).toList();
        isLoading = false;
      });
    } catch (e) {
      setState(() { isLoading = false; });
      _showSnackBar('Failed to load vaccines', Colors.red);
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
    if (selectedVaccine == null) {
      _showSnackBar('Please select a vaccine', Colors.orange);
      return;
    }
    if (selectedDate == null) {
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
    setState(() { isLoading = true; });
    final token = await AuthStorage().getToken();
    final api = ApiService();
    try {
      await api.attachVaccinesToAnimal(
        token!,
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
      setState(() { isLoading = false; });
      _showSnackBar('Vaccination recorded successfully!', Colors.green);
      Navigator.pop(context, true);
    } catch (e) {
      setState(() { isLoading = false; });
      _showSnackBar('Failed to record vaccination', Colors.red);
    }
  }

  void _showSnackBar(String message, Color color) {
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

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        elevation: 0,
        backgroundColor: Colors.white,
        leading: IconButton(
          onPressed: () => Navigator.pop(context),
          icon: Config.backButtonIcon,
        ),
        title: Text(
          'Vaccinate ${widget.animalModel.name}',
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

                // Enhanced Animal Information Card
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
                      _buildInfoRow('Age', widget.animalModel.ageString, Icons.cake),
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
                        onChanged: (v) => setState(() => selectedVaccine = v),
                        decoration: InputDecoration(labelText: 'Select Vaccine'),
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: doseController,
                        decoration: InputDecoration(labelText: 'Dose'),
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: adminController,
                        decoration: InputDecoration(labelText: 'Administrator'),
                      ),
                      const SizedBox(height: 12),
                      ListTile(
                        title: Text(selectedDate == null ? 'Select Date' : selectedDate!.toLocal().toString().split(' ')[0]),
                        trailing: Icon(Icons.calendar_today),
                        onTap: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: DateTime.now(),
                            firstDate: DateTime(2000),
                            lastDate: DateTime(2100),
                          );
                          if (picked != null) setState(() => selectedDate = picked);
                        },
                      ),
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
                      onTap: _submit,
                      borderRadius: BorderRadius.circular(16),
                      child: const Center(
                        child: Row(
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

// Enhanced Searchable Dropdown Widget
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
    setState(() {
      isDropdownOpen = false;
    });
    _focusNode.unfocus();
    _dropdownController.reverse();
  }

  @override
  Widget build(BuildContext context) {
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