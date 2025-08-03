import 'package:cityvet_app/components/card.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/home_view_model.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:cityvet_app/views/main_screens/home/all_activities_page.dart';
import 'package:cityvet_app/views/main_screens/home/all_aew_page.dart';
import 'package:cityvet_app/views/activity_vaccination_report_view.dart';
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
  late HomeViewModel _homeViewModel;

  @override
  void initState() {
    super.initState();
    _homeViewModel = HomeViewModel();
    // Initialize data fetching
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _homeViewModel.fetchUpcomingActivities();
      _homeViewModel.fetchOngoingActivities();
      _homeViewModel.fetchRecentActivities();
    });
  }

  @override
  void dispose() {
    _activitySearchController.dispose();
    _homeViewModel.dispose();
    super.dispose();
  }

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

    return ChangeNotifierProvider.value(
      value: _homeViewModel,
      builder: (context, child) {
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

                      // Show loading indicator for upcoming activities
                      if (homeViewModel.isLoadingUpcoming)
                        Container(
                          padding: const EdgeInsets.symmetric(vertical: 40),
                          child: const Center(child: CircularProgressIndicator()),
                        )
                      else if (homeViewModel.upcomingActivities != null && homeViewModel.upcomingActivities!.isNotEmpty)
                        _buildSingleUpcomingActivity(homeViewModel)
                      else
                        _buildNoUpcomingEventsCard(),
                      
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

  Widget _buildSingleUpcomingActivity(HomeViewModel homeViewModel) {
    // Show only the first upcoming activity
    final activity = homeViewModel.upcomingActivities!.first;

    return GestureDetector(
      onTap: () => _showActivityDialog(context, activity),
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [
              const Color(0xFF8ED968).withOpacity(0.1),
              const Color(0xFF8ED968).withOpacity(0.05),
            ],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: const Color(0xFF8ED968).withOpacity(0.3),
            width: 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.grey.withOpacity(0.08),
              spreadRadius: 1,
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header row with icon and "upcoming" badge
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: const Color(0xFF8ED968),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(
                    Icons.event,
                    color: Colors.white,
                    size: 20,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    activity.reason,
                    style: const TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontMedium,
                      fontWeight: Config.fontW600,
                      color: Color(0xFF524F4F),
                    ),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.orange.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Text(
                    'UPCOMING',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: 10,
                      color: Colors.orange,
                      fontWeight: FontWeight.w600,
                      letterSpacing: 0.5,
                    ),
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: 16),
            
            // Activity details
            Text(
              activity.details,
              style: const TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontSmall,
                color: Color(0xFF6B7280),
                height: 1.4,
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            
            const SizedBox(height: 16),
            
            // Date, time, and location info
            Row(
              children: [
                // Date
                Expanded(
                  child: Row(
                    children: [
                      Icon(
                        Icons.calendar_today_outlined,
                        size: 16,
                        color: Colors.grey[600],
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          DateFormat('MMM d, yyyy').format(activity.date),
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontSmall,
                            color: Colors.grey[600],
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                
                // Time
                Expanded(
                  child: Row(
                    children: [
                      Icon(
                        Icons.access_time_outlined,
                        size: 16,
                        color: Colors.grey[600],
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          DateFormat('h:mm a').format(activity.time),
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontSmall,
                            color: Colors.grey[600],
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: 8),
            
            // Location
            Row(
              children: [
                Icon(
                  Icons.location_on_outlined,
                  size: 16,
                  color: Colors.grey[600],
                ),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    activity.barangay.toString(),
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontSmall,
                      color: Colors.grey[600],
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
                
                // Tap to view more indicator
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.8),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: Colors.grey.withOpacity(0.2),
                      width: 1,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'View Details',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: 10,
                          color: Colors.grey[700],
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(width: 4),
                      Icon(
                        Icons.arrow_forward_ios,
                        size: 10,
                        color: Colors.grey[700],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNoUpcomingEventsCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 20),
      decoration: BoxDecoration(
        color: Colors.grey[50],
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: Colors.grey.withOpacity(0.2),
          width: 1,
        ),
      ),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.grey[100],
              shape: BoxShape.circle,
            ),
            child: Icon(
              Icons.event_busy_outlined,
              size: 32,
              color: Colors.grey[400],
            ),
          ),
          const SizedBox(height: 16),
          Text(
            'No Upcoming Events',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontMedium,
              fontWeight: Config.fontW600,
              color: Colors.grey[600],
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Check back later for scheduled activities.',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontSmall,
              color: Colors.grey[500],
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  void _showActivityDialog(BuildContext context, dynamic activity) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          title: Text(
            activity.reason,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
          content: _activityDetailsPopup(activity),
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
    
    // Check if user is veterinarian or staff to show ongoing activities
    if (userRole != 'owner') {
      return _buildSingleOngoingActivity(homeViewModel);
    } else {
      return _buildAEWSection();
    }
  }

  // Single Ongoing Activity section for veterinarian/staff
  Widget _buildSingleOngoingActivity(HomeViewModel homeViewModel) {
    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'On Going Activity',
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontMedium,
                fontWeight: FontWeight.w600,
              ),
            ),
            TextButton(
              onPressed: () => _navigateToAllActivities(context, homeViewModel),
              child: const Text(
                'See Recent Activities',
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
        if (homeViewModel.isLoadingOngoing)
          const Center(child: CircularProgressIndicator())
        else if (homeViewModel.ongoingActivities != null && homeViewModel.ongoingActivities!.isNotEmpty)
          // Show only the first ongoing activity
          GestureDetector(
            onTap: () => _navigateToActivityDetails(context, homeViewModel.ongoingActivities!.first),
            child: CustomCard(
              width: double.infinity,
              color: Colors.white,
              widget: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.blue.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Text(
                          'ON GOING',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: 10,
                            color: Colors.blue,
                            fontWeight: FontWeight.w600,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ),
                      const Spacer(),
                      Icon(
                        Icons.arrow_forward_ios,
                        size: 16,
                        color: Config.tertiaryColor,
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    homeViewModel.ongoingActivities!.first.reason,
                    style: const TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontMedium,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF524F4F),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    homeViewModel.ongoingActivities!.first.barangay.toString(),
                    style: const TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontSmall,
                      color: Config.tertiaryColor,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    DateFormat('MMM d, yyyy • h:mm a').format(homeViewModel.ongoingActivities!.first.date),
                    style: const TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontSmall,
                      color: Config.tertiaryColor,
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
              child: Column(
                children: [
                  Icon(
                    Icons.work_outline,
                    size: 48,
                    color: Colors.grey[400],
                  ),
                  const SizedBox(height: 12),
                  const Text(
                    'No ongoing activities.',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontSmall,
                      color: Config.secondaryColor,
                    ),
                  ),
                ],
              ),
            ),
          ),
      ],
    );
  }

Widget _buildAEWSection() {
  // Mock data for 2 AEWs to display in home view
  final List<Map<String, dynamic>> homeAEWs = [
    {
      'id': 1,
      'name': 'John Doe',
      'position': 'AEW',
      'barangay': 'Barangay 1',
      'contact': '+63 912 345 6789',
      'email': 'john.doe@cityvet.gov',
      'specialization': 'Agricultural Extension Worker',
      'yearsOfService': 5,
    },
    {
      'id': 2,
      'name': 'Jane Smith',
      'position': 'AEW',
      'barangay': 'Barangay 2',
      'contact': '+63 923 456 7890',
      'email': 'jane.smith@cityvet.gov',
      'specialization': 'Agricultural Extension Worker',
      'yearsOfService': 3,
    },
  ];

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
        children: homeAEWs.map((aew) => Container(
          margin: const EdgeInsets.only(bottom: 12),
          child: GestureDetector(
            onTap: () => _showAEWDetails(context, aew),
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color: Colors.grey.withOpacity(0.1),
                    spreadRadius: 1,
                    blurRadius: 3,
                    offset: const Offset(0, 1),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Name and position
                  Row(
                    children: [
                      CircleAvatar(
                        radius: 25,
                        backgroundColor: const Color(0xFF8ED968),
                        child: Text(
                          aew['name'].split(' ').map((n) => n[0]).take(2).join(''),
                          style: const TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontSmall,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              aew['name'],
                              style: const TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontMedium,
                                fontWeight: Config.fontW600,
                                color: Color(0xFF524F4F),
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              aew['position'],
                              style: const TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontSmall,
                                color: Config.tertiaryColor,
                              ),
                            ),
                          ],
                        ),
                      ),
                      // Years of service badge
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.blue.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          '${aew['yearsOfService']} yrs',
                          style: const TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: 10,
                            color: Colors.blue,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ],
                  ),
                  
                  const SizedBox(height: 12),
                  
                  // Specialization
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFF8ED968).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      aew['specialization'],
                      style: const TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontSmall,
                        color: Color(0xFF6BB54A),
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                  
                  const SizedBox(height: 12),
                  
                  // Contact info
                  Row(
                    children: [
                      const Icon(
                        Icons.location_on_outlined,
                        size: 16,
                        color: Colors.grey,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        aew['barangay'],
                        style: const TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
                          color: Colors.grey,
                        ),
                      ),
                      
                      const SizedBox(width: 16),
                      
                      const Icon(
                        Icons.phone_outlined,
                        size: 16,
                        color: Colors.grey,
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          aew['contact'],
                          style: const TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontSmall,
                            color: Colors.grey,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        )).toList(),
      ),
    ],
  );
}

void _showAEWDetails(BuildContext context, Map<String, dynamic> aew) {
  showDialog(
    context: context,
    builder: (BuildContext context) {
      return AlertDialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
        ),
        title: Column(
          children: [
            CircleAvatar(
              radius: 35,
              backgroundColor: const Color(0xFF8ED968),
              child: Text(
                aew['name'].split(' ').map((n) => n[0]).take(2).join(''),
                style: const TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: Config.fontMedium,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              aew['name'],
              style: const TextStyle(
                fontFamily: Config.primaryFont,
                fontWeight: FontWeight.bold,
              ),
              textAlign: TextAlign.center,
            ),
            Text(
              aew['position'],
              style: const TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontSmall,
                color: Colors.grey,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildDetailRow(Icons.work_outline, 'Specialization', aew['specialization']),
            _buildDetailRow(Icons.location_on_outlined, 'Barangay', aew['barangay']),
            _buildDetailRow(Icons.phone_outlined, 'Contact', aew['contact']),
            _buildDetailRow(Icons.email_outlined, 'Email', aew['email']),
            _buildDetailRow(Icons.timeline_outlined, 'Years of Service', '${aew['yearsOfService']} years'),
          ],
        ),
        actions: [
          Row(
            children: [
              Expanded(
                child: TextButton.icon(
                  onPressed: () {
                    Navigator.pop(context);
                    _chatWithAew(aew['contact']);
                  },
                  icon: const Icon(Icons.message),
                  label: const Text('Chat'),
                ),
              ),
            ],
          ),
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text(
              'Close',
              style: TextStyle(fontFamily: Config.primaryFont),
            ),
          ),
        ],
      );
    },
  );
}

// Add this helper method for detail rows
Widget _buildDetailRow(IconData icon, String label, String value) {
  return Padding(
    padding: const EdgeInsets.symmetric(vertical: 4),
    child: Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 18, color: Colors.grey),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: const TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: Config.fontSmall,
                  color: Colors.grey,
                  fontWeight: FontWeight.w500,
                ),
              ),
              Text(
                value,
                style: const TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: Config.fontSmall,
                ),
              ),
            ],
          ),
        ),
      ],
    ),
  );
}

void _chatWithAew(String phoneNumber) {
  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(content: Text('Opening a chat box')),
  );
}

void _sendEmail(String email) {
  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(content: Text('Opening email to $email...')),
  );
}

  void _navigateToAllActivities(BuildContext context, HomeViewModel homeViewModel) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => ChangeNotifierProvider.value(
          value: homeViewModel, 
          child: const AllActivitiesView(),
        ),
      ),
    );
  }

  void _navigateToActivityDetails(BuildContext context, dynamic activity) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => ActivityVaccinationReportView(
          activityId: activity.id.toString(),
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

  Widget _activityDetailsPopup(dynamic activity) {
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
            Text(activity.barangay.toString()),
          ],
        ),
      ],
    );
  }
}