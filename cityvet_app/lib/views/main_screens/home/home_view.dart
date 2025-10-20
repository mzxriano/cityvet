import 'package:cityvet_app/models/incident_model.dart';
import 'package:cityvet_app/services/incident_service.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/utils/role_constant.dart';
import 'package:cityvet_app/viewmodels/home_view_model.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:cityvet_app/views/activity_vaccination_report_view.dart';
import 'package:cityvet_app/views/incident_details_view.dart';
import 'package:cityvet_app/views/main_screens/home/all_activities_page.dart';
import 'package:cityvet_app/views/main_screens/home/dialogs/activity_dialog.dart';
import 'package:cityvet_app/views/main_screens/home/dialogs/animal_options_dialog.dart';
import 'package:cityvet_app/views/main_screens/home/sections/incidents_section.dart';
import 'package:cityvet_app/views/main_screens/home/sections/ongoing_activity_section.dart';
import 'package:cityvet_app/views/main_screens/home/widgets/animal_card.dart';
import 'package:cityvet_app/views/main_screens/home/widgets/nearby_clinics_map.dart';
import 'package:cityvet_app/views/main_screens/home/widgets/no_upcoming_events_card.dart';
import 'package:cityvet_app/views/main_screens/home/widgets/upcoming_activity_card.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class HomeView extends StatefulWidget {
  const HomeView({super.key});

  @override
  State<HomeView> createState() => HomeViewState();
}

class HomeViewState extends State<HomeView> {
  late HomeViewModel _homeViewModel;
  late final IncidentService _incidentService;
  List<IncidentModel> _incidents = [];
  bool _isLoadingIncidents = false;

  @override
  void initState() {
    super.initState();
    _homeViewModel = HomeViewModel();
    _incidentService = IncidentService();
    
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _homeViewModel.fetchUpcomingActivities();
      _homeViewModel.fetchOngoingActivities();
      _homeViewModel.fetchRecentActivities();
      _fetchIncidents();
    });
  }

  @override
  void dispose() {
    _homeViewModel.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    final List<Map<String, String>> animals = [
      {'image': 'assets/images/dog.png', 'label': 'Dog'},
      {'image': 'assets/images/cat.png', 'label': 'Cat'},
      {'image': 'assets/images/goat.png', 'label': 'Goat'},
      {'image': 'assets/images/cattle.png', 'label': 'Cattle'},
      {'image': 'assets/images/chicken.png', 'label': 'Chicken'},
    ];

    return ChangeNotifierProvider.value(
      value: _homeViewModel,
      builder: (context, child) {
        return Consumer<HomeViewModel>(
          builder: (context, homeViewModel, _) {
            final userViewModel = Provider.of<UserViewModel>(context, listen: false);
            final userBarangayName = userViewModel.user?.barangay?.name;
            final userRole = userViewModel.user?.role ?? '';

            // Vet, AEW, sub admin, and staff can see all upcoming activities
            // Pet/poultry/livestock owners only see their barangay activities
            final showAllActivities = userRole == Role.veterinarian || 
                                      userRole == Role.aew || 
                                      userRole == Role.subAdmin || 
                                      userRole == Role.staff;

            final filteredUpcoming = showAllActivities 
                ? (homeViewModel.upcomingActivities ?? [])
                : (homeViewModel.upcomingActivities?.where((activity) => activity.barangay == userBarangayName).toList() ?? []);

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
                              child: AnimalCard(
                                imageUrl: animals[index]['image']!,
                                label: animals[index]['label']!,
                                onTap: () => AnimalOptionsDialog.show(
                                  context,
                                  animals[index]['label']!,
                                ),
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
                      else if (filteredUpcoming.isNotEmpty)
                        UpcomingActivityCard(
                          activity: filteredUpcoming.first,
                          onTap: () => ActivityDialog.show(
                            context,
                            filteredUpcoming.first,
                          ),
                        )
                      else
                        const NoUpcomingEventsCard(),
                      
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

  Future<void> _fetchIncidents() async {
    if (!mounted) return;
    
    setState(() {
      _isLoadingIncidents = true;
    });

    try {
      final result = await _incidentService.fetchIncidentsForBarangay();

      if (mounted) {
        setState(() {
          if (result['success']) {
            _incidents = (result['data'] as List<IncidentModel>)
                .where((incident) =>
                    incident.status == 'pending' ||
                    incident.status == 'under_review')
                .toList();
          } else {
            _incidents = [];
            if (context.mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(result['message'] ?? 'Failed to fetch incidents'),
                  backgroundColor: Colors.red,
                ),
              );
            }
          }
          _isLoadingIncidents = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoadingIncidents = false;
        });
      }
    }
  }

  // Role-based section builder
  Widget _buildRoleBasedSection(HomeViewModel homeViewModel) {
    final userViewModel = Provider.of<UserViewModel>(context, listen: false);
    String userRole = userViewModel.user?.role ?? '';

    if (userRole == 'barangay_personnel') {
      return IncidentsSection(
        isLoading: _isLoadingIncidents,
        incidents: _incidents,
        onRefresh: _fetchIncidents,
        onIncidentTap: _navigateToIncidentDetails,
      );
    } else if (userRole != 'pet_owner' &&
        userRole != 'poultry_owner' &&
        userRole != 'livestock_owner') {
      return OngoingActivitySection(
        homeViewModel: homeViewModel,
        onSeeRecent: () => _navigateToAllActivities(context, homeViewModel),
        onActivityTap: (activity) => _navigateToActivityDetails(context, activity),
      );
    } else {
      return const NearbyClinicsMap();
    }
  }

  void _navigateToIncidentDetails(IncidentModel incident) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => IncidentDetailsView(
          incident: incident,
          canManageStatus: true,
        ),
      ),
    ).then((_) {
      _fetchIncidents();
    });
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
    final userViewModel = Provider.of<UserViewModel>(context, listen: false);
    String userRole = userViewModel.user?.role ?? '';

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => ActivityVaccinationReportView(
          activityId: activity.id.toString(),
          userRole: userRole,
        ),
      ),
    );
  }
}