import 'package:cityvet_app/modals/animal_modals/select_category_modal.dart';
import 'package:cityvet_app/viewmodels/animal_view_model.dart';
import 'package:cityvet_app/views/main_screens/animal/animal_card.dart';
import 'package:cityvet_app/views/main_screens/animal/animal_form.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class AnimalView extends StatefulWidget {
  const AnimalView({super.key});

  @override
  State<AnimalView> createState() => _AnimalViewState();
}

class _AnimalViewState extends State<AnimalView> {
  @override
  void initState() {
    super.initState();

    // Delay to ensure context is available
    Future.microtask(() {
      final animalViewModel = context.read<AnimalViewModel>();
      animalViewModel.fetchAnimals();
    });
  }

  void _openCategoryModal(BuildContext context, AnimalViewModel animalViewModel) async {
    final selected = await showAnimalCategorySelectionModal(context);
    if (selected != null) {
      final createdAnimal = await Navigator.push(context, MaterialPageRoute(builder: (_) => AnimalForm()));
      if (createdAnimal == true) {
        animalViewModel.fetchAnimals();
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final animalViewModel = context.watch<AnimalViewModel>();
    final animalCards = animalViewModel.animals
        .map((animal) => AnimalCard(animalModel: animal))
        .toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Owned Animals',
          style: TextStyle(
            fontFamily: 'Roboto',
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 16),
        Expanded(
          child: animalCards.isEmpty
              ? const Center(child: Text('No animals found.'))
              : GridView.builder(
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    mainAxisSpacing: 16,
                    crossAxisSpacing: 16,
                    childAspectRatio: 0.80,
                  ),
                  itemCount: animalCards.length + 1,
                  itemBuilder: (context, index) {
                    if (index == animalCards.length) {
                      return _buildAddAnimalButton(context, animalViewModel);
                    }
                    return animalCards[index];
                  },
                ),
        ),
      ],
    );
  }

  Widget _buildAddAnimalButton(BuildContext context, AnimalViewModel viewModel) {
    return InkWell(
      onTap: () => _openCategoryModal(context, viewModel),
      borderRadius: BorderRadius.circular(15),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(15),
          color: Colors.transparent,
          border: Border.all(
            color: Colors.grey.shade500,
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
    );
  }
}

