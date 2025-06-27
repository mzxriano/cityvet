import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/views/main_screens/animal/animal_preview.dart';
import 'package:flutter/material.dart';

class AnimalCard extends StatelessWidget {
  final AnimalModel animalModel;

  const AnimalCard({super.key, required this.animalModel});

  @override
  Widget build(BuildContext context) {
    return Card(
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
                child: Container(
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
    );
  }
}