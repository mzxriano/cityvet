import 'dart:io';

import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/components/label_text.dart';
import 'package:cityvet_app/components/text_field.dart';
import 'package:cityvet_app/modals/confirmation_modal.dart';
import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/utils/image_picker.dart';
import 'package:cityvet_app/viewmodels/animal_form_view_model.dart';
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

  String? selectedPetType;
  String? selectedBreed;
  String? selectedGender;
  DateTime? selectedDate;
  String? selectedColor;

  Map<String, List<String>> petBreeds = {
    'Dog': ['No Breed','Labrador', 'Poodle', 'Bulldog', 'Golden Retriever', 'Mixed-Breed'],
    'Cat': ['No Breed', 'Persian', 'Siamese', 'Bengal', 'British Shorthair', 'Mixed-Breed'],
  };

  List<String> colors = ['Black', 'Brown', 'White', 'Golden', 'Gray', 'Orange'];

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
                title: const Text('Register Animal'),
              ),
              body: Padding(
                padding: Config.paddingScreen,
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      
                      // Pet Profile - Fixed: Now using Consumer to listen to changes
                      SizedBox(
                        child: Center(
                          child: CircleAvatar(
                            backgroundImage: formRef.animalProfile != null
                                ? FileImage(formRef.animalProfile!)
                                : null,
                            radius: 70,
                            backgroundColor: Colors.grey[300],
                            child: formRef.animalProfile == null
                                ? IconButton(
                                    onPressed: formRef.isLoading ? null : () async {
                                      final pickedImage = await CustomImagePicker().pickFromGallery();
                                      if(pickedImage == null) return;
                                      formRef.setAnimalProfile(File(pickedImage.path));
                                    },
                                    icon: const Icon(
                                      Icons.camera_alt_rounded,
                                      size: 50,
                                      color: Colors.grey,
                                    ),
                                  )
                                : InkWell(
                                    onTap: formRef.isLoading ? null : () async {
                                      final pickedImage = await CustomImagePicker().pickFromGallery();
                                      if(pickedImage == null) return;
                                      formRef.setAnimalProfile(File(pickedImage.path));
                                    },
                                    child: Container(
                                      width: 140,
                                      height: 140,
                                      decoration: BoxDecoration(
                                        shape: BoxShape.circle,
                                        color: Colors.black.withOpacity(0.3),
                                      ),
                                      child: const Icon(
                                        Icons.camera_alt_rounded,
                                        size: 40,
                                        color: Colors.white,
                                      ),
                                    ),
                                  ),
                          ),
                        ),
                      ),

                      /// Pet Type
                      LabelText(label: 'Species ', isRequired: true),
                      DropdownButtonFormField<String>(
                        value: selectedPetType,
                        decoration: InputDecoration(
                          filled: true,
                          fillColor: Config.secondaryColor,
                          contentPadding: Config.paddingTextfield,
                          border: const OutlineInputBorder(
                            borderRadius: BorderRadius.all(Radius.circular(10)),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderSide: BorderSide(
                              color:Colors.transparent,
                            ),
                            borderRadius: const BorderRadius.all(Radius.circular(10)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderSide: BorderSide(
                              color:Config.primaryColor,
                              width: 2,
                            ),
                            borderRadius: const BorderRadius.all(Radius.circular(10)),
                          ),
                        ),
                        items: petBreeds.keys.map((type) {
                          return DropdownMenuItem(value: type, child: Text(type));
                        }).toList(),
                        onChanged: formRef.isLoading ? null : (value) {
                          setState(() {
                            selectedPetType = value;
                            selectedBreed = null;
                          });
                        },
                      ),
                      const SizedBox(height: 12),

                      /// Breed
                      LabelText(label: 'Breed ', isRequired: true),
                      DropdownButtonFormField<String>(
                        value: selectedBreed,
                        decoration: InputDecoration(
                          filled: true,
                          fillColor: Config.secondaryColor,
                          contentPadding: Config.paddingTextfield,
                          border: const OutlineInputBorder(
                            borderRadius: BorderRadius.all(Radius.circular(10)),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderSide: BorderSide(
                              color:Colors.transparent,
                            ),
                            borderRadius: const BorderRadius.all(Radius.circular(10)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderSide: BorderSide(
                              color:Config.primaryColor,
                              width: 2,
                            ),
                            borderRadius: const BorderRadius.all(Radius.circular(10)),
                          ),
                        ),
                        items: (selectedPetType != null
                                ? petBreeds[selectedPetType]!
                                : <String>[])
                            .map((breed) => DropdownMenuItem(value: breed, child: Text(breed)))
                            .toList(),
                        onChanged: formRef.isLoading ? null : (value) {
                          setState(() {
                            selectedBreed = value;
                          });
                        },
                      ),
                      const SizedBox(height: 12),

                      /// Pet Name
                      LabelText(label: 'Name ', isRequired: true),
                      CustomTextField(
                        controller: petNameController,
                        node: petNameNode,
                        textInputType: TextInputType.name,
                        isObscured: false,
                        isFocused: petNameNode.hasFocus,
                      ),
                      const SizedBox(height: 12),

                      /// Date of Birth
                      LabelText(label: 'Birthdate ', isRequired: false),
                      InkWell(
                        onTap: formRef.isLoading ? null : () async {
                          final date = await showDatePicker(
                            context: context,
                            initialDate: DateTime.now(),
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
                                  ? (formRef.isLoading ? Colors.grey : Colors.black) 
                                  : Colors.grey,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),

                      /// Gender
                     LabelText(label: 'Gender ', isRequired: true),
                      Row(
                        children: ['Male', 'Female'].map((gender) {
                          return Row(
                            children: [
                              Radio<String>(
                                value: gender,
                                groupValue: selectedGender,
                                onChanged: formRef.isLoading ? null : (value) {
                                  setState(() {
                                    selectedGender = value;
                                  });
                                },
                              ),
                              Text(
                                gender,
                                style: TextStyle(
                                  color: formRef.isLoading ? Colors.grey : Colors.black,
                                ),
                              ),
                            ],
                          );
                        }).toList(),
                      ),
                      const SizedBox(height: 12),

                      /// Weight
                      LabelText(label: 'Weight (kg) ', isRequired: false),
                      CustomTextField(
                        controller: weightController,
                        node: weightNode,
                        textInputType: TextInputType.number,
                        isObscured: false,
                        isFocused: weightNode.hasFocus,
                      ),
                      const SizedBox(height: 12),

                      /// Height
                      LabelText(label: 'Height (cm) ', isRequired: false),
                      CustomTextField(
                        controller: heightController,
                        node: heightNode,
                        textInputType: TextInputType.number,
                        isObscured: false,
                        isFocused: heightNode.hasFocus,
                      ),
                      const SizedBox(height: 12),

                      /// Color
                      LabelText(label: 'Color ', isRequired: true),
                      DropdownButtonFormField<String>(
                        value: selectedColor,
                        decoration: InputDecoration(
                          filled: true,
                          fillColor: Config.secondaryColor,
                          contentPadding: Config.paddingTextfield,
                          border: const OutlineInputBorder(
                            borderRadius: BorderRadius.all(Radius.circular(10)),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderSide: BorderSide(
                              color:Colors.transparent,
                            ),
                            borderRadius: const BorderRadius.all(Radius.circular(10)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderSide: BorderSide(
                              color:Config.primaryColor,
                              width: 2,
                            ),
                            borderRadius: const BorderRadius.all(Radius.circular(10)),
                          ),
                        ),
                        items: colors
                            .map((color) => DropdownMenuItem(value: color, child: Text(color)))
                            .toList(),
                        onChanged: formRef.isLoading ? null : (value) {
                          setState(() {
                            selectedColor = value;
                          });
                        },
                      ),

                      const SizedBox(height: 12),

                      /// Unique Spot
                      LabelText(label: 'Unique Spot ', isRequired: false),
                      CustomTextField(
                        controller: uniqueSpotController,
                        node: uniqueSpotNode,
                        textInputType: TextInputType.text,
                        isObscured: false,
                        isFocused: uniqueSpotNode.hasFocus,
                      ),
                      const SizedBox(height: 12),

                      /// Known Condition
                      LabelText(label: 'Known Conditions ', isRequired: false),
                      CustomTextField(
                        controller: knownConditionsController,
                        node: knownConditionsNode,
                        textInputType: TextInputType.text,
                        isObscured: false,
                        isFocused: knownConditionsNode.hasFocus,
                      ),
                      const SizedBox(height: 24),

                      /// Submit Button
                      Button(
                        width: double.infinity, 
                        title: formRef.isLoading ? 'Submitting...' : 'Submit',
                        onPressed: () async {
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
                            print('na create ba');
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(content: Text(formRef.message!)),
                            );
                            Navigator.pop(context, true);
                          }
                        }
                      ),
                    ],
                  ),
                ),
              ),
            ),
            // Loading overlay
            if (formRef.isLoading)
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
      },
    );
  }
}