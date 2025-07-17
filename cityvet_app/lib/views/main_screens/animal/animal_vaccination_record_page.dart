import 'package:flutter/material.dart';
import 'package:cityvet_app/utils/config.dart';

class VaccinationRecord extends StatefulWidget {
  final String animalName;
  
  const VaccinationRecord({
    super.key, 
    required this.animalName,
  });

  @override
  State<VaccinationRecord> createState() => _VaccinationRecordState();
}

class _VaccinationRecordState extends State<VaccinationRecord> with TickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 600),
      vsync: this,
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeInOut),
    );
    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, 0.3),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _animationController, curve: Curves.easeOutBack));
    
    _animationController.forward();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  // Static vaccination data
  final List<Map<String, String>> vaccinations = [
    {
      'name': 'Rabies Vaccine',
      'date': 'January 15, 2024',
      'administrator': 'Dr. Sarah Johnson',
      'dose': '1st dose',
      'nextDue': 'January 15, 2025',
    },
    {
      'name': 'DHPP (Distemper, Hepatitis, Parvovirus, Parainfluenza)',
      'date': 'December 10, 2023',
      'administrator': 'Dr. Michael Chen',
      'dose': '2nd dose',
      'nextDue': 'December 10, 2024',
    },
    {
      'name': 'Bordetella',
      'date': 'November 22, 2023',
      'administrator': 'Dr. Sarah Johnson',
      'dose': '1st dose',
      'nextDue': 'November 22, 2024',
    },
    {
      'name': 'Lyme Disease Vaccine',
      'date': 'October 18, 2023',
      'administrator': 'Dr. Emily Rodriguez',
      'dose': '1st dose',
      'nextDue': 'October 18, 2024',
    },
    {
      'name': 'Canine Influenza',
      'date': 'September 30, 2023',
      'administrator': 'Dr. Michael Chen',
      'dose': '1st dose',
      'nextDue': 'September 30, 2024',
    },
  ];

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        elevation: 0,
        backgroundColor: Colors.white,
        leading: IconButton(
          onPressed: () => Navigator.pop(context),
          icon: Config.backButtonIcon,
        ),
        title: Text(
          'Vaccination Record',
          style: TextStyle(
            color: Config.tertiaryColor,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
      ),
      body: FadeTransition(
        opacity: _fadeAnimation,
        child: SlideTransition(
          position: _slideAnimation,
          child: SingleChildScrollView(
            padding: Config.paddingScreen,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header with pet icon
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        Config.primaryColor.withOpacity(0.1),
                        Config.primaryColor.withOpacity(0.05),
                      ],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: Config.primaryColor.withOpacity(0.2),
                      width: 1,
                    ),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Config.primaryColor.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(
                          Icons.medical_services,
                          color: Config.primaryColor,
                          size: 32,
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Vaccination Record',
                              style: TextStyle(
                                fontSize: 18,
                                fontFamily: Config.primaryFont,
                                fontWeight: FontWeight.w600,
                                color: Config.tertiaryColor,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Complete vaccination records for ${widget.animalName}',
                              style: TextStyle(
                                fontSize: 14,
                                fontFamily: Config.primaryFont,
                                color: Colors.grey[600],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 24),

                // Vaccination cards list
                ...vaccinations.map((vaccination) => _buildVaccinationCard(vaccination)).toList(),

                const SizedBox(height: 20),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildVaccinationCard(Map<String, String> vaccination) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header with vaccine name and icon
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Config.primaryColor.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(
                  Icons.vaccines,
                  color: Config.primaryColor,
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  vaccination['name']!,
                  style: TextStyle(
                    fontSize: 16,
                    fontFamily: Config.primaryFont,
                    fontWeight: FontWeight.w600,
                    color: Config.tertiaryColor,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // Vaccine details in a grid layout
          Row(
            children: [
              Expanded(
                child: _buildDetailColumn(
                  'Date Given',
                  vaccination['date']!,
                  Icons.calendar_today,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildDetailColumn(
                  'Dose',
                  vaccination['dose']!,
                  Icons.numbers,
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          Row(
            children: [
              Expanded(
                child: _buildDetailColumn(
                  'Administered by',
                  vaccination['administrator']!,
                  Icons.person,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildDetailColumn(
                  'Next Due',
                  vaccination['nextDue']!,
                  Icons.schedule,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildDetailColumn(String title, String value, IconData icon) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(
              icon,
              size: 14,
              color: Config.primaryColor,
            ),
            const SizedBox(width: 4),
            Text(
              title,
              style: TextStyle(
                fontSize: 12,
                fontFamily: Config.primaryFont,
                fontWeight: FontWeight.w500,
                color: Colors.grey[600],
              ),
            ),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: TextStyle(
            fontSize: 14,
            fontFamily: Config.primaryFont,
            fontWeight: FontWeight.w500,
            color: Config.tertiaryColor,
          ),
        ),
      ],
    );
  }
}