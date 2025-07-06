import 'package:cityvet_app/components/card.dart';
import 'package:cityvet_app/components/card_veterinarian.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class HomeView extends StatefulWidget {
  const HomeView({super.key});

  @override
  State<HomeView> createState() => HomeViewState();
}

class HomeViewState extends State<HomeView> {
  @override
  Widget build(BuildContext context) {
    Config().init(context);
    
    final List<Map<String, String>> animals = [
      {'image': 'assets/images/logo.png', 'label': 'Dog'},
      {'image': 'assets/images/logo.png', 'label': 'Cat'},
      {'image': 'assets/images/logo.png', 'label': 'Goat'},
      {'image': 'assets/images/logo.png', 'label': 'Cattle'},
      {'image': 'assets/images/logo.png', 'label': 'Chicken'},
    ];

    return SafeArea(
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(child: _searchBar()),
            Config.heightBig,
            const Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Animals',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                    fontWeight: Config.fontW600,
                  ),
                ),
                Icon(Icons.arrow_forward),
              ],
            ),
            Config.heightSmall,
        
            SizedBox(
              height: 160,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: animals.length,
                padding: const EdgeInsets.symmetric(horizontal: 8),
                itemBuilder: (context, index) {
                  return SizedBox(
                    width: 150,
                    child: _animalCard(
                      animals[index]['image']!,
                      animals[index]['label']!,
                    ),
                  );
                },
                separatorBuilder: (context, index) => const SizedBox(width: 10),
              ),
            ),
        
            Config.heightBig,
            const Text(
              'Up Coming',
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontMedium,
                fontWeight: Config.fontW600,
              ),
            ),
            Config.heightSmall,
            CustomCard(
              width: double.infinity, 
              color: Colors.white, 
              widget: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Vaccination',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontMedium,
                      fontWeight: Config.fontW600,
                      color: Color(0xFF524F4F),
                    ),
                  ),
                  Config.heightSmall,
                  SizedBox(
                    width: double.infinity,
                    child: Expanded(
                      child: Text(
                          'In the coming days of May 8th, there will be available vaccines for Dogs, Cats and Goats. This will be held at Brgy Catablan.',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
                          color: Config.tertiaryColor,
                        ),
                        maxLines: 3,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ),
                ],
              )
            ),
        
            Config.heightBig,
            const Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Available Vet',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                    fontWeight: Config.fontW600,
                  ),
                ),
                TextButton(
                  onPressed: null, 
                  child: Text(
                    'See all',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontSmall,
                      color: Config.tertiaryColor,
                    ),
                  )
                ),
              ],
            ),
            Config.heightSmall,
            SizedBox(
              child: Column(
                children: [
                  CardVeterinarian(
                    vetName: 'Dr. Sarah Cruz', 
                    vetEmail: 'cruz@gmail.com', 
                    vetPhone: '+639152623657', 
                    vetImageUrl: 'assets/images/default_avatar.png'
                  ),
                  Config.heightSmall,
                  CardVeterinarian(
                    vetName: 'Dr. Sarah Cruz', 
                    vetEmail: 'cruz@gmail.com', 
                    vetPhone: '+639152623657', 
                    vetImageUrl: 'assets/images/default_avatar.png'
                  ),
                ],
              ),
            )
          ],
        ),
      ),
    );
  } 

  // Search field widget
  Widget _searchBar() {
    return Container(
      width: double.infinity,
      constraints: const BoxConstraints(maxWidth: 500),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(8),
      ),
      child: TextField(
        decoration: InputDecoration(
          hintText: 'Search',
          hintStyle: TextStyle(
            fontFamily: Config.primaryFont,
            fontSize: Config.fontMedium,
          ),
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(vertical: 8, horizontal: 15),
        ),
        style: TextStyle(
          fontFamily: Config.primaryFont,
          fontSize: Config.fontSmall,
        ),
      ),
    );
  }

  // Animal card widget
  Widget _animalCard(String imageUrl, String label) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: const Color(0xFF8ED968),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Expanded(
            child: Image.asset(
              imageUrl,
              fit: BoxFit.contain,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            label,
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontMedium,
            ),
          ),
        ],
      ),
    );
  }
}
