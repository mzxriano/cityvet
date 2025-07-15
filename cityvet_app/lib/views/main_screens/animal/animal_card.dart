import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/views/main_screens/animal/animal_preview.dart';
import 'package:flutter/material.dart';

class AnimalCard extends StatelessWidget {
  final AnimalModel animalModel;
  final VoidCallback? onDelete;

  const AnimalCard({super.key, required this.animalModel, required this.onDelete});

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        Card(
          elevation: 4,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(15),
          ),
          child: InkWell(
            onTap: () {
              Navigator.push(context, MaterialPageRoute(builder: (_) => AnimalPreview(animalModel: animalModel,)));
            },
            borderRadius: BorderRadius.circular(15),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(15),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Top section - Image/Icon area
                  Expanded(
                    flex: 3,
                    child: animalModel.imageUrl != null ?
                       Image.network(
                         animalModel.imageUrl!,
                         fit: BoxFit.cover,
                         width: double.infinity,
                         height: double.infinity,
                         errorBuilder: (context, error, stackTrace) {
                           return Container(
                             color: Config.primaryColor,
                             child: const Center(
                               child: Icon(
                                 Icons.pets,
                                 size: 50,
                                 color: Colors.white,
                               ),
                             ),
                           );
                         },
                         loadingBuilder: (context, child, loadingProgress) {
                           if (loadingProgress == null) return child;
                           return Container(
                             color: Config.primaryColor.withOpacity(0.3),
                             child: const Center(
                               child: CircularProgressIndicator(),
                             ),
                           );
                         },
                       ) :
                      Container(
                      color: Config.primaryColor,
                      child: const Center(
                        child: Icon(
                          Icons.pets,
                          size: 50,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
                  // Bottom section - Animal info
                  Expanded(
                    flex: 2,
                    child: Container(
                      color: Colors.white,
                      padding: const EdgeInsets.all(12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            animalModel.name,
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Colors.black87,
                            ),
                          ),
                          SizedBox(height: 4),
                          Text(
                            animalModel.type,
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
                
        // Delete button positioned at top-right
        if (onDelete != null)
          Positioned(
            top: 8,
            right: 8,
            child: GestureDetector(
              onTap: onDelete,
              child: Container(
                padding: const EdgeInsets.all(4),
                decoration: BoxDecoration(
                  color: Colors.red.withOpacity(0.8),
                  borderRadius: BorderRadius.circular(15),
                ),
                child: const Icon(
                  Icons.delete,
                  size: 16,
                  color: Colors.white,
                ),
              ),
            ),
          ),
      ],
    );
  }
}