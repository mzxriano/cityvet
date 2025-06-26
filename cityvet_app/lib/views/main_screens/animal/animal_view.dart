import 'package:cityvet_app/modals/animal_modals/select_category_modal.dart';
import 'package:cityvet_app/views/main_screens/animal/animal_card.dart';
import 'package:cityvet_app/views/main_screens/animal/animal_form.dart';
import 'package:flutter/material.dart';

class AnimalView extends StatefulWidget {
  const AnimalView({super.key});

  @override
  State<AnimalView> createState() => _AnimalViewState();
}

class _AnimalViewState extends State<AnimalView> {
  List<AnimalCard> animalCards = [];

  void _openCategoryModal(BuildContext context) async {
    final selected = await showAnimalCategorySelectionModal(context);
    if (selected != null) {
      final createdAnimal = await Navigator.push(context, MaterialPageRoute(builder: (_) => AnimalForm()));
      if(createdAnimal != null) {
        setState(() {
          animalCards.add(createdAnimal);
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Owned Animals',
          style: TextStyle(
            fontFamily: 'Roboto', 
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 16),
        
        Expanded(
          child: GridView.builder(
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              mainAxisSpacing: 16,
              crossAxisSpacing: 16,
              childAspectRatio: 0.85,
            ),
            itemCount: animalCards.length + 1,
            itemBuilder: (context, index) {
              if (index == animalCards.length) {
                // Add new animal button
                return SizedBox(
                  child: InkWell(
                    onTap: () {
                      _openCategoryModal(context);
                    },
                    borderRadius: BorderRadius.circular(15),
                    child: Container(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(15),
                        color: Colors.transparent,
                        border: Border.all(
                          color: Colors.grey.shade500,
                          style: BorderStyle.solid,
                        ),
                      ),
                      child: const Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.add_circle_outline,
                            size: 40,
                            color: Color(0xFF9E9999),
                          ),
                          SizedBox(height: 8),
                          Text(
                            'Add Animal',
                            style: TextStyle(
                              fontSize: 14,
                              color: Color(0xFF9E9999),
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }
              return animalCards[index];
            },
          ),
        ),
      ],
    );
  }
}