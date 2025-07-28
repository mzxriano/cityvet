import 'package:cityvet_app/modals/animal_modals/select_category_modal.dart';
import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/utils/config.dart';
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
  final TextEditingController _animalSearchController = TextEditingController();
  String _animalSearchQuery = '';

  @override
  void initState() {
    super.initState();

    Future.microtask(() {
      final animalViewModel = context.read<AnimalViewModel>();
      animalViewModel.fetchAnimals();

      final message = animalViewModel.message;
      print(message);
      if(message != null && context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
      }
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

  void _deleteAnimal(BuildContext context, AnimalViewModel animalViewModel, AnimalModel animal) async {
    // Show confirmation dialog
    final bool? confirmed = await showDialog<bool>(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Delete Animal'),
          content: Text('Are you sure you want to delete ${animal.name}'),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              child: const Text('Cancel'),
            ),
            TextButton(
              onPressed: () => Navigator.of(context).pop(true),
              style: TextButton.styleFrom(
                foregroundColor: Colors.red,
              ),
              child: const Text('Delete'),
            ),
          ],
        );
      },
    );

    if (confirmed == true) {
      try {
        // Call the delete method from your view model
        await animalViewModel.deleteAnimal(animal);
        
        // Show success message
        if (animalViewModel.message?.isNotEmpty ?? false) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(animalViewModel.message ?? 'No Message'),
              backgroundColor: Colors.green,
            ),
          );
        }

        // Clear message
        animalViewModel.setMessage(null);
        
        // Refresh the animal list
        animalViewModel.fetchAnimals();
      } catch (e) {
        // Show error message
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Failed to delete animal: $e'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final animalViewModel = context.watch<AnimalViewModel>();

    if(animalViewModel.isLoading) {
      return Center(
        child: CircularProgressIndicator(),
      );
    }

    final filteredAnimals = animalViewModel.animals.where((animal) =>
      _animalSearchQuery.isEmpty ||
      animal.name.toLowerCase().contains(_animalSearchQuery.toLowerCase()) ||
      (animal.breed?.toLowerCase().contains(_animalSearchQuery.toLowerCase()) ?? false) ||
      animal.type.toLowerCase().contains(_animalSearchQuery.toLowerCase()) ||
      animal.color.toLowerCase().contains(_animalSearchQuery.toLowerCase())
    ).toList();

    final animalCards = filteredAnimals
        .map((animal) => AnimalCard(
            animalModel: animal,
            onDelete: () => _deleteAnimal(context, animalViewModel, animal),
          ))
        .toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Owned Animals',
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 16),
        // Search bar for animals
        Container(
          width: double.infinity,
          constraints: const BoxConstraints(maxWidth: 500),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(8),
          ),
          child: TextField(
            controller: _animalSearchController,
            onChanged: (value) {
              setState(() {
                _animalSearchQuery = value;
              });
            },
            decoration: const InputDecoration(
              hintText: 'Search animals...',
              hintStyle: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontSmall,
                color: Colors.grey,
              ),
              border: InputBorder.none,
              contentPadding: EdgeInsets.symmetric(vertical: 8, horizontal: 15),
              prefixIcon: Icon(Icons.search, color: Colors.grey),
            ),
            style: const TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontSmall,
            ),
          ),
        ),
        const SizedBox(height: 16),
        Expanded(
          child: GridView.builder(
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