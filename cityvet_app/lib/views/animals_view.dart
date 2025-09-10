import 'package:cityvet_app/models/animal_model.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/utils/role_constant.dart';
import 'package:cityvet_app/viewmodels/animal_view_model.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:cityvet_app/views/main_screens/animal/animal_preview.dart';

class AnimalManagementView extends StatefulWidget {
  const AnimalManagementView({super.key});

  @override
  State<AnimalManagementView> createState() => _AnimalManagementViewState();
}

class _AnimalManagementViewState extends State<AnimalManagementView> {
  String searchQuery = '';
  String selectedType = 'All';
  String selectedVaccinationStatus = 'All'; // New vaccination filter
  final TextEditingController _searchController = TextEditingController();
  bool _showFilterOptions = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AnimalViewModel>().fetchAllAnimals();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Consumer2<UserViewModel, AnimalViewModel>(
      builder: (context, userViewModel, animalViewModel, _) {
        final isOwner = userViewModel.user?.role == Role.owner;
        final animals = animalViewModel.allAnimals ?? [];

        // Enhanced filtering logic with vaccination status
        final filteredAnimals = animals.where((animal) {
          // Search in multiple fields
          final query = searchQuery.toLowerCase();
          final matchesName = animal.name.toLowerCase().contains(query);
          final matchesType = animal.type.toLowerCase().contains(query);
          final matchesBreed = (animal.breed ?? '').toLowerCase().contains(query);
          
          final matchesSearch = searchQuery.isEmpty || matchesName || matchesType || matchesBreed;
          final matchesTypeFilter = selectedType == 'All' || animal.type.toLowerCase() == selectedType.toLowerCase();
          
          // Vaccination status filtering
          final isVaccinated = animal.vaccinations != null && animal.vaccinations!.isNotEmpty;
          final matchesVaccinationFilter = selectedVaccinationStatus == 'All' ||
              (selectedVaccinationStatus == 'Vaccinated' && isVaccinated) ||
              (selectedVaccinationStatus == 'Not Vaccinated' && !isVaccinated);
          
          return matchesSearch && matchesTypeFilter && matchesVaccinationFilter;
        }).toList();

        // Sort filtered animals alphabetically by name
        filteredAnimals.sort((a, b) => a.name.compareTo(b.name));

        return Scaffold(
          appBar: AppBar(
            title: const Text('Animals'),
            actions: [
              // Results counter
              if (searchQuery.isNotEmpty || selectedType != 'All' || selectedVaccinationStatus != 'All')
                Padding(
                  padding: const EdgeInsets.only(right: 16),
                  child: Center(
                    child: Text(
                      '${filteredAnimals.length} result${filteredAnimals.length != 1 ? 's' : ''}',
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.grey[600],
                      ),
                    ),
                  ),
                ),
            ],
          ),
          body: animalViewModel.isLoading
              ? const Center(child: CircularProgressIndicator())
              : animalViewModel.errors != null
                  ? _buildError(animalViewModel.errors!)
                  : Column(
                      children: [
                        _buildEnhancedSearchBar(animals),
                        if (_showFilterOptions) _buildFilterChips(animals),
                        Expanded(child: _buildAnimalList(filteredAnimals)),
                      ],
                    ),
          floatingActionButton: isOwner
              ? FloatingActionButton.extended(
                  backgroundColor: Config.primaryColor,
                  icon: const Icon(Icons.add),
                  label: const Text('Add Animal'),
                  onPressed: () {},
                )
              : null,
        );
      },
    );
  }

  /// Enhanced search bar with integrated filter
  Widget _buildEnhancedSearchBar(List animals) {
    return Container(
      padding: const EdgeInsets.all(12.0),
      decoration: BoxDecoration(
        color: Colors.grey[50],
        border: Border(bottom: BorderSide(color: Colors.grey[200]!)),
      ),
      child: Column(
        children: [
          TextField(
            controller: _searchController,
            decoration: InputDecoration(
              hintText: "Search by name, type, or breed...",
              prefixIcon: const Icon(Icons.search),
              suffixIcon: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Clear search button
                  if (searchQuery.isNotEmpty)
                    IconButton(
                      icon: const Icon(Icons.clear, size: 20),
                      onPressed: () {
                        _searchController.clear();
                        setState(() => searchQuery = '');
                      },
                    ),
                  // Filter toggle button
                  IconButton(
                    icon: Icon(
                      _showFilterOptions ? Icons.filter_list : Icons.tune,
                      color: (selectedType != 'All' || selectedVaccinationStatus != 'All') 
                          ? Config.primaryColor : null,
                    ),
                    onPressed: () {
                      setState(() => _showFilterOptions = !_showFilterOptions);
                    },
                  ),
                ],
              ),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide.none,
              ),
              filled: true,
              fillColor: Colors.white,
              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            ),
            onChanged: (value) {
              setState(() => searchQuery = value);
            },
          ),
        ],
      ),
    );
  }

  /// Enhanced filter chips for animal types and vaccination status
  Widget _buildFilterChips(List animals) {
    final animalList = animals.cast<AnimalModel>();
    final uniqueTypes = ['All', ...{for (var a in animalList) a.type}];
    final vaccinationOptions = ['All', 'Vaccinated', 'Not Vaccinated'];

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.grey[50],
        border: Border(bottom: BorderSide(color: Colors.grey[200]!)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Animal Type Filter
          Text(
            'Filter by type:',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w500,
              color: Colors.grey[700],
            ),
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            children: uniqueTypes.map((type) {
              final isSelected = selectedType == type;
              return FilterChip(
                label: Text(
                  type,
                  style: TextStyle(
                    color: isSelected ? Colors.white : Colors.grey[700],
                    fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                  ),
                ),
                selected: isSelected,
                onSelected: (selected) {
                  setState(() {
                    selectedType = selected ? type : 'All';
                  });
                },
                selectedColor: Config.primaryColor,
                checkmarkColor: Colors.white,
                backgroundColor: Colors.grey[200],
              );
            }).toList(),
          ),
          
          const SizedBox(height: 16),
          
          // Vaccination Status Filter
          Text(
            'Filter by vaccination status:',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w500,
              color: Colors.grey[700],
            ),
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            children: vaccinationOptions.map((status) {
              final isSelected = selectedVaccinationStatus == status;
              final Color chipColor = status == 'Vaccinated' 
                  ? Colors.green 
                  : status == 'Not Vaccinated' 
                      ? Colors.orange 
                      : Config.primaryColor;
              
              return FilterChip(
                label: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (status == 'Vaccinated')
                      Icon(
                        Icons.verified,
                        size: 16,
                        color: isSelected ? Colors.white : Colors.green,
                      ),
                    if (status == 'Not Vaccinated')
                      Icon(
                        Icons.warning_amber_rounded,
                        size: 16,
                        color: isSelected ? Colors.white : Colors.orange,
                      ),
                    if (status != 'All') const SizedBox(width: 4),
                    Text(
                      status,
                      style: TextStyle(
                        color: isSelected ? Colors.white : Colors.grey[700],
                        fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                      ),
                    ),
                  ],
                ),
                selected: isSelected,
                onSelected: (selected) {
                  setState(() {
                    selectedVaccinationStatus = selected ? status : 'All';
                  });
                },
                selectedColor: chipColor,
                checkmarkColor: Colors.white,
                backgroundColor: Colors.grey[200],
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  /// Enhanced animal list with vaccination status indicators
  Widget _buildAnimalList(List animals) {
    if (animals.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.pets_outlined,
              size: 80,
              color: Colors.grey[400],
            ),
            const SizedBox(height: 16),
            Text(
              searchQuery.isNotEmpty || selectedType != 'All' || selectedVaccinationStatus != 'All'
                  ? 'No animals match your filters'
                  : 'No animals found',
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: 18,
                fontWeight: FontWeight.w500,
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 8),
            if (searchQuery.isNotEmpty || selectedType != 'All' || selectedVaccinationStatus != 'All')
              TextButton(
                onPressed: () {
                  _searchController.clear();
                  setState(() {
                    searchQuery = '';
                    selectedType = 'All';
                    selectedVaccinationStatus = 'All';
                  });
                },
                child: const Text('Clear all filters'),
              ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: animals.length,
      itemBuilder: (context, index) {
        final animal = animals[index];
        final isVaccinated = animal.vaccinations != null && animal.vaccinations!.isNotEmpty;
        final vaccinationCount = animal.vaccinations?.length ?? 0;

        return Card(
          elevation: 2,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          margin: const EdgeInsets.symmetric(vertical: 6),
          child: InkWell(
            borderRadius: BorderRadius.circular(16),
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => AnimalPreview(animalModel: animal),
                ),
              );
            },
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Row(
                children: [
                  // Enhanced avatar with vaccination status indicator
                  Stack(
                    children: [
                      Container(
                        width: 60,
                        height: 60,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: Config.primaryColor.withOpacity(0.1),
                          border: Border.all(
                            color: Config.primaryColor.withOpacity(0.2),
                            width: 2,
                          ),
                        ),
                        child: ClipOval(
                          child: animal.imageUrl != null
                              ? Image.network(
                                  animal.imageUrl!,
                                  fit: BoxFit.cover,
                                  errorBuilder: (context, error, stackTrace) =>
                                      const Icon(Icons.pets, color: Colors.orange, size: 30),
                                )
                              : const Icon(Icons.pets, color: Colors.orange, size: 30),
                        ),
                      ),
                      // Vaccination status badge
                      Positioned(
                        right: 0,
                        bottom: 0,
                        child: Container(
                          width: 20,
                          height: 20,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: isVaccinated ? Colors.green : Colors.orange,
                            border: Border.all(color: Colors.white, width: 2),
                          ),
                          child: Icon(
                            isVaccinated ? Icons.check : Icons.warning,
                            size: 12,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(width: 16),
                  // Animal info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          animal.name,
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: 18,
                            fontWeight: FontWeight.w600,
                            color: Colors.grey[800],
                          ),
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: Config.primaryColor.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Text(
                                animal.type,
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w500,
                                  color: Config.primaryColor,
                                ),
                              ),
                            ),
                            if (animal.breed != null) ...[
                              const SizedBox(width: 8),
                              Text(
                                '• ${animal.breed}',
                                style: TextStyle(
                                  fontSize: 14,
                                  color: Colors.grey[600],
                                ),
                              ),
                            ],
                          ],
                        ),
                        const SizedBox(height: 4),
                        // Vaccination status info
                        Row(
                          children: [
                            Icon(
                              isVaccinated ? Icons.verified : Icons.warning_amber_rounded,
                              size: 14,
                              color: isVaccinated ? Colors.green : Colors.orange,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              isVaccinated 
                                  ? '$vaccinationCount vaccine${vaccinationCount != 1 ? 's' : ''}'
                                  : 'Not vaccinated',
                              style: TextStyle(
                                fontSize: 12,
                                color: isVaccinated ? Colors.green : Colors.orange,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  // Arrow icon
                  Icon(
                    Icons.arrow_forward_ios,
                    size: 16,
                    color: Colors.grey[400],
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildError(String message) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.error_outline, size: 60, color: Colors.red[300]),
          const SizedBox(height: 16),
          Text(
            'Oops! Something went wrong',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Colors.grey[800],
            ),
          ),
          const SizedBox(height: 8),
          Text(
            message,
            style: TextStyle(color: Colors.grey[600]),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 20),
          ElevatedButton.icon(
            onPressed: () {
              context.read<AnimalViewModel>().fetchAllAnimals();
            },
            icon: const Icon(Icons.refresh),
            label: const Text("Try Again"),
            style: ElevatedButton.styleFrom(
              backgroundColor: Config.primaryColor,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
            ),
          ),
        ],
      ),
    );
  }
}