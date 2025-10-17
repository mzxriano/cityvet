import 'dart:io';

import 'package:cityvet_app/modals/confirmation_modal.dart';
import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/utils/image_picker.dart';
import 'package:cityvet_app/viewmodels/animal_form_view_model.dart';
import 'package:cityvet_app/services/animal_type_service.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class AnimalForm extends StatefulWidget {
  const AnimalForm({super.key});

  @override
  State<AnimalForm> createState() => _AnimalFormState();
}

class _AnimalFormState extends State<AnimalForm> {
  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (context) => AnimalFormViewModel(),
      child: const _AnimalFormContent(),
    );
  }
}

class _AnimalFormContent extends StatefulWidget {
  const _AnimalFormContent();

  @override
  State<_AnimalFormContent> createState() => _AnimalFormContentState();
}

class _AnimalFormContentState extends State<_AnimalFormContent> {
  final TextEditingController petNameController = TextEditingController();
  final TextEditingController weightController = TextEditingController();
  final TextEditingController heightController = TextEditingController();
  final TextEditingController uniqueSpotController = TextEditingController();
  final TextEditingController knownConditionsController = TextEditingController();
  final FocusNode petNameNode = FocusNode();
  final FocusNode weightNode = FocusNode();
  final FocusNode heightNode = FocusNode();
  final FocusNode uniqueSpotNode = FocusNode();
  final FocusNode knownConditionsNode = FocusNode();
  final AnimalTypeService _animalTypeService = AnimalTypeService();

  String? selectedPetType;
  String? selectedBreed;
  String? selectedGender;
  DateTime? selectedDate;
  String? selectedColor;
  bool _isLoadingBreeds = true;

  Map<String, List<String>> petBreeds = {};

  List<String> colors = ['Black', 'Brown', 'White', 'Golden', 'Gray', 'Orange'];

  @override
  void initState() {
    super.initState();
    _loadAnimalTypes();
  }

  Future<void> _loadAnimalTypes() async {
    try {
      final data = await _animalTypeService.getAnimalTypesAndBreeds();
      setState(() {
        petBreeds = data['petBreeds'] as Map<String, List<String>>;
        _isLoadingBreeds = false;
      });
    } catch (e) {
      setState(() {
        _isLoadingBreeds = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to load animal types: $e')),
        );
      }
    }
  }

  void _clearForm() {
    setState(() {
      petNameController.clear();
      weightController.clear();
      heightController.clear();
      uniqueSpotController.clear();
      knownConditionsController.clear();
      selectedPetType = null;
      selectedBreed = null;
      selectedGender = null;
      selectedDate = null;
      selectedColor = null;
    });
    
    final formRef = Provider.of<AnimalFormViewModel>(context, listen: false);
    formRef.setAnimalProfile(null);
  }

  Future<bool> _onWillPop() async {
    final isFormDirty = petNameController.text.isNotEmpty ||
                        weightController.text.isNotEmpty ||
                        heightController.text.isNotEmpty ||
                        uniqueSpotController.text.isNotEmpty ||
                        knownConditionsController.text.isNotEmpty ||
                        selectedPetType != null ||
                        selectedBreed != null ||
                        selectedGender != null ||
                        selectedDate != null ||
                        selectedColor != null;

    if (!isFormDirty) return true;

    final shouldLeave = await showConfirmationModal(context);

    return shouldLeave ?? false;
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);
    
    return Consumer<AnimalFormViewModel>(
      builder: (context, formRef, child) {
        return Stack(
          children: [
            Scaffold(
              appBar: AppBar(
                leading: IconButton(
                  onPressed: formRef.isLoading ? null : () async {
                    final shouldPop = await _onWillPop();
                    if(shouldPop) {
                     Navigator.pop(context);
                    }
                  },
                  icon: Config.backButtonIcon,
                ),
                title: Text(
                  'Register Animal',
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
                    onPressed: formRef.isLoading ? null : () {
                      _clearForm();
                    },
                    child: const Text(
                      'Clear',
                      style: TextStyle(color: Colors.white),
                    ),
                  ),
                ],
              ),
              body: SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Config.primaryColor.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            Icons.pets,
                            color: Config.primaryColor,
                            size: 28,
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Animal Registration',
                                  style: TextStyle(
                                    fontFamily: Config.primaryFont,
                                    fontSize: 18,
                                    fontWeight: FontWeight.w600,
                                    color: Config.primaryColor,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'Register a new pet with complete information',
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

                    // Animal Photo Section
                    _buildSectionHeader('Animal Photo'),
                    const SizedBox(height: 16),
                    
                    Center(
                      child: Stack(
                        children: [
                          CircleAvatar(
                            backgroundImage: formRef.animalProfile != null
                                ? FileImage(formRef.animalProfile!)
                                : null,
                            radius: 80,
                            backgroundColor: Colors.grey[200],
                            child: formRef.animalProfile == null
                                ? Icon(
                                    Icons.pets,
                                    size: 60,
                                    color: Colors.grey[400],
                                  )
                                : null,
                          ),
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: InkWell(
                              onTap: formRef.isLoading ? null : () async {
                                final pickedImage = await CustomImagePicker().pickFromGallery();
                                if(pickedImage == null) return;
                                formRef.setAnimalProfile(File(pickedImage.path));
                              },
                              child: Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: Config.primaryColor,
                                  shape: BoxShape.circle,
                                  border: Border.all(color: Colors.white, width: 2),
                                ),
                                child: const Icon(
                                  Icons.camera_alt,
                                  color: Colors.white,
                                  size: 20,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    
                    const SizedBox(height: 32),

                    // Basic Information Section
                    _buildSectionHeader('Basic Information'),
                    const SizedBox(height: 16),

                    /// Pet Type
                    _buildDropdownField<String>(
                      value: selectedPetType,
                      label: 'Species',
                      icon: Icons.category_outlined,
                      items: petBreeds.keys.map((type) {
                        return DropdownMenuItem(value: type, child: Text(type));
                      }).toList(),
                      onChanged: (value) {
                        if (!formRef.isLoading) {
                          setState(() {
                            selectedPetType = value;
                            selectedBreed = null;
                          });
                        }
                      },
                      isRequired: true,
                    ),

                    const SizedBox(height: 16),

                    /// Breed
                    _buildDropdownField<String>(
                      value: selectedBreed,
                      label: 'Breed',
                      icon: Icons.pets,
                      items: (selectedPetType != null
                              ? petBreeds[selectedPetType]!
                              : <String>[])
                          .map((breed) => DropdownMenuItem(value: breed, child: Text(breed)))
                          .toList(),
                      onChanged: (value) {
                        if (!formRef.isLoading) {
                          setState(() {
                            selectedBreed = value;
                          });
                        }
                      },
                      isRequired: true,
                    ),

                    const SizedBox(height: 16),

                    /// Pet Name
                    _buildTextField(
                      controller: petNameController,
                      label: 'Name',
                      icon: Icons.abc,
                      validator: (value) {
                        if (value?.isEmpty ?? true) return 'Name is required';
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
                          child: _buildDropdownField<String>(
                            value: selectedColor,
                            label: 'Color',
                            icon: Icons.color_lens_outlined,
                            items: colors
                                .map((color) => DropdownMenuItem(value: color, child: Text(color)))
                                .toList(),
                            onChanged: (value) {
                              if (!formRef.isLoading) {
                                setState(() {
                                  selectedColor = value;
                                });
                              }
                            },
                            isRequired: true,
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 16),

                    /// Gender
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.grey[50],
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.grey[300]!),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Icon(Icons.wc_outlined, color: Config.primaryColor, size: 20),
                              const SizedBox(width: 8),
                              Text(
                                'Gender *',
                                style: TextStyle(
                                  fontFamily: Config.primaryFont,
                                  fontSize: 16,
                                  fontWeight: FontWeight.w500,
                                  color: Colors.grey[700],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Row(
                            children: ['Male', 'Female'].map((gender) {
                              return Expanded(
                                child: InkWell(
                                  onTap: () {
                                    if (!formRef.isLoading) {
                                      setState(() {
                                        selectedGender = gender;
                                      });
                                    }
                                  },
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                                    margin: const EdgeInsets.only(right: 8),
                                    decoration: BoxDecoration(
                                      color: selectedGender == gender 
                                          ? Config.primaryColor.withValues(alpha: 0.1)
                                          : Colors.white,
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(
                                        color: selectedGender == gender 
                                            ? Config.primaryColor
                                            : Colors.grey[300]!,
                                        width: selectedGender == gender ? 2 : 1,
                                      ),
                                    ),
                                    child: Row(
                                      children: [
                                        Radio<String>(
                                          value: gender,
                                          groupValue: selectedGender,
                                          onChanged: (value) {
                                            if (!formRef.isLoading) {
                                              setState(() {
                                                selectedGender = value;
                                              });
                                            }
                                          },
                                          activeColor: Config.primaryColor,
                                        ),
                                        Text(
                                          gender,
                                          style: TextStyle(
                                            fontFamily: Config.primaryFont,
                                            fontSize: 16,
                                            color: selectedGender == gender 
                                                ? Config.primaryColor
                                                : Colors.grey[700],
                                            fontWeight: selectedGender == gender 
                                                ? FontWeight.w600
                                                : FontWeight.normal,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              );
                            }).toList(),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 24),

                    // Physical Characteristics Section
                    _buildSectionHeader('Physical Characteristics'),
                    const SizedBox(height: 16),

                    Row(
                      children: [
                        Expanded(
                          child: _buildTextField(
                            controller: weightController,
                            label: 'Weight (kg)',
                            icon: Icons.monitor_weight_outlined,
                            keyboardType: TextInputType.number,
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: _buildTextField(
                            controller: heightController,
                            label: 'Height (cm)',
                            icon: Icons.height_outlined,
                            keyboardType: TextInputType.number,
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 16),

                    _buildTextField(
                      controller: uniqueSpotController,
                      label: 'Unique Spot/Markings',
                      icon: Icons.colorize_outlined,
                    ),

                    const SizedBox(height: 16),

                    _buildTextField(
                      controller: knownConditionsController,
                      label: 'Known Conditions',
                      icon: Icons.medical_services_outlined,
                      maxLines: 3,
                    ),

                    const SizedBox(height: 32),

                    /// Submit Button
                    SizedBox(
                      width: double.infinity,
                      height: 50,
                      child: ElevatedButton(
                        onPressed: formRef.isLoading ? null : () async {
                          if (selectedPetType == null ||
                              selectedBreed == null ||
                              selectedGender == null ||
                              selectedColor == null ||
                              petNameController.text.trim().isEmpty) {
                                
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Please fill in all required fields.')),
                            );
                            return;
                          }

                          String? formattedDate;
                          if(selectedDate != null) {
                            formattedDate = 
                              '${selectedDate!.year}-${selectedDate!.month.toString().padLeft(2, '0')}-${selectedDate!.day.toString().padLeft(2, '0')}';
                          }

                          final animal = AnimalModel(
                            type: selectedPetType!, 
                            name: petNameController.text, 
                            breed: selectedBreed!, 
                            birthDate: formattedDate, 
                            gender: selectedGender!.toLowerCase(), 
                            weight: double.tryParse(weightController.text), 
                            height: double.tryParse(heightController.text), 
                            color: selectedColor!,
                            uniqueSpot: uniqueSpotController.text,
                            knownConditions: knownConditionsController.text,
                          );

                          await formRef.createAnimal(animal);

                          if (formRef.message != null && context.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(content: Text(formRef.message!)),
                            );
                            Navigator.pop(context, true);
                          }
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Config.primaryColor,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          elevation: 0,
                        ),
                        child: formRef.isLoading
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                  color: Colors.white,
                                  strokeWidth: 2,
                                ),
                              )
                            : Text(
                                'Register Animal',
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
            // Loading overlay
            if (formRef.isLoading)
              Container(
                color: Colors.black.withValues(alpha: 0.5),
                child: const Center(
                  child: CircularProgressIndicator(
                    strokeWidth: 4.0,
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                  ),
                ),
              ),
          ],
        );
      },
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
    int? maxLines,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      obscureText: obscureText,
      maxLines: maxLines ?? 1,
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
      readOnly: true,
      onTap: () async {
        if (Provider.of<AnimalFormViewModel>(context, listen: false).isLoading) return;
        
        final date = await showDatePicker(
          context: context,
          initialDate: selectedDate ?? DateTime.now(),
          firstDate: DateTime(1950),
          lastDate: DateTime.now(),
        );
        if (date != null) {
          setState(() {
            selectedDate = date;
          });
        }
      },
      style: TextStyle(
        fontFamily: Config.primaryFont,
        fontSize: 16,
      ),
      decoration: InputDecoration(
        labelText: 'Birth Date',
        hintText: selectedDate != null
            ? '${selectedDate!.month}/${selectedDate!.day}/${selectedDate!.year}'
            : 'Select Date',
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
    bool isRequired = false,
  }) {
    return DropdownButtonFormField<T>(
      value: value,
      items: items,
      onChanged: onChanged,
      validator: isRequired ? (value) {
        if (value == null) return '$label is required';
        return null;
      } : null,
      style: TextStyle(
        fontFamily: Config.primaryFont,
        fontSize: 16,
        color: Colors.black,
      ),
      decoration: InputDecoration(
        labelText: isRequired ? '$label *' : label,
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