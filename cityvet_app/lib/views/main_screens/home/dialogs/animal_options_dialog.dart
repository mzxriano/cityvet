import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/views/main_screens/home/disease_info_page.dart';
import 'package:flutter/material.dart';

class AnimalOptionsDialog {
  static void show(BuildContext context, String animalType) {
    final List<Map<String, dynamic>> animalOptions = [
      {
        'title': 'Diseases',
        'subtitle': 'Disease guidelines',
        'icon': Icons.warning,
        'color': Colors.red,
      },
    ];

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (BuildContext context) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.only(
              topLeft: Radius.circular(20),
              topRight: Radius.circular(20),
            ),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Handle bar
              Container(
                margin: const EdgeInsets.symmetric(vertical: 12),
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey[300],
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              
              // Header
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFF8ED968).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(
                        Icons.pets,
                        color: Color(0xFF8ED968),
                        size: 24,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            animalType,
                            style: const TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: Config.fontMedium,
                              fontWeight: Config.fontW600,
                              color: Color(0xFF524F4F),
                            ),
                          ),
                          Text(
                            'Select an option',
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
              ),
              
              const Divider(height: 1),
              
              // Options list
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: animalOptions.length,
                itemBuilder: (context, index) {
                  final option = animalOptions[index];
                  return ListTile(
                    contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                    leading: Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: option['color'].withOpacity(0.1),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(
                        option['icon'],
                        color: option['color'],
                        size: 20,
                      ),
                    ),
                    title: Text(
                      option['title'],
                      style: const TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontSmall,
                        fontWeight: Config.fontW600,
                      ),
                    ),
                    subtitle: Text(
                      option['subtitle'],
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                    ),
                    trailing: const Icon(
                      Icons.arrow_forward_ios,
                      size: 16,
                      color: Colors.grey,
                    ),
                    onTap: () {
                      Navigator.pop(context);
                      _handleAnimalOptionTap(context, animalType, option['title']);
                    },
                  );
                },
              ),
              
              // Bottom padding
              const SizedBox(height: 20),
            ],
          ),
        );
      },
    );
  }

  static void _handleAnimalOptionTap(BuildContext context, String animalType, String option) {
    switch (option) {
      case 'Diseases':
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => DiseaseInfoPage(animalType: animalType),
          ),
        );
        break;
      default:
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('$option for $animalType - Feature coming soon!'),
            backgroundColor: const Color(0xFF8ED968),
          ),
        );
    }
  }
}
