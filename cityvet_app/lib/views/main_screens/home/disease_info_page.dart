import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class DiseaseInfoPage extends StatelessWidget {
  final String animalType;

  const DiseaseInfoPage({super.key, required this.animalType});

  // --- Disease Data ---
  static const Map<String, List<Map<String, String>>> diseaseData = {
    'dog': [
      {
        'name': 'Canine Distemper',
        'symptoms': 'Fever, nasal/ocular discharge, coughing, vomiting, seizures.',
        'prevention': 'Vaccination (DAPP), minimizing contact with infected animals.',
      },
      {
        'name': 'Canine Parvovirus (Parvo)',
        'symptoms': 'Severe bloody diarrhea, vomiting, lethargy, fever, anorexia.',
        'prevention': 'Strict vaccination schedule (starting at 6-8 weeks old), good hygiene.',
      },
      {
        'name': 'Rabies',
        'symptoms': 'Behavioral changes, aggression, salivation, difficulty swallowing, paralysis.',
        'prevention': 'Mandatory vaccination (usually yearly or tri-annually, depending on vaccine type).',
      },
    ],
    'cat': [
      {
        'name': 'Feline Panleukopenia (FPL)',
        'symptoms': 'Fever, lethargy, severe vomiting and diarrhea, dehydration.',
        'prevention': 'Vaccination (FVRCP), avoiding contaminated environments.',
      },
      {
        'name': 'Feline Immunodeficiency Virus (FIV)',
        'symptoms': 'Recurrent fever, weight loss, chronic infections, gum disease.',
        'prevention': 'Keeping cats indoors, neutering, avoiding fights with infected cats.',
      },
    ],
    'goat': [
      {
        'name': 'Contagious Caprine Pleuropneumonia (CCPP)',
        'symptoms': 'Severe coughing, difficulty breathing, fever, nasal discharge.',
        'prevention': 'Vaccination in endemic areas, strict biosecurity, culling of infected stock.',
      },
      {
        'name': 'Goat Pox',
        'symptoms': 'Fever, generalized skin lesions (papules/nodules), especially on udder and inner thighs.',
        'prevention': 'Vaccination, isolation of sick animals, proper disinfection.',
      },
    ],
    'cattle': [
      {
        'name': 'Foot-and-Mouth Disease (FMD)',
        'symptoms': 'Fever, vesicles (blisters) in the mouth and on feet/teats, lameness.',
        'prevention': 'Strict quarantine/movement control, vaccination where available, hygiene.',
      },
      {
        'name': 'Hemorrhagic Septicemia (HS)',
        'symptoms': 'High fever, severe respiratory distress, swelling of the throat and neck.',
        'prevention': 'Vaccination (especially before monsoon/stress periods), prompt treatment.',
      },
    ],
    'chicken': [
      {
        'name': 'Avian Influenza (Bird Flu)',
        'symptoms': 'Severe depression, reduced egg production, swelling of head, cyanosis of wattles/combs.',
        'prevention': 'Strict biosecurity, limiting contact with wild birds, depopulation of affected flocks.',
      },
      {
        'name': 'Newcastle Disease (NCD)',
        'symptoms': 'Respiratory signs (coughing), nervous signs (paralysis, twisted neck), severe drops in egg production.',
        'prevention': 'Routine vaccination, strict biosecurity measures.',
      },
    ],
  };
  // -------------------------

  @override
  Widget build(BuildContext context) {
    // Convert animalType to lower-case to match the keys in diseaseData
    final key = animalType.toLowerCase();
    final diseases = diseaseData[key];

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          onPressed: () {
            Navigator.pop(context);
          },
          icon: Config.backButtonIcon,
        ),
        title: Text("$animalType Diseases"),
      ),
      body: SafeArea(
        child: diseases == null || diseases.isEmpty
            ? Center(
                child: Text(
                  'No specific disease information available for $animalType yet.',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                    color: Config.tertiaryColor,
                  ),
                ),
              )
            : ListView.builder(
                padding: const EdgeInsets.all(16.0),
                itemCount: diseases.length,
                itemBuilder: (context, index) {
                  final disease = diseases[index];
                  return Card(
                    margin: const EdgeInsets.only(bottom: 16.0),
                    elevation: 2,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10.0),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Disease Name
                          Text(
                            disease['name']!,
                            style: TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: Config.fontMedium,
                              fontWeight: FontWeight.bold,
                              color: Config.primaryColor,
                            ),
                          ),
                          const Divider(height: 16, thickness: 1),
                          
                          // Symptoms
                          _buildDiseaseDetail(
                            'Symptoms:',
                            disease['symptoms']!,
                            Config.tertiaryColor,
                          ),
                          const SizedBox(height: 10),
                          
                          // Prevention
                          _buildDiseaseDetail(
                            'Prevention:',
                            disease['prevention']!,
                            Config.tertiaryColor,
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
      ),
    );
  }

  Widget _buildDiseaseDetail(String title, String content, Color color) {
    return RichText(
      text: TextSpan(
        style: TextStyle(
          fontFamily: Config.primaryFont,
          fontSize: Config.fontSmall,
          color: color,
        ),
        children: [
          TextSpan(
            text: title,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
          TextSpan(text: ' $content'),
        ],
      ),
    );
  }
}