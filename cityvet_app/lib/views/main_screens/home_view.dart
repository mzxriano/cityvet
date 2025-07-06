import 'package:cityvet_app/components/aew_card.dart';
import 'package:cityvet_app/components/card.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/home_view_model.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

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

    return ChangeNotifierProvider<HomeViewModel>(
      create: (_) => HomeViewModel()..fetchActivity(),
      child: Consumer<HomeViewModel>(
        builder: (context, ref, _) {
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
                  if (ref.activity != null)
                    GestureDetector(
                      onTap: () {
                        showDialog(
                          context: context,
                          builder: (BuildContext context) {
                            return AlertDialog(
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16),
                              ),
                              title: Text(
                                ref.activity!.reason,
                                style: const TextStyle(fontWeight: FontWeight.bold),
                              ),
                              content: _activityDetailsPopup(ref),
                              actions: [
                                TextButton(
                                  onPressed: () => Navigator.pop(context),
                                  child: const Text('Close'),
                                ),
                              ],
                            );
                          },
                        );
                      },
                      child: CustomCard(
                        width: double.infinity, 
                        color: Colors.white, 
                        widget: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              ref.activity?.reason ?? 'No reason',
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
                              child: Text(
                                ref.activity?.details ?? 'No details',
                                style: TextStyle(
                                  fontFamily: Config.primaryFont,
                                  fontSize: Config.fontSmall,
                                  color: Config.tertiaryColor,
                                ),
                                maxLines: 3,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),


                  if(ref.activity == null)
                  Center(
                    child: Container(
                      padding: EdgeInsets.all(20.0),
                      child: Text(
                        'No upcoming events.',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
                          color: Config.secondaryColor,
                        ),
                      ),
                    ),
                  ),
              
                  Config.heightBig,
                  const Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Administrative Extension Worker',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
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
                        AewCard(),
                        Config.heightSmall,
                        AewCard(),
                      ],
                    ),
                  )
                ],
              ),
            ),
          );
        }
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

Widget _activityDetailsPopup(HomeViewModel ref) {
  final activity = ref.activity!;
  final formattedDate = DateFormat('MMMM d, yyyy').format(activity.date);
  final formattedTime = DateFormat('h:mm a').format(activity.time);

  return Column(
    mainAxisSize: MainAxisSize.min,
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      Text(activity.details),
      const SizedBox(height: 12),
      Row(
        children: [
          const Icon(Icons.calendar_today, size: 18, color: Colors.grey),
          const SizedBox(width: 8),
          Text(formattedDate),
        ],
      ),
      const SizedBox(height: 8),
      Row(
        children: [
          const Icon(Icons.access_time, size: 18, color: Colors.grey),
          const SizedBox(width: 8),
          Text(formattedTime),
        ],
      ),
      const SizedBox(height: 8),
      Row(
        children: [
          const Icon(Icons.location_on, size: 18, color: Colors.grey),
          const SizedBox(width: 8),
          Text(ref.activity?.barangay ?? 'Unknown'),
        ],
      ),
    ],
  );
}



