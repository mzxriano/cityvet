import 'package:cityvet_app/components/button.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cityvet_app/modals/confirmation_modal.dart';
import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/animal_edit_view_model.dart';
import 'package:cityvet_app/viewmodels/animal_view_model.dart';

class AnimalEdit extends StatefulWidget {
  final AnimalModel animalModel;
  const AnimalEdit({super.key, required this.animalModel});

  @override
  State<AnimalEdit> createState() => _AnimalEditState();
}

class _AnimalEditState extends State<AnimalEdit> {
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();

  final TextEditingController petNameController = TextEditingController();
  final TextEditingController weightController = TextEditingController();
  final TextEditingController heightController = TextEditingController();
  final FocusNode petNameNode = FocusNode();
  final FocusNode weightNode = FocusNode();
  final FocusNode heightNode = FocusNode();

  String? selectedPetType;
  String? selectedBreed;
  String? selectedGender;
  DateTime? selectedDate;
  String? selectedColor;

  String? petTypeError;
  String? breedError;
  String? genderError;
  String? colorError;

  Map<String, List<String>> petBreeds = {
    'Dog': ['Aspin', 'Labrador', 'Poodle', 'Bulldog'],
    'Cat': ['Persian', 'Siamese', 'Bengal', 'British Shorthair'],
  };

  List<String> colors = ['Black', 'Brown', 'White', 'Golden', 'Gray'];

  Future<bool> _onWillPop() async {
    final isFormDirty = petNameController.text.isNotEmpty ||
        weightController.text.isNotEmpty ||
        heightController.text.isNotEmpty ||
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
  void initState() {
    super.initState();

    final animal = widget.animalModel;

    petNameController.text = animal.name;
    weightController.text = animal.weight?.toString() ?? '';
    heightController.text = animal.height?.toString() ?? '';

    selectedPetType = animal.type;
    selectedBreed = animal.breed;
    selectedDate = animal.birthDate != null ? DateTime.tryParse(animal.birthDate!) : null;
    selectedGender = animal.gender;
    selectedColor = animal.color;
  }

  // Helper method for consistent InputDecoration
  InputDecoration _buildInputDecoration() {
    return InputDecoration(
      filled: true,
      fillColor: Config.secondaryColor,
      contentPadding: Config.paddingTextfield,
      border: const OutlineInputBorder(
        borderRadius: BorderRadius.all(Radius.circular(10)),
      ),
      enabledBorder: const OutlineInputBorder(
        borderSide: BorderSide(color: Colors.transparent),
        borderRadius: BorderRadius.all(Radius.circular(10)),
      ),
      focusedBorder: OutlineInputBorder(
        borderSide: BorderSide(
          color: Config.primaryColor,
          width: 2,
        ),
        borderRadius: BorderRadius.all(Radius.circular(10)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    return ChangeNotifierProvider<AnimalEditViewModel>(
      create: (_) => AnimalEditViewModel(),
      child: Consumer<AnimalEditViewModel>(builder: (context, ref, _) {
        return Stack(
          children: [
            Scaffold(
              appBar: AppBar(
                leading: IconButton(
                  onPressed: ref.isLoading ? null : () async {
                    final shouldPop = await _onWillPop();
                    if (shouldPop) {
                      Navigator.pop(context);
                    }
                  },
                  icon: Config.backButtonIcon,
                ),
                title: const Text('Edit Pet'),
              ),
              body: Padding(
                padding: Config.paddingScreen,
                child: SingleChildScrollView(
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Pet Profile - Fixed image display logic
                        SizedBox(
                          child: Center(
                            child: CircleAvatar(
                              radius: 70,
                              backgroundImage: _getImageProvider(ref),
                              child: IconButton(
                                onPressed: ref.isLoading ? null : () {
                                  ref.pickImageFromGallery();
                                },
                                icon: const Icon(
                                  Icons.camera_alt_rounded,
                                  size: 50,
                                  color: Colors.grey,
                                ),
                              ),
                            ),
                          ),
                        ),

                        /// Pet Type
                        const SizedBox(height: 12),
                        Text(
                          'Pet Type',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontMedium,
                          ),
                        ),
                        DropdownButtonFormField<String>(
                          value: selectedPetType,
                          decoration: _buildInputDecoration(),
                          items: petBreeds.keys
                              .map((type) => DropdownMenuItem(
                                    value: type,
                                    child: Text(type),
                                  ))
                              .toList(),
                          onChanged: ref.isLoading ? null : (value) {
                            setState(() {
                              selectedPetType = value;
                              selectedBreed = null;
                              petTypeError = null;
                            });
                          },
                          validator: (value) {
                            if (value == null) {
                              return 'Please select a pet type';
                            }
                            return null;
                          },
                        ),

                        /// Breed
                        const SizedBox(height: 12),
                        Text(
                          'Pet Breed',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontMedium,
                          ),
                        ),
                        DropdownButtonFormField<String>(
                          value: selectedBreed,
                          decoration: _buildInputDecoration(),
                          items: (selectedPetType != null
                                  ? petBreeds[selectedPetType]!
                                  : <String>[])
                              .map((breed) => DropdownMenuItem(
                                    value: breed,
                                    child: Text(breed),
                                  ))
                              .toList(),
                          onChanged: ref.isLoading ? null : (value) {
                            setState(() {
                              selectedBreed = value;
                              breedError = null;
                            });
                          },
                          validator: (value) {
                            if (selectedPetType != null && value == null) {
                              return 'Please select a breed';
                            }
                            return null;
                          },
                        ),

                        /// Pet Name
                        const SizedBox(height: 12),
                        Text(
                          'Pet Name',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontMedium,
                          ),
                        ),
                        TextFormField(
                          controller: petNameController,
                          focusNode: petNameNode,
                          keyboardType: TextInputType.name,
                          decoration: _buildInputDecoration(),
                          enabled: !ref.isLoading,
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'Please enter pet name';
                            }
                            return null;
                          },
                        ),

                        /// Date of Birth
                        const SizedBox(height: 12),
                        Text(
                          'Pet Birthdate',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontMedium,
                          ),
                        ),
                        InkWell(
                          onTap: ref.isLoading ? null : () async {
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
                          child: InputDecorator(
                            decoration: const InputDecoration(labelText: ''),
                            child: Text(
                              selectedDate != null
                                  ? '${selectedDate!.month}/${selectedDate!.day}/${selectedDate!.year}'
                                  : 'Select Date',
                              style: TextStyle(
                                color: selectedDate != null
                                    ? (ref.isLoading ? Colors.grey : Colors.black)
                                    : Colors.grey,
                              ),
                            ),
                          ),
                        ),

                        /// Gender
                        const SizedBox(height: 12),
                        Text(
                          'Pet Gender',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontMedium,
                          ),
                        ),
                        Row(
                          children: ['male', 'female'].map((gender) {
                            return Row(
                              children: [
                                Radio<String>(
                                  value: gender,
                                  groupValue: selectedGender,
                                  onChanged: ref.isLoading ? null : (value) {
                                    setState(() {
                                      selectedGender = value;
                                      genderError = null;
                                    });
                                  },
                                ),
                                Text(
                                  gender,
                                  style: TextStyle(
                                    color: ref.isLoading ? Colors.grey : Colors.black,
                                  ),
                                ),
                              ],
                            );
                          }).toList(),
                        ),
                        if (genderError != null)
                          Padding(
                            padding: const EdgeInsets.only(left: 12),
                            child: Text(
                              genderError!,
                              style: const TextStyle(color: Colors.red, fontSize: 12),
                            ),
                          ),

                        /// Weight
                        const SizedBox(height: 12),
                        Text(
                          'Pet Weight (kg)',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontMedium,
                          ),
                        ),
                        TextFormField(
                          controller: weightController,
                          focusNode: weightNode,
                          keyboardType: TextInputType.number,
                          decoration: _buildInputDecoration(),
                          enabled: !ref.isLoading,
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return null;
                            }
                            final parsed = double.tryParse(value);
                            if (parsed == null) {
                              return 'Please enter a valid number';
                            }
                            return null;
                          },
                        ),

                        /// Height
                        const SizedBox(height: 12),
                        Text(
                          'Pet Height (cm)',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontMedium,
                          ),
                        ),
                        TextFormField(
                          controller: heightController,
                          focusNode: heightNode,
                          keyboardType: TextInputType.number,
                          decoration: _buildInputDecoration(),
                          enabled: !ref.isLoading,
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return null;
                            }
                            final parsed = double.tryParse(value);
                            if (parsed == null) {
                              return 'Please enter a valid number';
                            }
                            return null;
                          },
                        ),

                        /// Color
                        const SizedBox(height: 12),
                        Text(
                          'Pet Color',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontMedium,
                          ),
                        ),
                        DropdownButtonFormField<String>(
                          value: selectedColor,
                          decoration: _buildInputDecoration(),
                          items: colors
                              .map((color) =>
                                  DropdownMenuItem(value: color, child: Text(color)))
                              .toList(),
                          onChanged: ref.isLoading ? null : (value) {
                            setState(() {
                              selectedColor = value;
                              colorError = null;
                            });
                          },
                          validator: (value) {
                            if (value == null) {
                              return 'Please select a color';
                            }
                            return null;
                          },
                        ),

                        const SizedBox(height: 24),

                        /// Submit Button
                        Button(
                          width: double.infinity,
                          title: ref.isLoading ? 'Saving...' : 'Save',
                          onPressed: () async {
                            final isValid = _formKey.currentState?.validate() ?? false;

                            bool manualValid = true;

                            if (selectedGender == null) {
                              setState(() {
                                genderError = 'Please select a gender';
                              });
                              manualValid = false;
                            }

                            if (!manualValid || !isValid) {
                              return;
                            }

                            // Format date properly for PHP backend
                            String? formattedDate;
                            if (selectedDate != null) {
                              formattedDate = '${selectedDate!.year}-${selectedDate!.month.toString().padLeft(2, '0')}-${selectedDate!.day.toString().padLeft(2, '0')}';
                            }

                            final updatedAnimal = AnimalModel(
                              id: widget.animalModel.id,
                              type: selectedPetType!,
                              breed: selectedBreed,
                              name: petNameController.text.trim(),
                              birthDate: formattedDate, // Use formatted date instead of ISO string
                              gender: selectedGender!,
                              weight: double.tryParse(weightController.text.trim()),
                              height: double.tryParse(heightController.text.trim()),
                              color: selectedColor!,
                              owner: widget.animalModel.owner,
                              code: widget.animalModel.code,
                              qrCode: widget.animalModel.qrCode,
                              qrCodeUrl: widget.animalModel.qrCodeUrl,
                            );

                            await ref.updateAnimal(updatedAnimal);

                            if (ref.isSucess) {
                              // Update the local animal model with the response data
                              final updatedAnimalFromResponse = ref.animal ?? updatedAnimal;
                              
                              Provider.of<AnimalViewModel>(context, listen: false)
                                  .updateAnimal(updatedAnimalFromResponse);
                              
                              ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(content: Text(ref.message ?? 'Animal updated successfully!')));
                              
                              // Return the updated animal
                              Navigator.pop(context, updatedAnimalFromResponse);
                            } else {
                              ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(content: Text(ref.message ?? 'Update failed')));
                            }
                          },
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
            // Loading overlay
            if (ref.isLoading)
              Container(
                color: Colors.black.withOpacity(0.5),
                child: const Center(
                  child: CircularProgressIndicator(
                    strokeWidth: 4.0,
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                  ),
                ),
              ),
          ],
        );
      }),
    );
  }

  // Helper method to get the correct image provider
  ImageProvider<Object>? _getImageProvider(AnimalEditViewModel ref) {
    // Priority: local file > updated animal image > original animal image
    if (ref.animalProfile != null) {
      return FileImage(ref.animalProfile!);
    } else if (ref.animal?.imageUrl != null) {
      return NetworkImage(ref.animal!.imageUrl!);
    } else if (widget.animalModel.imageUrl != null) {
      return NetworkImage(widget.animalModel.imageUrl!);
    }
    return null;
  }
}