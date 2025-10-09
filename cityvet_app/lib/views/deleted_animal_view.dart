import 'package:cityvet_app/models/animal_archive_model.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class DeletedAnimalView extends StatelessWidget {
  final AnimalArchiveModel archive;

  const DeletedAnimalView({
    super.key,
    required this.archive,
  });

  @override
  Widget build(BuildContext context) {
    final animal = archive.animal;
    
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Deleted Animal Record',
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.orange[700],
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Header
            Container(
              width: double.infinity,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [Colors.orange[700]!, Colors.orange[500]!],
                ),
              ),
              child: Column(
                children: [
                  const SizedBox(height: 20),
                  // Archive Symbol
                  Container(
                    width: 80,
                    height: 80,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(40),
                      border: Border.all(color: Colors.white.withOpacity(0.3), width: 2),
                    ),
                    child: Icon(
                      Icons.archive_outlined,
                      size: 40,
                      color: Colors.white.withOpacity(0.8),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    animal.name,
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: 28,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '${animal.type} • ${animal.breed ?? 'Mixed Breed'}',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: 16,
                      color: Colors.white.withOpacity(0.9),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      'ARCHIVED RECORD',
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                        letterSpacing: 1,
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                ],
              ),
            ),
            
            const SizedBox(height: 24),
            
            // Archive Info Banner
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 20),
              decoration: BoxDecoration(
                color: Colors.orange[50],
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.orange[300]!),
              ),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Icon(
                      Icons.info_outline,
                      color: Colors.orange[700],
                      size: 24,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'This animal record has been archived',
                            style: TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                              color: Colors.orange[800],
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Deleted on ${archive.formattedArchiveDate}',
                            style: TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: 12,
                              color: Colors.orange[700],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            
            const SizedBox(height: 20),
            
            // Animal Photo
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Column(
                children: [
                  ClipRRect(
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                    child: Stack(
                      children: [
                        Container(
                          height: 250,
                          width: double.infinity,
                          decoration: BoxDecoration(
                            color: Colors.grey[200],
                            border: Border.all(color: Colors.grey[300]!, width: 2),
                          ),
                          child: animal.imageUrl != null && animal.imageUrl!.isNotEmpty
                              ? ColorFiltered(
                                  colorFilter: ColorFilter.mode(
                                    Colors.grey.withOpacity(0.3),
                                    BlendMode.overlay,
                                  ),
                                  child: Image.network(
                                    animal.imageUrl!,
                                    fit: BoxFit.cover,
                                    errorBuilder: (context, error, stackTrace) => _buildPlaceholderImage(),
                                  ),
                                )
                              : _buildPlaceholderImage(),
                        ),
                        // Archive overlay
                        Positioned(
                          top: 12,
                          right: 12,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.orange[600],
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              'ARCHIVED',
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            
            const SizedBox(height: 20),
            
            // Animal Details
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Animal Details',
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.grey[800],
                      ),
                    ),
                    const SizedBox(height: 16),
                    _buildDetailRow('Animal ID', '#${animal.id}'),
                    _buildDetailRow('Name', animal.name),
                    _buildDetailRow('Type', animal.type),
                    _buildDetailRow('Breed', animal.breed ?? 'Mixed Breed'),
                    _buildDetailRow('Gender', animal.gender.toUpperCase()),
                    _buildDetailRow('Color', animal.color),
                    if (animal.birthDate != null) _buildDetailRow('Birth Date', animal.birthDate!),
                    if (animal.weight != null) _buildDetailRow('Weight', '${animal.weight} kg'),
                    if (animal.height != null) _buildDetailRow('Height', '${animal.height} cm'),
                    if (animal.uniqueSpot != null && animal.uniqueSpot!.isNotEmpty) 
                      _buildDetailRow('Unique Markings', animal.uniqueSpot!),
                    if (animal.knownConditions != null && animal.knownConditions!.isNotEmpty) 
                      _buildDetailRow('Known Conditions', animal.knownConditions!),
                    _buildDetailRow('Owner', animal.owner ?? 'Unknown'),
                  ],
                ),
              ),
            ),
            
            const SizedBox(height: 20),
            
            // Archive Details
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.archive, color: Colors.orange[600], size: 20),
                        const SizedBox(width: 8),
                        Text(
                          'Archive Information',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: Colors.grey[800],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    _buildDetailRow('Archive Type', 'DELETED'),
                    _buildDetailRow('Archive Date', archive.formattedArchiveDate),
                    _buildDetailRow('Archived At', archive.formattedArchivedAt),
                    _buildDetailRow('Archived By', archive.archivedBy),
                    if (archive.reason != null && archive.reason!.isNotEmpty)
                      _buildDetailRow('Reason', archive.reason!),
                  ],
                ),
              ),
            ),
            
            const SizedBox(height: 20),
            
            // Archive Notes
            if (archive.notes != null && archive.notes!.isNotEmpty) ...[
              Container(
                margin: const EdgeInsets.symmetric(horizontal: 20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.1),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.note, color: Colors.blue[600], size: 20),
                          const SizedBox(width: 8),
                          Text(
                            'Archive Notes',
                            style: TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: Colors.grey[800],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text(
                        archive.notes!,
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: 14,
                          color: Colors.grey[700],
                          height: 1.4,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),
            ],
            
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }

  Widget _buildPlaceholderImage() {
    return Container(
      width: double.infinity,
      height: double.infinity,
      color: Colors.grey[200],
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.pets,
            size: 60,
            color: Colors.grey[400],
          ),
          const SizedBox(height: 8),
          Text(
            'No Photo Available',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              color: Colors.grey[500],
              fontSize: 12,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              label,
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: Colors.grey[600],
              ),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: 14,
                color: Colors.grey[800],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
