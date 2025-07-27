import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class AllAEWsView extends StatefulWidget {
  const AllAEWsView({super.key});

  @override
  State<AllAEWsView> createState() => _AllAEWsViewState();
}

class _AllAEWsViewState extends State<AllAEWsView> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  
  // Mock data - replace with actual data from your service/viewmodel
  final List<Map<String, dynamic>> _aews = [
    {
      'id': 1,
      'name': 'John Doe',
      'position': 'Senior AEW',
      'barangay': 'Barangay 1',
      'contact': '+63 912 345 6789',
      'email': 'john.doe@cityvet.gov',
      'specialization': 'Livestock Management',
      'yearsOfService': 5,
    },
    {
      'id': 2,
      'name': 'Jane Smith',
      'position': 'AEW',
      'barangay': 'Barangay 2',
      'contact': '+63 923 456 7890',
      'email': 'jane.smith@cityvet.gov',
      'specialization': 'Poultry Care',
      'yearsOfService': 3,
    },
    {
      'id': 3,
      'name': 'Robert Johnson',
      'position': 'Senior AEW',
      'barangay': 'Barangay 3',
      'contact': '+63 934 567 8901',
      'email': 'robert.johnson@cityvet.gov',
      'specialization': 'Veterinary Medicine',
      'yearsOfService': 8,
    },
    {
      'id': 4,
      'name': 'Maria Garcia',
      'position': 'AEW',
      'barangay': 'Barangay 4',
      'contact': '+63 945 678 9012',
      'email': 'maria.garcia@cityvet.gov',
      'specialization': 'Animal Breeding',
      'yearsOfService': 2,
    },
    {
      'id': 5,
      'name': 'David Wilson',
      'position': 'Lead AEW',
      'barangay': 'Barangay 5',
      'contact': '+63 956 789 0123',
      'email': 'david.wilson@cityvet.gov',
      'specialization': 'Disease Prevention',
      'yearsOfService': 10,
    },
  ];

  List<Map<String, dynamic>> get _filteredAEWs {
    if (_searchQuery.isEmpty) {
      return _aews;
    }
    return _aews.where((aew) {
      return aew['name'].toLowerCase().contains(_searchQuery.toLowerCase()) ||
             aew['barangay'].toLowerCase().contains(_searchQuery.toLowerCase()) ||
             aew['specialization'].toLowerCase().contains(_searchQuery.toLowerCase());
    }).toList();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Administrative Extension Workers',
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontSize: Config.fontMedium,
            fontWeight: Config.fontW600,
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.black),
      ),
      body: Container(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Search bar
            Container(
              width: double.infinity,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(8),
                boxShadow: [
                  BoxShadow(
                    color: Colors.grey.withOpacity(0.1),
                    spreadRadius: 1,
                    blurRadius: 3,
                    offset: const Offset(0, 1),
                  ),
                ],
              ),
              child: TextField(
                controller: _searchController,
                onChanged: (value) {
                  setState(() {
                    _searchQuery = value;
                  });
                },
                decoration: const InputDecoration(
                  hintText: 'Search AEWs by name, barangay, or specialization...',
                  hintStyle: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontSmall,
                    color: Colors.grey,
                  ),
                  border: InputBorder.none,
                  contentPadding: EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                  prefixIcon: Icon(Icons.search, color: Colors.grey),
                ),
                style: const TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: Config.fontSmall,
                ),
              ),
            ),
            
            const SizedBox(height: 20),
            
            // Results count
            if (_searchQuery.isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(bottom: 16),
                child: Row(
                  children: [
                    Text(
                      '${_filteredAEWs.length} result${_filteredAEWs.length != 1 ? 's' : ''} found',
                      style: const TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontSmall,
                        color: Colors.grey,
                      ),
                    ),
                    if (_searchQuery.isNotEmpty)
                      TextButton(
                        onPressed: () {
                          _searchController.clear();
                          setState(() {
                            _searchQuery = '';
                          });
                        },
                        child: const Text(
                          'Clear',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: Config.fontSmall,
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            
            // AEWs list
            Expanded(
              child: _buildAEWsList(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAEWsList() {
    if (_filteredAEWs.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.person_search_outlined,
              size: 64,
              color: Colors.grey[400],
            ),
            const SizedBox(height: 16),
            Text(
              _searchQuery.isEmpty ? 'No AEWs found' : 'No AEWs match your search',
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontMedium,
                color: Colors.grey[600],
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              _searchQuery.isEmpty 
                ? 'AEW information will appear here.'
                : 'Try adjusting your search terms.',
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

    return ListView.separated(
      itemCount: _filteredAEWs.length,
      separatorBuilder: (context, index) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final aew = _filteredAEWs[index];
        
        return GestureDetector(
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
        );
      },
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
                      // Handle call action
                      Navigator.pop(context);
                      _makePhoneCall(aew['contact']);
                    },
                    icon: const Icon(Icons.phone),
                    label: const Text('Call'),
                  ),
                ),
                Expanded(
                  child: TextButton.icon(
                    onPressed: () {
                      // Handle email action  
                      Navigator.pop(context);
                      _sendEmail(aew['email']);
                    },
                    icon: const Icon(Icons.email),
                    label: const Text('Email'),
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

  void _makePhoneCall(String phoneNumber) {
    // Implement phone call functionality
    // You can use url_launcher package: launch("tel:$phoneNumber")
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Calling $phoneNumber...')),
    );
  }

  void _sendEmail(String email) {
    // Implement email functionality
    // You can use url_launcher package: launch("mailto:$email")
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Opening email to $email...')),
    );
  }
}