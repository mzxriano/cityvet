import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class NutritionGuidePage extends StatelessWidget {
  final String animalType;

  const NutritionGuidePage({super.key, required this.animalType});

  // --- Nutrition Data ---
  static const Map<String, List<Map<String, String>>> nutritionData = {
    'dog': [
      {
        'title': 'Essential Nutrients',
        'content': 'Dogs require a balanced diet of protein (for muscle), fats (for energy and coat health), carbohydrates (for daily fuel), vitamins, and minerals. High-quality commercial food is generally complete.',
        'guideline': 'Feed 2-3 times daily for puppies; 1-2 times daily for adults. Ensure constant access to fresh water. Portions depend on breed size, age, and activity level.',
      },
      {
        'title': 'Common Mistakes',
        'content': 'Overfeeding (leading to obesity), feeding excessive table scraps (can cause pancreatitis or nutrient imbalance), and feeding toxic foods (chocolate, grapes, onions, xylitol).',
        'guideline': 'Always choose age-appropriate food (puppy, adult, senior). Consult a vet before switching to raw or home-cooked diets.',
      },
    ],
    'cat': [
      {
        'title': 'Obligate Carnivores',
        'content': 'Cats are obligate carnivores, meaning they must eat meat. Their diet must be high in protein, moderate in fat, and low in carbs. Essential nutrients include Taurine (a lack causes heart and vision problems) and Arachidonic Acid.',
        'guideline': 'Offer wet food for hydration and dry food for convenience. Many cats prefer "free feeding" small portions throughout the day. Avoid dog food, as it lacks essential feline nutrients like Taurine.',
      },
      {
        'title': 'Hydration',
        'content': 'Cats often have a low thirst drive. Wet food is critical for maintaining kidney health and hydration. Consider using a water fountain to encourage drinking.',
        'guideline': 'Ensure fresh water is available far away from their food bowl, as they instinctively avoid contaminating their water source.',
      },
    ],
    'goat': [
      {
        'title': 'Ruminant Diet (Browsers)',
        'content': 'Goats are browsers, preferring hay, leaves, shrubs, and weeds over short grass. Their diet should primarily be hay (grass or legume) and pasture (browse). Feed quality roughage to maintain rumen health.',
        'guideline': 'Offer free-choice hay. Supplement with a mineral block specifically formulated for goats (ensure it does NOT contain copper if also feeding sheep, as copper is toxic to sheep). Limit commercial feed/grains to pregnant, lactating, or growing animals.',
      },
      {
        'title': 'Essential Supplements',
        'content': 'Goats often require supplementation of copper, selenium, and zinc, depending on local soil conditions. Consult an agricultural extension for local mineral deficiencies.',
        'guideline': 'Fresh, clean water is essential, especially for lactating does.',
      },
    ],
    'cattle': [
      {
        'title': 'Grazing and Forage',
        'content': 'Cattle are grazers and require a high-forage diet (grass, hay, silage). The rumen requires long fiber to function correctly. Diet varies significantly based on purpose (beef vs. dairy).',
        'guideline': 'Feed quality hay or pasture as the foundation. Dairy cows require high-energy, protein-rich concentrate feed to support milk production. Beef cattle often need supplementary feed for weight gain.',
      },
      {
        'title': 'Water and Salt',
        'content': 'Cattle drink large amounts of water (up to 30 gallons/day). Water quality is vital for health and production.',
        'guideline': 'Provide free access to salt and mineral mixes. Ensure adequate shade and ventilation to prevent heat stress, which affects appetite.',
      },
    ],
    'chicken': [
      {
        'title': 'Complete Layer/Grower Feed',
        'content': 'Chickens need a specific balance of protein, energy, vitamins, and calcium. Diet changes based on age: Starter (high protein), Grower, and Layer (high calcium).',
        'guideline': 'Layer hens MUST be fed Layer Feed (around 16% protein and 3-4% calcium). Free-choice grit is required to help them grind food in the gizzard. Provide oyster shells as an additional calcium source for strong eggshells.',
      },
      {
        'title': 'Treats and Scraps',
        'content': 'Limit treats and kitchen scraps to no more than 10% of the total diet. Excessive scraps reduce the nutritional completeness of their main feed.',
        'guideline': 'Avoid feeding moldy food, avocado pits/skins, raw potato skins, and excessive salt. Fresh water should be available at all times.',
      },
    ],
  };
  // -------------------------

  @override
  Widget build(BuildContext context) {
    final key = animalType.toLowerCase();
    final nutritionGuide = nutritionData[key];

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          onPressed: () {
            Navigator.pop(context);
          },
          icon: Config.backButtonIcon,
        ),
        title: Text('$animalType Nutrition Guide'),
      ),
      body: SafeArea(
        child: nutritionGuide == null || nutritionGuide.isEmpty
            ? Center(
                child: Text(
                  'No specific nutrition guide information available for $animalType yet.',
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
                itemCount: nutritionGuide.length,
                itemBuilder: (context, index) {
                  final section = nutritionGuide[index];
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
                          // Section Title
                          Text(
                            section['title']!,
                            style: TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: Config.fontMedium,
                              fontWeight: FontWeight.bold,
                              color: Config.primaryColor,
                            ),
                          ),
                          const Divider(height: 16, thickness: 1),
                          
                          // Main Content
                          _buildDetailText(
                            'Summary:',
                            section['content']!,
                            Config.tertiaryColor,
                          ),
                          const SizedBox(height: 10),
                          
                          // Guideline/Actionable Advice
                          _buildDetailText(
                            'Key Guidance:',
                            section['guideline']!,
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

  Widget _buildDetailText(String title, String content, Color color) {
    return RichText(
      textAlign: TextAlign.justify,
      text: TextSpan(
        style: TextStyle(
          fontFamily: Config.primaryFont,
          fontSize: Config.fontSmall,
          color: color,
          height: 1.4,
        ),
        children: [
          TextSpan(
            text: '$title ',
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
          TextSpan(text: content),
        ],
      ),
    );
  }
}
