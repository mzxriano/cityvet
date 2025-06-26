import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/components/text_field.dart';
import 'package:cityvet_app/modals/confirmation_modal.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/views/main_screens/animal/animal_card.dart';
import 'package:flutter/material.dart';

class AnimalForm extends StatefulWidget {
  const AnimalForm({super.key});

  @override
  State<AnimalForm> createState() => _AnimalFormState();
}

class _AnimalFormState extends State<AnimalForm> {
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

  Map<String, List<String>> petBreeds = {
    'Dog': ['Labrador', 'Poodle', 'Bulldog'],
    'Cat': ['Persian', 'Siamese', 'Bengal'],
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
  Widget build(BuildContext context) {
    Config().init(context);
    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          onPressed:() async {
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
              
              // Pet Profile
              SizedBox(
                child: Center(
                  child: Stack(
                    children: [
                      CircleAvatar(
                        radius: 70,
                      ),
                      Positioned(
                        right: 5,
                        bottom: 0,
                        child: Icon(Icons.edit, size: 40)
                      ),
                    ],
                  ),
                ),
              ),

              /// Pet Type
              Text('Pet Type', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
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
                onChanged: (value) {
                  setState(() {
                    selectedPetType = value;
                    selectedBreed = null;
                  });
                },
              ),
              const SizedBox(height: 12),

              /// Breed
              Text('Pet Breed', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
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
                onChanged: (value) {
                  setState(() {
                    selectedBreed = value;
                  });
                },
              ),
              const SizedBox(height: 12),

              /// Pet Name
              Text('Pet Name', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
              CustomTextField(
                controller: petNameController,
                node: petNameNode,
                textInputType: TextInputType.name,
                isObscured: false,
                isFocused: petNameNode.hasFocus,
              ),
              const SizedBox(height: 12),

              /// Date of Birth
              Text('Pet Date of Birth', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
              InkWell(
                onTap: () async {
                  final date = await showDatePicker(
                    context: context,
                    initialDate: DateTime(2020),
                    firstDate: DateTime(2000),
                    lastDate: DateTime.now(),
                  );
                  if (date != null) {
                    setState(() {
                      selectedDate = date;
                    });
                  }
                },
                child: InputDecorator(
                  decoration: const InputDecoration(labelText: 'Date of Birth'),
                  child: Text(
                    selectedDate != null
                        ? '${selectedDate!.month}/${selectedDate!.day}/${selectedDate!.year}'
                        : 'Select Date',
                    style: TextStyle(
                      color: selectedDate != null ? Colors.black : Colors.grey,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 12),

              /// Gender
             Text('Pet Gender', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
              Row(
                children: ['Male', 'Female'].map((gender) {
                  return Row(
                    children: [
                      Radio<String>(
                        value: gender,
                        groupValue: selectedGender,
                        onChanged: (value) {
                          setState(() {
                            selectedGender = value;
                          });
                        },
                      ),
                      Text(gender),
                    ],
                  );
                }).toList(),
              ),
              const SizedBox(height: 12),

              /// Weight
              Text('Pet Weight', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
              CustomTextField(
                controller: weightController,
                node: weightNode,
                textInputType: TextInputType.number,
                isObscured: false,
                isFocused: weightNode.hasFocus,
              ),
              const SizedBox(height: 12),

              /// Height
              Text('Pet Height', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
              CustomTextField(
                controller: heightController,
                node: heightNode,
                textInputType: TextInputType.number,
                isObscured: false,
                isFocused: heightNode.hasFocus,
              ),
              const SizedBox(height: 12),

              /// Color
              Text('Pet Color', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium)),
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
                onChanged: (value) {
                  setState(() {
                    selectedColor = value;
                  });
                },
              ),
              const SizedBox(height: 24),

              /// Submit Button
              Button(
                width: double.infinity, 
                title: 'Submit', 
                onPressed: (){
                  if(selectedPetType != null) {
                    print(selectedPetType);
                    Navigator.pop(context, AnimalCard(type: selectedPetType!));
                  }
                }
              ),
            ],
          ),
        ),
      ),
    );
  }
}
