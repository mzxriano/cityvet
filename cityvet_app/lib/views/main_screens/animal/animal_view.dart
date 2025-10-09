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
  String _selectedType = 'All';
  final List<String> _animalTypes = ['All', 'Dog', 'Cat', 'Bird', 'Others'];

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

  void _openAnimalForm(BuildContext context, AnimalViewModel animalViewModel) async {
    final createdAnimal = await Navigator.push(context, MaterialPageRoute(builder: (_) => AnimalForm()));
    if (createdAnimal == true) {
      animalViewModel.fetchAnimals();
    }
  }

  void _markAsDeceased(BuildContext context, AnimalViewModel animalViewModel, AnimalModel animal) async {
    final TextEditingController causeController = TextEditingController();
    final TextEditingController notesController = TextEditingController();
    DateTime selectedDate = DateTime.now();

    final bool? confirmed = await showDialog<bool>(
      context: context,
      builder: (BuildContext context) {
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              title: Text('Mark ${animal.name} as Deceased'),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // Date picker
                    ListTile(
                      leading: const Icon(Icons.calendar_today),
                      title: const Text('Date of Death'),
                      subtitle: Text(
                        '${selectedDate.day}/${selectedDate.month}/${selectedDate.year}',
                      ),
                      onTap: () async {
                        final DateTime? picked = await showDatePicker(
                          context: context,
                          initialDate: selectedDate,
                          firstDate: DateTime(2000),
                          lastDate: DateTime.now(),
                        );
                        if (picked != null && picked != selectedDate) {
                          setState(() {
                            selectedDate = picked;
                          });
                        }
                      },
                    ),
                    const SizedBox(height: 16),
                    // Cause field
                    TextField(
                      controller: causeController,
                      decoration: const InputDecoration(
                        labelText: 'Cause of Death (Optional)',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 16),
                    // Notes field
                    TextField(
                      controller: notesController,
                      decoration: const InputDecoration(
                        labelText: 'Additional Notes (Optional)',
                        border: OutlineInputBorder(),
                      ),
                      maxLines: 3,
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.of(context).pop(false),
                  child: const Text('Cancel'),
                ),
                ElevatedButton(
                  onPressed: () => Navigator.of(context).pop(true),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.orange,
                    foregroundColor: Colors.white,
                  ),
                  child: const Text('Mark as Deceased'),
                ),
              ],
            );
          },
        );
      },
    );

    if (confirmed == true) {
      try {
        await animalViewModel.archiveAnimal(
          animal,
          archiveType: 'deceased',
          archiveDate: selectedDate.toIso8601String().split('T')[0], // YYYY-MM-DD format
          reason: causeController.text.isNotEmpty ? causeController.text : null,
          notes: notesController.text.isNotEmpty ? notesController.text : null,
        );

        if (animalViewModel.message?.isNotEmpty ?? false) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(animalViewModel.message ?? 'No Message'),
              backgroundColor: Colors.orange,
            ),
          );
        }

        animalViewModel.setMessage(null);
        animalViewModel.fetchAnimals();
      } catch (e) {
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Failed to mark animal as deceased: $e'),
              backgroundColor: Colors.red,
            ),
          );
        }
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
        // Archive as deleted
        await animalViewModel.archiveAnimal(
          animal,
          archiveType: 'deleted',
          archiveDate: DateTime.now().toIso8601String().split('T')[0],
          reason: 'Deleted by owner',
        );
        
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

    final filteredAnimals = animalViewModel.animals.where((animal) {
      final matchesSearch = _animalSearchQuery.isEmpty ||
          animal.name.toLowerCase().contains(_animalSearchQuery.toLowerCase()) ||
          (animal.breed?.toLowerCase().contains(_animalSearchQuery.toLowerCase()) ?? false) ||
          animal.type.toLowerCase().contains(_animalSearchQuery.toLowerCase()) ||
          animal.color.toLowerCase().contains(_animalSearchQuery.toLowerCase());

      final matchesType = _selectedType == 'All' || 
          animal.type.toLowerCase() == _selectedType.toLowerCase();

      return matchesSearch && matchesType;
    }).toList();

    final animalCards = filteredAnimals
        .map((animal) => AnimalCard(
            animalModel: animal,
            onDelete: () => _deleteAnimal(context, animalViewModel, animal),
            onMarkAsDeceased: () => _markAsDeceased(context, animalViewModel, animal),
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
        // Search bar and filters for animals
        Container(
          width: double.infinity,
          child: Column(
            children: [
              // Search and Type filter row
              Row(
                children: [
                  Expanded(
                    flex: 3,
                    child: Container(
                  height: 45,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.grey.shade300),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.grey.withOpacity(0.1),
                        spreadRadius: 1,
                        blurRadius: 2,
                        offset: const Offset(0, 1),
                      ),
                    ],
                  ),
                  child: TextField(
                    controller: _animalSearchController,
                    onChanged: (value) {
                      setState(() {
                        _animalSearchQuery = value;
                      });
                    },
                    decoration: InputDecoration(
                      hintText: 'Search animals...',
                      hintStyle: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontSmall,
                        color: Colors.grey.shade500,
                      ),
                      border: InputBorder.none,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 15),
                      prefixIcon: Icon(Icons.search, color: Colors.grey.shade500),
                    ),
                    style: const TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontSmall,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                flex: 2,
                child: Container(
                  height: 45,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.grey.shade300),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.grey.withOpacity(0.1),
                        spreadRadius: 1,
                        blurRadius: 2,
                        offset: const Offset(0, 1),
                      ),
                    ],
                  ),
                  child: DropdownButtonHideUnderline(
                    child: ButtonTheme(
                      alignedDropdown: true,
                      child: DropdownButton<String>(
                        value: _selectedType,
                        isExpanded: true,
                        icon: Icon(Icons.arrow_drop_down, color: Colors.grey.shade500),
                        items: _animalTypes.map((String type) {
                          return DropdownMenuItem<String>(
                            value: type,
                            child: Text(
                              type,
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontSmall,
                                color: Colors.grey.shade700,
                              ),
                            ),
                          );
                        }).toList(),
                        onChanged: (String? newValue) {
                          setState(() {
                            _selectedType = newValue ?? 'All';
                          });
                        },
                      ),
                    ),
                  ),
                ),
              ),
                ],
              ),
            ],
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
      onTap: () => _openAnimalForm(context, viewModel),
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