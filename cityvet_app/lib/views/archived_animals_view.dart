import 'package:cityvet_app/models/animal_archive_model.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/animal_view_model.dart';
import 'package:cityvet_app/views/memorial_animal_view.dart';
import 'package:cityvet_app/views/deleted_animal_view.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class ArchivedAnimalsView extends StatefulWidget {
  const ArchivedAnimalsView({super.key});

  @override
  State<ArchivedAnimalsView> createState() => _ArchivedAnimalsViewState();
}

class _ArchivedAnimalsViewState extends State<ArchivedAnimalsView> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  String _selectedType = 'All';
  String _selectedArchiveType = 'All'; // All, Deceased, Deleted
  final List<String> _animalTypes = ['All', 'Dog', 'Cat', 'Bird', 'Others'];
  final List<String> _archiveTypes = ['All', 'Deceased', 'Deleted'];

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      final animalViewModel = context.read<AnimalViewModel>();
      animalViewModel.fetchArchivedAnimals();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFEEEEEE),
      appBar: AppBar(
        title: Text(
          'Archived Animals',
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontSize: Config.fontMedium,
            fontWeight: FontWeight.w600,
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 0.5,
        leading: IconButton(
          onPressed: () => Navigator.pop(context),
          icon: Config.backButtonIcon,
        ),
      ),
      body: Consumer<AnimalViewModel>(
        builder: (context, animalViewModel, _) {
          if (animalViewModel.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          return _buildBody(animalViewModel);
        },
      ),
    );
  }

  Widget _buildBody(AnimalViewModel animalViewModel) {
    // Filter archived animals
    final filteredAnimals = animalViewModel.archivedAnimals.where((archive) {
      final animal = archive.animal;
      
      final matchesSearch = animal.name.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          (animal.breed?.toLowerCase().contains(_searchQuery.toLowerCase()) ?? false) ||
          animal.type.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          animal.color.toLowerCase().contains(_searchQuery.toLowerCase());

      final matchesType = _selectedType == 'All' || 
          animal.type.toLowerCase() == _selectedType.toLowerCase();

      final matchesArchiveType = _selectedArchiveType == 'All' ||
          (_selectedArchiveType == 'Deceased' && archive.isDeceased) ||
          (_selectedArchiveType == 'Deleted' && archive.isDeleted);

      return matchesSearch && matchesType && matchesArchiveType;
    }).toList();

    return Column(
      children: [
        // Search and filter section
        Container(
          padding: const EdgeInsets.all(16),
          color: Colors.white,
          child: Column(
            children: [
              // Search bar and type filter
              Row(
                children: [
                  Expanded(
                    flex: 3,
                    child: Container(
                      height: 45,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.grey.shade300),
                      ),
                      child: TextField(
                        controller: _searchController,
                        onChanged: (value) {
                          setState(() {
                            _searchQuery = value;
                          });
                        },
                        decoration: InputDecoration(
                          hintText: 'Search archived animals...',
                          hintStyle: TextStyle(
                            color: Colors.grey.shade500,
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontSmall,
                          ),
                          border: InputBorder.none,
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
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
              const SizedBox(height: 12),
              // Archive type filter
              Row(
                children: [
                  Text(
                    'Archive Type:',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontSmall,
                      fontWeight: FontWeight.w500,
                      color: Colors.grey.shade700,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
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
                            value: _selectedArchiveType,
                            isExpanded: true,
                            icon: Icon(Icons.arrow_drop_down, color: Colors.grey.shade500),
                            items: _archiveTypes.map((String type) {
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
                                _selectedArchiveType = newValue ?? 'All';
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
        
        // Results section
        Expanded(
          child: filteredAnimals.isEmpty
              ? _buildEmptyState()
              : _buildArchivesList(filteredAnimals),
        ),
      ],
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.archive_outlined,
            size: 64,
            color: Colors.grey[400],
          ),
          const SizedBox(height: 16),
          Text(
            'No archived animals found',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontMedium,
              color: Colors.grey[600],
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Deleted and deceased animals will appear here',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontSmall,
              color: Colors.grey[500],
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildArchivesList(List<AnimalArchiveModel> archives) {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: archives.length,
      itemBuilder: (context, index) {
        final archive = archives[index];
        final animal = archive.animal;
        
        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          child: Card(
            elevation: 2,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            child: InkWell(
              borderRadius: BorderRadius.circular(12),
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => archive.isDeceased
                        ? MemorialAnimalView(archive: archive)
                        : DeletedAnimalView(archive: archive),
                  ),
                );
              },
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        // Animal avatar
                        ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: Container(
                            width: 60,
                            height: 60,
                            decoration: BoxDecoration(
                              color: Colors.grey[200],
                            ),
                            child: animal.imageUrl != null && animal.imageUrl!.isNotEmpty
                                ? Image.network(
                                    animal.imageUrl!,
                                    fit: BoxFit.cover,
                                    errorBuilder: (context, error, stackTrace) => Icon(
                                      Icons.pets,
                                      color: Colors.grey[500],
                                      size: 30,
                                    ),
                                  )
                                : Icon(
                                    Icons.pets,
                                    color: Colors.grey[500],
                                    size: 30,
                                  ),
                          ),
                        ),
                        const SizedBox(width: 16),
                        
                        // Animal info
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      animal.name,
                                      style: TextStyle(
                                        fontFamily: Config.primaryFont,
                                        fontSize: Config.fontMedium,
                                        fontWeight: FontWeight.w600,
                                        color: Colors.grey[800],
                                      ),
                                    ),
                                  ),
                                  // Archive type badge
                                  Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: archive.isDeceased ? Colors.red[100] : Colors.orange[100],
                                          borderRadius: BorderRadius.circular(12),
                                          border: Border.all(
                                            color: archive.isDeceased ? Colors.red[300]! : Colors.orange[300]!,
                                          ),
                                        ),
                                        child: Text(
                                          archive.isDeceased ? 'DECEASED' : 'DELETED',
                                          style: TextStyle(
                                            color: archive.isDeceased ? Colors.red[700] : Colors.orange[700],
                                            fontSize: 10,
                                            fontWeight: FontWeight.bold,
                                            fontFamily: Config.primaryFont,
                                          ),
                                        ),
                                      ),
                                      if (archive.isDeleted) ...[
                                        const SizedBox(width: 8),
                                        InkWell(
                                          onTap: () => _showRestoreDialog(context, archive),
                                          borderRadius: BorderRadius.circular(16),
                                          child: Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                            decoration: BoxDecoration(
                                              color: Colors.green[100],
                                              borderRadius: BorderRadius.circular(12),
                                              border: Border.all(
                                                color: Colors.green[300]!,
                                              ),
                                            ),
                                            child: Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                Icon(
                                                  Icons.restore,
                                                  size: 12,
                                                  color: Colors.green[700],
                                                ),
                                                const SizedBox(width: 4),
                                                Text(
                                                  'RESTORE',
                                                  style: TextStyle(
                                                    color: Colors.green[700],
                                                    fontSize: 10,
                                                    fontWeight: FontWeight.bold,
                                                    fontFamily: Config.primaryFont,
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ),
                                      ],
                                    ],
                                  ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${animal.breed} • ${animal.type}',
                                style: TextStyle(
                                  fontFamily: Config.primaryFont,
                                  fontSize: Config.fontSmall,
                                  color: Colors.grey[600],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    
                    const SizedBox(height: 12),
                    
                    // Archive details
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.grey[50],
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.grey[200]!),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Icon(Icons.calendar_today, size: 16, color: Colors.grey[600]),
                              const SizedBox(width: 8),
                              Text(
                                'Archive Date: ${archive.formattedArchiveDate}',
                                style: TextStyle(
                                  fontFamily: Config.primaryFont,
                                  fontSize: Config.fontXS,
                                  color: Colors.grey[600],
                                ),
                              ),
                            ],
                          ),
                          if (archive.reason != null) ...[
                            const SizedBox(height: 4),
                            Row(
                              children: [
                                Icon(Icons.info_outline, size: 16, color: Colors.grey[600]),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    'Reason: ${archive.reason}',
                                    style: TextStyle(
                                      fontFamily: Config.primaryFont,
                                      fontSize: Config.fontXS,
                                      color: Colors.grey[600],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ],
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              Icon(Icons.person, size: 16, color: Colors.grey[600]),
                              const SizedBox(width: 8),
                              Text(
                                'Archived by: ${archive.archivedBy}',
                                style: TextStyle(
                                  fontFamily: Config.primaryFont,
                                  fontSize: Config.fontXS,
                                  color: Colors.grey[600],
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }

  void _showRestoreDialog(BuildContext context, AnimalArchiveModel archive) async {
    final bool? confirmed = await showDialog<bool>(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Restore Animal'),
          content: Text(
            'Are you sure you want to restore ${archive.animal.name} back to your active animals list?',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontSmall,
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              child: Text(
                'Cancel',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  color: Colors.grey[600],
                ),
              ),
            ),
            ElevatedButton(
              onPressed: () => Navigator.of(context).pop(true),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.green,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              child: Text(
                'Restore',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        );
      },
    );

    if (confirmed == true) {
      await _restoreAnimal(archive);
    }
  }

  Future<void> _restoreAnimal(AnimalArchiveModel archive) async {
    try {
      final animalViewModel = context.read<AnimalViewModel>();
      
      // Call the restore method from the view model
      await animalViewModel.restoreArchivedAnimal(archive);
      
      // Show success message
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              '${archive.animal.name} has been restored successfully!',
              style: const TextStyle(
                fontFamily: Config.primaryFont,
              ),
            ),
            backgroundColor: Colors.green,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(8),
            ),
          ),
        );
      }
      
      // Refresh the archived animals list
      animalViewModel.fetchArchivedAnimals();
      
    } catch (e) {
      // Show error message
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Failed to restore animal: $e',
              style: const TextStyle(
                fontFamily: Config.primaryFont,
              ),
            ),
            backgroundColor: Colors.red,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(8),
            ),
          ),
        );
      }
    }
  }
}
