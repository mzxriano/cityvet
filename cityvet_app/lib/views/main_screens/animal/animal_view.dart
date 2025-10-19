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
  String _selectedVaccinationStatus = 'All';
  final List<String> _vaccinationStatuses = ['All', 'Vaccinated', 'Not Vaccinated'];
  bool _isFiltersExpanded = false;

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

      // Vaccination status filtering
      bool matchesVaccination = _selectedVaccinationStatus == 'All';
      if (!matchesVaccination) {
        final hasVaccinations = animal.vaccinations?.isNotEmpty ?? false;
        matchesVaccination = (_selectedVaccinationStatus == 'Vaccinated' && hasVaccinations) ||
                           (_selectedVaccinationStatus == 'Not Vaccinated' && !hasVaccinations);
      }

      return matchesSearch && matchesVaccination;
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
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Owned Animals',
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: Config.primaryColor.withOpacity(0.1),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                '${animalViewModel.animals.length} Total',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: Config.fontSmall,
                  color: Config.primaryColor,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        
        // Search and Filter Section
        Container(
          width: double.infinity,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey.shade200),
            boxShadow: [
              BoxShadow(
                color: Colors.grey.withOpacity(0.1),
                spreadRadius: 1,
                blurRadius: 4,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            children: [
              // Header with toggle button
              Container(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Icon(
                      Icons.search,
                      color: Config.primaryColor,
                      size: 20,
                    ),
                    const SizedBox(width: 8),
                    const Expanded(
                      child: Text(
                        'Search & Filter',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    // Active filters indicator
                    if (_selectedVaccinationStatus != 'All' || _animalSearchQuery.isNotEmpty)
                      Container(
                        margin: const EdgeInsets.only(right: 8),
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: Config.primaryColor,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          'Active',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    // Toggle button
                    InkWell(
                      onTap: () {
                        setState(() {
                          _isFiltersExpanded = !_isFiltersExpanded;
                        });
                      },
                      borderRadius: BorderRadius.circular(20),
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        child: Icon(
                          _isFiltersExpanded ? Icons.expand_less : Icons.expand_more,
                          color: Config.primaryColor,
                          size: 24,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              
              // Expandable search and filter content
              if (_isFiltersExpanded)
                Container(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                  child: SingleChildScrollView(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // Search bar
                        Container(
                          width: double.infinity,
                          height: 45,
                          decoration: BoxDecoration(
                            color: Colors.grey.shade50,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: Colors.grey.shade300),
                          ),
                          child: TextField(
                            controller: _animalSearchController,
                            onChanged: (value) {
                              setState(() {
                                _animalSearchQuery = value;
                              });
                            },
                            decoration: InputDecoration(
                              hintText: 'Search animals by name, breed, or color...',
                              hintStyle: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontSmall,
                                color: Colors.grey.shade500,
                              ),
                              border: InputBorder.none,
                              contentPadding: const EdgeInsets.symmetric(horizontal: 15),
                              prefixIcon: Icon(Icons.search, color: Colors.grey.shade500),
                              suffixIcon: _animalSearchQuery.isNotEmpty 
                                ? IconButton(
                                    icon: Icon(Icons.clear, color: Colors.grey.shade500),
                                    onPressed: () {
                                      setState(() {
                                        _animalSearchController.clear();
                                        _animalSearchQuery = '';
                                      });
                                    },
                                  )
                                : null,
                            ),
                            style: const TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: Config.fontSmall,
                            ),
                          ),
                        ),
                        const SizedBox(height: 12),
                        
                        // Filter row
                        Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            // Vaccination status filter
                            Expanded(
                              child: Container(
                                height: 45,
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade50,
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(color: Colors.grey.shade300),
                                ),
                                child: DropdownButtonHideUnderline(
                                  child: ButtonTheme(
                                    alignedDropdown: true,
                                    child: DropdownButton<String>(
                                      value: _selectedVaccinationStatus,
                                      isExpanded: true,
                                      icon: Icon(Icons.arrow_drop_down, color: Colors.grey.shade500),
                                      items: _vaccinationStatuses.map((String status) {
                                        return DropdownMenuItem<String>(
                                          value: status,
                                          child: Row(
                                            children: [
                                              Icon(
                                                status == 'All' ? Icons.medical_services :
                                                status == 'Vaccinated' ? Icons.verified :
                                                Icons.warning,
                                                size: 16,
                                                color: status == 'Vaccinated' ? Colors.green :
                                                       status == 'Not Vaccinated' ? Colors.orange :
                                                       Colors.grey.shade600,
                                              ),
                                              const SizedBox(width: 8),
                                              Expanded(
                                                child: Text(
                                                  status,
                                                  style: TextStyle(
                                                    fontFamily: Config.primaryFont,
                                                    fontSize: Config.fontSmall,
                                                    color: Colors.grey.shade700,
                                                  ),
                                                  overflow: TextOverflow.ellipsis,
                                                ),
                                              ),
                                            ],
                                          ),
                                        );
                                      }).toList(),
                                      onChanged: (String? newValue) {
                                        setState(() {
                                          _selectedVaccinationStatus = newValue ?? 'All';
                                        });
                                      },
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                        if (_selectedVaccinationStatus != 'All' || _animalSearchQuery.isNotEmpty)
                          Container(
                            width: double.infinity,
                            margin: const EdgeInsets.only(top: 12),
                            child: Wrap(
                              spacing: 8,
                              runSpacing: 8,
                              alignment: WrapAlignment.start,
                              children: [
                                if (_selectedVaccinationStatus != 'All')
                                  Chip(
                                    label: Text(
                                      _selectedVaccinationStatus,
                                      style: const TextStyle(
                                        fontSize: 12,
                                        color: Colors.white,
                                      ),
                                    ),
                                    backgroundColor: _selectedVaccinationStatus == 'Vaccinated' ? Colors.green : Colors.orange,
                                    deleteIcon: const Icon(Icons.close, size: 16, color: Colors.white),
                                    onDeleted: () {
                                      setState(() {
                                        _selectedVaccinationStatus = 'All';
                                      });
                                    },
                                  ),
                              ],
                            ),
                          ),
                        if (_selectedVaccinationStatus != 'All' || _animalSearchQuery.isNotEmpty)
                          Container(
                            width: double.infinity,
                            margin: const EdgeInsets.only(top: 12),
                            child: TextButton.icon(
                              onPressed: () {
                                setState(() {
                                  _selectedVaccinationStatus = 'All';
                                  _animalSearchController.clear();
                                  _animalSearchQuery = '';
                                });
                              },
                              icon: Icon(
                                Icons.clear_all,
                                size: 16,
                                color: Colors.grey.shade600,
                              ),
                              label: Text(
                                'Clear All Filters',
                                style: TextStyle(
                                  fontFamily: Config.primaryFont,
                                  fontSize: Config.fontSmall,
                                  color: Colors.grey.shade600,
                                ),
                              ),
                              style: TextButton.styleFrom(
                                backgroundColor: Colors.grey.shade100,
                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(6),
                                ),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                ),
            ],
          ),
        ),
        const SizedBox(height: 16),
        // Results summary
        Container(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Text(
            'Showing ${filteredAnimals.length} of ${animalViewModel.animals.length} animals',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontSmall,
              color: Colors.grey.shade600,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
        
        Expanded(
          child: filteredAnimals.isEmpty
              ? SingleChildScrollView(
                  child: SizedBox(
                    height: MediaQuery.of(context).size.height * 0.4,
                    child: _buildEmptyState(animalViewModel),
                  ),
                )
              : GridView.builder(
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    mainAxisSpacing: 16,
                    crossAxisSpacing: 16,
                    childAspectRatio: 0.80,
                  ),
                  itemCount: animalCards.length + (animalViewModel.animals.length < 10 ? 1 : 0),
                  itemBuilder: (context, index) {
                    if (index == animalCards.length && animalViewModel.animals.length < 10) {
                      return _buildAddAnimalButton(context, animalViewModel);
                    }
                    return animalCards[index];
                  },
                ),
        ),
      ],
    );
  }

  Widget _buildEmptyState(AnimalViewModel viewModel) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.search_off,
            size: 64,
            color: Colors.grey.shade400,
          ),
          const SizedBox(height: 16),
          Text(
            'No animals found',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontMedium,
              color: Colors.grey.shade600,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Try adjusting your search or filters',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontSmall,
              color: Colors.grey.shade500,
            ),
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: () => _openAnimalForm(context, viewModel),
            icon: const Icon(Icons.add, color: Colors.white),
            label: const Text(
              'Add First Animal',
              style: TextStyle(color: Colors.white),
            ),
            style: ElevatedButton.styleFrom(
              backgroundColor: Config.primaryColor,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
          ),
        ],
      ),
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