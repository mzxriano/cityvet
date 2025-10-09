import 'package:cityvet_app/models/animal_archive_model.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class MemorialAnimalView extends StatelessWidget {
  final AnimalArchiveModel archive;

  const MemorialAnimalView({
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
          'In Memory of ${animal.name}',
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.grey[800],
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Memorial Header
            Container(
              width: double.infinity,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [Colors.grey[800]!, Colors.grey[600]!],
                ),
              ),
              child: Column(
                children: [
                  const SizedBox(height: 20),
                  // Memorial Symbol
                  Container(
                    width: 80,
                    height: 80,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(40),
                      border: Border.all(color: Colors.white.withOpacity(0.3), width: 2),
                    ),
                    child: Icon(
                      Icons.favorite,
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
                  const SizedBox(height: 20),
                ],
              ),
            ),
            
            const SizedBox(height: 24),
            
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
                    child: Container(
                      height: 250,
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: Colors.grey[200],
                        border: Border.all(color: Colors.grey[300]!, width: 2),
                      ),
                      child: animal.imageUrl != null && animal.imageUrl!.isNotEmpty
                          ? Image.network(
                              animal.imageUrl!,
                              fit: BoxFit.contain,
                              errorBuilder: (context, error, stackTrace) => _buildPlaceholderImage(),
                            )
                          : _buildPlaceholderImage(),
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Icon(Icons.calendar_today, size: 16, color: Colors.grey[600]),
                            const SizedBox(width: 8),
                            Text(
                              'Passed Away: ${archive.formattedArchiveDate}',
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: 14,
                                color: Colors.grey[700],
                                fontStyle: FontStyle.italic,
                              ),
                            ),
                          ],
                        ),
                        if (archive.reason != null && archive.reason!.isNotEmpty) ...[
                          const SizedBox(height: 12),
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.orange[50],
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: Colors.orange[200]!),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Cause of Death',
                                  style: TextStyle(
                                    fontFamily: Config.primaryFont,
                                    fontSize: 14,
                                    fontWeight: FontWeight.w600,
                                    color: Colors.orange[800],
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  archive.reason!,
                                  style: TextStyle(
                                    fontFamily: Config.primaryFont,
                                    fontSize: 14,
                                    color: Colors.orange[700],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
            ),
            
            const SizedBox(height: 20),
            
            // Life Details
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
                      'Life Details',
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.grey[800],
                      ),
                    ),
                    const SizedBox(height: 16),
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
            
            // Memorial Notes
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
                            'Memorial Notes',
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
            
            // Memorial Message
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 20),
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [Colors.blue[50]!, Colors.purple[50]!],
                ),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.blue[200]!, width: 1),
              ),
              child: Column(
                children: [
                  Icon(
                    Icons.pets,
                    size: 40,
                    color: Colors.blue[400],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    '"${animal.name} will always be remembered as a beloved companion who brought joy and love to everyone they met."',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: 16,
                      fontStyle: FontStyle.italic,
                      color: Colors.grey[700],
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Forever in our hearts',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Colors.grey[600],
                    ),
                  ),
                ],
              ),
            ),
            
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
