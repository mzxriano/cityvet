import 'package:cityvet_app/components/card.dart';
import 'package:cityvet_app/models/aew_model.dart';
import 'package:cityvet_app/models/incident_model.dart';
import 'package:cityvet_app/services/incident_service.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/home_view_model.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:cityvet_app/views/main_screens/home/all_activities_page.dart';
import 'package:cityvet_app/views/main_screens/home/all_aew_page.dart';
import 'package:cityvet_app/views/activity_vaccination_report_view.dart';
import 'package:cityvet_app/views/main_screens/home/disease_info_page.dart';
import 'package:cityvet_app/views/chat_screen_view.dart';
import 'package:cityvet_app/views/incident_details_view.dart';
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
  late HomeViewModel _homeViewModel;
  List<AewModel> _aewUsers = [];
  bool _isLoadingAEW = false;
  late final IncidentService _incidentService;
  List<IncidentModel> _incidents = [];
  bool _isLoadingIncidents = false;

  @override
  void initState() {
    super.initState();
    _homeViewModel = HomeViewModel();
    _incidentService = IncidentService();
    // Initialize data fetching
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _homeViewModel.fetchUpcomingActivities();
      _homeViewModel.fetchOngoingActivities();
      _homeViewModel.fetchRecentActivities();
      _homeViewModel.fetchAEWUsers();
      _fetchAEWUsers();
      _fetchIncidents();
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

  Future<void> _fetchAEWUsers() async {
    if (mounted) {
      setState(() {
        _isLoadingAEW = true;
      });
    }

    try {
      final result = await _homeViewModel.fetchAEWUsers();
      
      if (mounted) {
        setState(() {
          _aewUsers = result;
          _isLoadingAEW = false;
        });
      }
    } catch (e) {
      print('Error fetching AEW users: $e');
      if (mounted) {
        setState(() {
          _isLoadingAEW = false;
        });
      }
    }
  }

  Future<void> _fetchIncidents() async {
    print('🔍 Starting to fetch incidents...');
    if (mounted) {
      setState(() {
        _isLoadingIncidents = true;
      });
    }

    try {
      final result = await _incidentService.fetchIncidentsForBarangay();
      print('📊 Incident fetch result: $result');
      
      if (mounted) {
        setState(() {
          if (result['success']) {
            // Show pending and under_review incidents for barangay personnel to manage
            _incidents = (result['data'] as List<IncidentModel>)
                .where((incident) => incident.status == 'pending' || incident.status == 'under_review')
                .toList();
            print('✅ Successfully loaded ${_incidents.length} incidents for review');
          } else {
            _incidents = [];
            print('❌ Failed to fetch incidents: ${result['message']}');
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(result['message'] ?? 'Failed to fetch incidents'),
                backgroundColor: Colors.red,
              ),
            );
          }
          _isLoadingIncidents = false;
        });
      }
    } catch (e) {
      print('💥 Error fetching incidents: $e');
      if (mounted) {
        setState(() {
          _isLoadingIncidents = false;
        });
      }
    }
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
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Column(
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFF8ED968),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.event,
                  color: Colors.white,
                  size: 32,
                ),
              ),
              const SizedBox(height: 12),
              Text(
                activity.reason,
                style: const TextStyle(
                  fontFamily: Config.primaryFont,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.center,
              ),
              Text(
                'Activity Details',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: Config.fontSmall,
                  color: Colors.grey[600],
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              _buildDetailRow(Icons.description_outlined, 'Details', activity.details),
              _buildDetailRow(Icons.calendar_today_outlined, 'Date', DateFormat('MMMM d, yyyy').format(activity.date)),
              _buildDetailRow(Icons.access_time_outlined, 'Time', DateFormat('h:mm a').format(activity.time)),
              _buildDetailRow(Icons.location_on_outlined, 'Location', activity.barangay.toString()),
            ],
          ),
          actions: [
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

  // Role-based section builder
  Widget _buildRoleBasedSection(HomeViewModel homeViewModel) {
    final userViewModel = Provider.of<UserViewModel>(context, listen: false);
    String userRole = userViewModel.user?.role ?? '';

    print(userRole);
    
    // Show incidents section for barangay personnel
    if (userRole == 'barangay_personnel') {
      return _buildIncidentsSection();
    }
    // Check if user is veterinarian or staff to show ongoing activities
    else if (userRole != 'pet_owner' && userRole != 'poultry_owner' && userRole != 'livestock_owner') {
      return _buildSingleOngoingActivity(homeViewModel);
    } else {
      return _buildAEWSection();
    }
  }

  // Incidents section for barangay personnel
  Widget _buildIncidentsSection() {
    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Incident Reports (For Review)',
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontMedium,
                fontWeight: FontWeight.w600,
              ),
            ),
            TextButton(
              onPressed: _isLoadingIncidents ? null : () => _fetchIncidents(),
              child: const Text(
                'Refresh',
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
        if (_isLoadingIncidents)
          const Center(child: CircularProgressIndicator())
        else if (_incidents.isEmpty)
          Center(
            child: Container(
              padding: const EdgeInsets.all(20.0),
              child: Column(
                children: [
                  Icon(
                    Icons.report_outlined,
                    size: 48,
                    color: Colors.grey[400],
                  ),
                  const SizedBox(height: 12),
                  const Text(
                    'No incident reports found.',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontSmall,
                      color: Config.secondaryColor,
                    ),
                  ),
                ],
              ),
            ),
          )
        else
          Column(
            children: _incidents.take(3).map((incident) => Container(
              margin: const EdgeInsets.only(bottom: 12),
              child: GestureDetector(
                onTap: () => _navigateToIncidentDetails(incident),
                child: Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.grey.withOpacity(0.2)),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.grey.withOpacity(0.1),
                        spreadRadius: 1,
                        blurRadius: 4,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: _getStatusColor(incident.status).withOpacity(0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Icon(
                              _getStatusIcon(incident.status),
                              color: _getStatusColor(incident.status),
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  incident.victimName,
                                  style: const TextStyle(
                                    fontFamily: Config.primaryFont,
                                    fontSize: Config.fontMedium,
                                    fontWeight: Config.fontW600,
                                    color: Color(0xFF524F4F),
                                  ),
                                ),
                                Text(
                                  '${incident.species} - ${incident.age} years old',
                                  style: const TextStyle(
                                    fontFamily: Config.primaryFont,
                                    fontSize: Config.fontSmall,
                                    color: Config.tertiaryColor,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: _getStatusColor(incident.status).withOpacity(0.1),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              incident.statusDisplayName,
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: 10,
                                color: _getStatusColor(incident.status),
                                fontWeight: FontWeight.w600,
                                letterSpacing: 0.5,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          const Icon(Icons.calendar_today_outlined, size: 16, color: Colors.grey),
                          const SizedBox(width: 6),
                          Text(
                            DateFormat('MMM d, yyyy • h:mm a').format(incident.incidentTime),
                            style: const TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: Config.fontSmall,
                              color: Colors.grey,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(Icons.location_on_outlined, size: 16, color: Colors.grey),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              incident.locationAddress,
                              style: const TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontSmall,
                                color: Colors.grey,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
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

  Color _getStatusColor(String status) {
    switch (status) {
      case 'pending':
        return Colors.orange;
      case 'under_review':
        return Colors.blue;
      case 'confirmed':
        return Colors.red;
      case 'disputed':
        return Colors.purple;
      default:
        return Colors.grey;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'pending':
        return Icons.pending;
      case 'under_review':
        return Icons.search;
      case 'confirmed':
        return Icons.check_circle;
      case 'disputed':
        return Icons.report_problem;
      default:
        return Icons.help;
    }
  }

  void _navigateToIncidentDetails(IncidentModel incident) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => IncidentDetailsView(
          incident: incident,
          canManageStatus: true, // Barangay personnel can manage status
        ),
      ),
    ).then((_) {
      // Refresh incidents when returning from details
      _fetchIncidents();
    });
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
      
      if (_isLoadingAEW)
        const Center(child: CircularProgressIndicator())
      else if (_aewUsers.isEmpty)
        Container(
          padding: const EdgeInsets.all(20),
          child: Column(
            children: [
              Icon(Icons.person_outline, size: 48, color: Colors.grey[400]),
              const SizedBox(height: 12),
              Text(
                'No AEW available',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: Config.fontSmall,
                  color: Colors.grey[600],
                ),
              ),
            ],
          ),
        )
      else
        Column(
          children: _aewUsers.take(2).map((aew) => Container(
            margin: const EdgeInsets.only(bottom: 12),
            child: GestureDetector(
              onTap: () => _showAEWDetails(context, aew),
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey.withOpacity(0.2)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 25,
                          backgroundColor: const Color(0xFF8ED968),
                          child: Text(
                            aew.name.split(' ').map((n) => n[0]).take(2).join(''),
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
                                aew.name,
                                style: const TextStyle(
                                  fontFamily: Config.primaryFont,
                                  fontSize: Config.fontMedium,
                                  fontWeight: Config.fontW600,
                                  color: Color(0xFF524F4F),
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                aew.position,
                                style: const TextStyle(
                                  fontFamily: Config.primaryFont,
                                  fontSize: Config.fontSmall,
                                  color: Config.tertiaryColor,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFF8ED968).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        aew.specialization,
                        style: const TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
                          color: Color(0xFF6BB54A),
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        const Icon(Icons.location_on_outlined, size: 16, color: Colors.grey),
                        const SizedBox(width: 4),
                        Text(aew.barangay.toString(), style: const TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontSmall, color: Colors.grey)),
                        const SizedBox(width: 16),
                        const Icon(Icons.phone_outlined, size: 16, color: Colors.grey),
                        const SizedBox(width: 4),
                        Expanded(child: Text(aew.contact, style: const TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontSmall, color: Colors.grey))),
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

void _showAEWDetails(BuildContext context, AewModel aew) {
  showDialog(
    context: context,
    builder: (BuildContext context) {
      return AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Column(
          children: [
            CircleAvatar(
              radius: 35,
              backgroundColor: const Color(0xFF8ED968),
              child: Text(
                aew.name.split(' ').map((n) => n[0]).take(2).join(''),
                style: const TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium, fontWeight: FontWeight.bold, color: Colors.white),
              ),
            ),
            const SizedBox(height: 8),
            Text(aew.name, style: const TextStyle(fontFamily: Config.primaryFont, fontWeight: FontWeight.bold), textAlign: TextAlign.center),
            Text(aew.position, style: const TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontSmall, color: Colors.grey), textAlign: TextAlign.center),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildDetailRow(Icons.work_outline, 'Specialization', aew.specialization),
            _buildDetailRow(Icons.location_on_outlined, 'Barangay', aew.barangay.toString()),
            _buildDetailRow(Icons.phone_outlined, 'Contact', aew.contact),
            _buildDetailRow(Icons.email_outlined, 'Email', aew.email),
          ],
        ),
        actions: [
          Row(
            children: [
              Expanded(
                child: TextButton.icon(
                  onPressed: () {
                    Navigator.pop(context);
                    _chatWithAew(aew.contact);
                  },
                  icon: const Icon(Icons.message),
                  label: const Text('Chat'),
                ),
              ),
            ],
          ),
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close', style: TextStyle(fontFamily: Config.primaryFont)),
          ),
        ],
      );
    },
  );
}

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
  Navigator.push(context, MaterialPageRoute(builder: (_) => ChatScreen()));
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

void _navigateToAllAEWs(BuildContext context) {
  Navigator.push(
    context,
    MaterialPageRoute(
      builder: (context) => AllAEWsView(aewUsers: _aewUsers),
    ),
  );
}

  // Animal card widget
  Widget _animalCard(String imageUrl, String label) {
    return GestureDetector(
      onTap: () => _showAnimalOptionsDialog(context, label),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: Colors.grey.withOpacity(0.2),
            width: 1,
          ),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Animal icon/image
            Container(
              child: Image.asset(
                imageUrl,
                fit: BoxFit.contain,
              ),
            ),
            const SizedBox(height: 8),
            
            // Animal name
            Text(
              label,
              style: const TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontSmall,
                fontWeight: Config.fontW600,
                color: Color(0xFF524F4F),
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  void _showAnimalOptionsDialog(BuildContext context, String animalType) {
  // Mock data for animal-related options
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
                    _handleAnimalOptionTap(animalType, option['title']);
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

  void _handleAnimalOptionTap(String animalType, String option) {
    switch (option) {
      case 'Diseases':
        Navigator.push(context, MaterialPageRoute(builder: (_) => DiseaseInfoPage(animalType: animalType,)));
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