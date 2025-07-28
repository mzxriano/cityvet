import 'package:cityvet_app/components/aew_card.dart';
import 'package:cityvet_app/components/card.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/home_view_model.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:cityvet_app/views/main_screens/home/all_activities_page.dart';
import 'package:cityvet_app/views/main_screens/home/all_aew_page.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

class HomeView extends StatefulWidget {
  const HomeView({super.key});

  @override
  State<HomeView> createState() => HomeViewState();
}

class HomeViewState extends State<HomeView> {
  final TextEditingController _activitySearchController = TextEditingController();
  String _activitySearchQuery = '';

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

    return ChangeNotifierProvider(
      create: (_) => HomeViewModel(),
      builder: (context, child) {
        // Initialize data fetching after provider is created
        WidgetsBinding.instance.addPostFrameCallback((_) {
          final homeViewModel = Provider.of<HomeViewModel>(context, listen: false);
          homeViewModel.fetchActivity();
          homeViewModel.fetchRecentActivities();
        });

        return Consumer<HomeViewModel>(
          builder: (context, homeViewModel, _) {
            return SafeArea(
              child: RefreshIndicator(
                onRefresh: () => homeViewModel.refreshData(),
                child: SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [                      
                      // Animals Section
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
                      
                      // Upcoming Section
                      const Text(
                        'Up Coming',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontMedium,
                          fontWeight: Config.fontW600,
                        ),
                      ),
                      Config.heightSmall,
                      
                      // Show loading indicator for upcoming activity
                      if (homeViewModel.isLoading)
                        const Center(child: CircularProgressIndicator())
                      else if (homeViewModel.error != null)
                        Center(
                          child: Container(
                            padding: const EdgeInsets.all(20.0),
                            child: Column(
                              children: [
                                Text(
                                  'Error: ${homeViewModel.error}',
                                  style: const TextStyle(
                                    fontFamily: Config.primaryFont,
                                    fontSize: Config.fontSmall,
                                    color: Colors.red,
                                  ),
                                ),
                                const SizedBox(height: 10),
                                ElevatedButton(
                                  onPressed: () => homeViewModel.fetchActivity(),
                                  child: const Text('Retry'),
                                ),
                              ],
                            ),
                          ),
                        )
                      else if (homeViewModel.activity != null)
                        GestureDetector(
                          onTap: () => _showActivityDialog(context, homeViewModel),
                          child: CustomCard(
                            width: double.infinity,
                            color: Colors.white,
                            widget: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  homeViewModel.activity?.reason ?? 'No reason',
                                  style: const TextStyle(
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
                                    homeViewModel.activity?.details ?? 'No details',
                                    style: const TextStyle(
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
                        )
                      else
                        Center(
                          child: Container(
                            padding: const EdgeInsets.all(20.0),
                            child: const Text(
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
                      
                      // Role-based section
                      _buildRoleBasedSection(homeViewModel),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }

  void _showActivityDialog(BuildContext context, HomeViewModel homeViewModel) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          title: Text(
            homeViewModel.activity!.reason,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
          content: _activityDetailsPopup(homeViewModel),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Close'),
            ),
          ],
        );
      },
    );
  }

  // Role-based section builder
  Widget _buildRoleBasedSection(HomeViewModel homeViewModel) {
    final userViewModel = Provider.of<UserViewModel>(context, listen: false);
    String userRole = userViewModel.user?.role ?? '';
    
    // Check if user is veterinarian or staff to show recent activities
    if (userRole == 'veterinarian' || userRole == 'staff') {
      return _buildRecentActivitiesSection(homeViewModel);
    } else {
      return _buildAEWSection();
    }
  }

  // Recent Activities section for veterinarian/staff
  Widget _buildRecentActivitiesSection(HomeViewModel homeViewModel) {
    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Recent Activities',
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontMedium,
                fontWeight: FontWeight.w600,
              ),
            ),
            TextButton(
              onPressed: () => _navigateToAllActivities(context, homeViewModel),
              child: const Text(
                'See all',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: Config.fontSmall,
                  color: Config.tertiaryColor,
                ),
              ),
            ),
          ],
        ),
        Config.heightSmall,
        // Search bar for recent activities
        Container(
          width: double.infinity,
          constraints: const BoxConstraints(maxWidth: 500),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(8),
          ),
          child: TextField(
            controller: _activitySearchController,
            onChanged: (value) {
              setState(() {
                _activitySearchQuery = value;
              });
            },
            decoration: const InputDecoration(
              hintText: 'Search recent activities...',
              hintStyle: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontSmall,
                color: Colors.grey,
              ),
              border: InputBorder.none,
              contentPadding: EdgeInsets.symmetric(vertical: 8, horizontal: 15),
              prefixIcon: Icon(Icons.search, color: Colors.grey),
            ),
            style: const TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontSmall,
            ),
          ),
        ),
        Config.heightSmall,
        if (homeViewModel.isLoadingRecent)
          const Center(child: CircularProgressIndicator())
        else if (homeViewModel.recentActivities != null && homeViewModel.recentActivities!.isNotEmpty)
          Column(
            children: homeViewModel.recentActivities!
              .where((activity) =>
                _activitySearchQuery.isEmpty ||
                activity.reason.toLowerCase().contains(_activitySearchQuery.toLowerCase()) ||
                activity.details.toLowerCase().contains(_activitySearchQuery.toLowerCase()) ||
                activity.barangay.toLowerCase().contains(_activitySearchQuery.toLowerCase())
              )
              .take(3)
              .map((activity) {
                return Container(
                  margin: const EdgeInsets.only(bottom: 10),
                  child: CustomCard(
                    width: double.infinity,
                    color: Colors.white,
                    widget: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          activity.reason,
                          style: const TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontMedium,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF524F4F),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          activity.barangay.toString(),
                          style: const TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontSmall,
                            color: Config.tertiaryColor,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          DateFormat('MMM d, yyyy • h:mm a').format(activity.date),
                          style: const TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontSmall,
                            color: Config.tertiaryColor,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }).toList(),
          )
        else
          Center(
            child: Container(
              padding: const EdgeInsets.all(20.0),
              child: const Text(
                'No recent activities.',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: Config.fontSmall,
                  color: Config.secondaryColor,
                ),
              ),
            ),
          ),
      ],
    );
  }

  // AEW section for other users
  Widget _buildAEWSection() {
    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Administrative Extension Worker',
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontSmall,
                fontWeight: Config.fontW600,
              ),
            ),
            TextButton(
              onPressed: () => _navigateToAllAEWs(context),
              child: const Text(
                'See all',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: Config.fontSmall,
                  color: Config.tertiaryColor,
                ),
              ),
            ),
          ],
        ),
        Config.heightSmall,
        Column(
          children: [
            const AewCard(),
            Config.heightSmall,
            const AewCard(),
          ],
        ),
      ],
    );
  }

  // Navigation functions - passing HomeViewModel to AllActivitiesView
  void _navigateToAllActivities(BuildContext context, HomeViewModel homeViewModel) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => ChangeNotifierProvider.value(
          value: homeViewModel, // Share the same HomeViewModel instance
          child: const AllActivitiesView(),
        ),
      ),
    );
  }

  void _navigateToAllAEWs(BuildContext context) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => const AllAEWsView()),
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
            style: const TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontMedium,
            ),
          ),
        ],
      ),
    );
  }

  Widget _activityDetailsPopup(HomeViewModel homeViewModel) {
    final activity = homeViewModel.activity!;
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
            Text(homeViewModel.activity?.barangay.toString() ?? 'Unknown'),
          ],
        ),
      ],
    );
  }
}