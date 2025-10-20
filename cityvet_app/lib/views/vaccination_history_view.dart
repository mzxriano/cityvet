import 'package:cityvet_app/utils/api_constant.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/utils/role_constant.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:provider/provider.dart';

class VaccinationHistoryView extends StatefulWidget {
  const VaccinationHistoryView({super.key});

  @override
  State<VaccinationHistoryView> createState() => _VaccinationHistoryViewState();
}

class _VaccinationHistoryViewState extends State<VaccinationHistoryView> {
  List<dynamic> vaccinationRecords = [];
  List<dynamic> filteredRecords = [];
  bool isLoading = true;
  String errorMessage = '';
  final Dio _dio = Dio();
  bool _isDisposed = false;

  // Search and Filter Controllers
  final TextEditingController _searchController = TextEditingController();
  String _selectedFilter = 'All';
  final List<String> _filterOptions = ['All', 'This Month', 'Last 3 Months', 'This Year'];
  bool _showSearchBar = false;
  String? userRole;

  @override
  void initState() {
    super.initState();
    _searchController.addListener(_performSearch);
    _initializeAndFetch();
    userRole = Provider.of<UserViewModel>(context, listen: false).user?.role;
  }

  @override
  void dispose() {
    _isDisposed = true;
    _searchController.dispose();
    _dio.close(); 
    super.dispose();
  }

  Future<void> _initializeAndFetch() async {
    try {
      await _setupDio();
      await fetchVaccinationRecords();
    } catch (e) {
      if (!_isDisposed && mounted) {
        setState(() {
          errorMessage = 'Initialization failed: ${e.toString()}';
          isLoading = false;
        });
      }
    }
  }

  Future<void> _setupDio() async {
    _dio.options.baseUrl = ApiConstant.baseUrl; 
    _dio.options.connectTimeout = const Duration(seconds: 15);
    _dio.options.receiveTimeout = const Duration(seconds: 15); 
    _dio.options.sendTimeout = const Duration(seconds: 15); 

    try {
      final token = await AuthStorage().getToken();
      
      if (token != null) {
        _dio.options.headers['Authorization'] = 'Bearer $token';
      } else {
        throw Exception('Authentication token not found');
      }
    } catch (e) {
      throw Exception('Failed to setup authentication: $e');
    }
  }

  Future<void> fetchVaccinationRecords() async {
    if (_isDisposed || !mounted) return;

    try {
      if (mounted) {
        setState(() {
          isLoading = true;
          errorMessage = '';
        });
      }

      final response = await _dio.get('/vaccination-records');
      
      if (!mounted || _isDisposed) return;
      
      if (response.statusCode == 200) {
        setState(() {
          if (response.data is List) {
            vaccinationRecords = response.data;
          } else if (response.data is Map && response.data['data'] is List) {
            vaccinationRecords = response.data['data'];
          } else if (response.data is Map && response.data['records'] is List) {
            vaccinationRecords = response.data['records'];
          } else if (response.data is Map && response.data['vaccination_records'] is List) {
            vaccinationRecords = response.data['vaccination_records'];
          } else {
            print('Unexpected response structure: ${response.data}');
            vaccinationRecords = [];
          }
          _applyFilters();
          isLoading = false;
        });
      } else {
        if (mounted) {
          setState(() {
            errorMessage = 'Failed to load vaccination records (Status: ${response.statusCode})';
            isLoading = false;
          });
        }
      }
    } on DioException catch (e) {
      if (!mounted || _isDisposed) return;
      
      setState(() {
        if (e.type == DioExceptionType.connectionTimeout) {
          errorMessage = 'Connection timeout. Please check your internet connection.';
        } else if (e.type == DioExceptionType.receiveTimeout) {
          errorMessage = 'Server took too long to respond.';
        } else if (e.type == DioExceptionType.sendTimeout) {
          errorMessage = 'Request took too long to send.';
        } else if (e.response?.statusCode == 401) {
          errorMessage = 'Authentication failed. Please login again.';
        } else if (e.response?.statusCode == 404) {
          errorMessage = 'Vaccination records not found.';
        } else if (e.response?.statusCode == 500) {
          print('Server error response: ${e.response?.data}');
          errorMessage = 'Server error. Please try again later.';
        } else {
          print('Network error: ${e.response?.data}');
          errorMessage = 'Network error: ${e.message ?? 'Unknown error'}';
        }
        isLoading = false;
      });
    } catch (e) {
      if (!mounted || _isDisposed) return;
      
      setState(() {
        print('Unexpected error: $e');
        errorMessage = 'Unexpected error: ${e.toString()}';
        isLoading = false;
      });
    }
  }

  void _performSearch() {
    _applyFilters();
  }

  void _applyFilters() {
    if (!mounted) return;

    setState(() {
      List<dynamic> records = List.from(vaccinationRecords);

      // Apply date filter
      if (_selectedFilter != 'All') {
        final now = DateTime.now();
        records = records.where((record) {
          final dateStr = record['date_given'];
          if (dateStr == null) return false;
          
          try {
            final date = DateTime.parse(dateStr);
            switch (_selectedFilter) {
              case 'This Month':
                return date.year == now.year && date.month == now.month;
              case 'Last 3 Months':
                final threeMonthsAgo = DateTime(now.year, now.month - 3, now.day);
                return date.isAfter(threeMonthsAgo);
              case 'This Year':
                return date.year == now.year;
              default:
                return true;
            }
          } catch (e) {
            return false;
          }
        }).toList();
      }

      // Apply search filter
      final searchQuery = _searchController.text.toLowerCase().trim();
      if (searchQuery.isNotEmpty) {
        records = records.where((record) {
          final vaccineName = (record['vaccine_name'] ?? '').toString().toLowerCase();
          final animalName = (record['animal_name'] ?? '').toString().toLowerCase();
          final ownerName = (record['owner_full_name'] ?? record['owner_name'] ?? '').toString().toLowerCase();
          final administrator = (record['administrator'] ?? '').toString().toLowerCase();
          final animalType = (record['animal_type'] ?? '').toString().toLowerCase();
          final breed = (record['animal_breed'] ?? '').toString().toLowerCase();

          return vaccineName.contains(searchQuery) ||
                 animalName.contains(searchQuery) ||
                 ownerName.contains(searchQuery) ||
                 administrator.contains(searchQuery) ||
                 animalType.contains(searchQuery) ||
                 breed.contains(searchQuery);
        }).toList();
      }

      filteredRecords = records;
    });
  }

  Future<void> _refreshData() async {
    if (_isDisposed || !mounted) return;
    
    try {
      await _setupDio();
      await fetchVaccinationRecords();
    } catch (e) {
      if (mounted) {
        setState(() {
          errorMessage = 'Refresh failed: ${e.toString()}';
          isLoading = false;
        });
      }
    }
  }

  void _clearSearch() {
    _searchController.clear();
    setState(() {
      _showSearchBar = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    final mainBody = Container(
      decoration: BoxDecoration(
        // gradient: LinearGradient(
        //   begin: Alignment.topCenter,
        //   end: Alignment.bottomCenter,
        //   colors: [
        //     Colors.green[50]!,
        //     Colors.white,
        //   ],
        // ),
      ),
      child: Column(
        children: [
          // Filter/Results info bar
          if (vaccinationRecords.isNotEmpty && !isLoading)
            Container(
              width: double.infinity,
              padding: userRole == Role.veterinarian && userRole == Role.staff ? 
                EdgeInsets.zero : const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
              child: Row(
                children: [
                  Icon(Icons.info_outline, size: 16, color: Colors.green[700]),
                  const SizedBox(width: 8),
                  Text(
                    'Showing ${filteredRecords.length} of ${vaccinationRecords.length} records',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.green[700],
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  if (_selectedFilter != 'All') ...[
                    const Spacer(),
                    Chip(
                      label: Text(
                        _selectedFilter,
                        style: const TextStyle(fontSize: 10),
                      ),
                      backgroundColor: Colors.green[100],
                      deleteIcon: const Icon(Icons.close, size: 14),
                      onDeleted: () {
                        setState(() {
                          _selectedFilter = 'All';
                        });
                        _applyFilters();
                      },
                    ),
                  ],
                ],
              ),
            ),
          // Filters (moved from AppBar to body)
          Padding(
            padding: userRole == Role.veterinarian && userRole == Role.staff ? 
              EdgeInsets.zero : const EdgeInsets.all(16.0),
            child: Row(
              children: [
                Expanded(
                  child: _showSearchBar
                      ? TextField(
                          controller: _searchController,
                          autofocus: true,
                          style: const TextStyle(color: Colors.black),
                          decoration: const InputDecoration(
                            hintText: 'Search vaccinations...',
                            hintStyle: TextStyle(color: Colors.black54),
                            border: OutlineInputBorder(),
                            prefixIcon: Icon(Icons.search, color: Colors.black54),
                          ),
                        )
                      : const Text('Vaccination History', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                ),
                if (_showSearchBar)
                  IconButton(
                    icon: const Icon(Icons.clear),
                    onPressed: _clearSearch,
                    tooltip: 'Clear search',
                  )
                else
                  IconButton(
                    icon: const Icon(Icons.search),
                    onPressed: () {
                      setState(() {
                        _showSearchBar = true;
                      });
                    },
                    tooltip: 'Search',
                  ),
                PopupMenuButton<String>(
                  icon: const Icon(Icons.filter_list),
                  tooltip: 'Filter',
                  onSelected: (value) {
                    setState(() {
                      _selectedFilter = value;
                    });
                    _applyFilters();
                  },
                  itemBuilder: (context) => _filterOptions.map((filter) {
                    return PopupMenuItem<String>(
                      value: filter,
                      child: Row(
                        children: [
                          Icon(
                            _selectedFilter == filter 
                              ? Icons.radio_button_checked 
                              : Icons.radio_button_unchecked,
                            color: Colors.green[600],
                            size: 20,
                          ),
                          const SizedBox(width: 8),
                          Text(filter),
                        ],
                      ),
                    );
                  }).toList(),
                ),
                IconButton(
                  icon: const Icon(Icons.refresh),
                  onPressed: isLoading ? null : _refreshData,
                  tooltip: 'Refresh',
                ),
              ],
            ),
          ),
          // Main content
          Expanded(
            child: isLoading
                ? const Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        CircularProgressIndicator(color: Colors.green),
                        SizedBox(height: 16),
                        Text('Loading vaccination records...'),
                      ],
                    ),
                  )
                : errorMessage.isNotEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.error_outline,
                              size: 64,
                              color: Colors.red[300],
                            ),
                            const SizedBox(height: 16),
                            Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 32),
                              child: Text(
                                errorMessage,
                                style: const TextStyle(fontSize: 16),
                                textAlign: TextAlign.center,
                              ),
                            ),
                            const SizedBox(height: 16),
                            ElevatedButton.icon(
                              onPressed: _refreshData,
                              icon: const Icon(Icons.refresh),
                              label: const Text('Retry'),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.green[600],
                                foregroundColor: Colors.white,
                              ),
                            ),
                          ],
                        ),
                      )
                    : filteredRecords.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  _searchController.text.isNotEmpty || _selectedFilter != 'All'
                                    ? Icons.search_off
                                    : Icons.vaccines_outlined,
                                  size: 64,
                                  color: Colors.grey[400],
                                ),
                                const SizedBox(height: 16),
                                Text(
                                  _searchController.text.isNotEmpty || _selectedFilter != 'All'
                                    ? 'No records match your search'
                                    : 'No vaccination records found',
                                  style: const TextStyle(
                                    fontSize: 18,
                                    color: Colors.grey,
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  _searchController.text.isNotEmpty || _selectedFilter != 'All'
                                    ? 'Try adjusting your search or filters'
                                    : 'Vaccination records will appear here once available',
                                  style: const TextStyle(
                                    color: Colors.grey,
                                    fontSize: 14,
                                  ),
                                  textAlign: TextAlign.center,
                                ),
                                if (_searchController.text.isNotEmpty || _selectedFilter != 'All') ...[
                                  const SizedBox(height: 16),
                                  ElevatedButton.icon(
                                    onPressed: () {
                                      _searchController.clear();
                                      setState(() {
                                        _selectedFilter = 'All';
                                        _showSearchBar = false;
                                      });
                                      _applyFilters();
                                    },
                                    icon: const Icon(Icons.clear),
                                    label: const Text('Clear Filters'),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: Colors.green[600],
                                      foregroundColor: Colors.white,
                                    ),
                                  ),
                                ],
                              ],
                            ),
                          )
                        : RefreshIndicator(
                            onRefresh: _refreshData,
                            child: ListView.builder(
                              padding: userRole == Role.veterinarian && userRole == Role.staff
                                  ? const EdgeInsets.all(16)
                                  : const EdgeInsets.symmetric(horizontal: 16),
                              itemCount: filteredRecords.length,
                              itemBuilder: (context, index) {
                                final record = filteredRecords[index];
                                return VaccinationCard(
                                  record: record,
                                  searchQuery: _searchController.text,
                                );
                              },
                            ),
                          ),
          ),
        ],
      ),
    );

    if (userRole != Role.veterinarian && userRole != Role.staff) {
      return Scaffold(
        appBar: AppBar(
          leading: IconButton(
            onPressed: () => Navigator.pop(context),
            icon: Config.backButtonIcon,
          ),
          backgroundColor: Colors.green[600],
          foregroundColor: Colors.white,
          elevation: 0,
        ),
        body: mainBody,
      );
    } else {
      return mainBody;
    }
  }
}

class VaccinationCard extends StatelessWidget {
  final dynamic record;
  final String searchQuery;

  const VaccinationCard({
    super.key, 
    required this.record,
    this.searchQuery = '',
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(12),
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Colors.white,
              Colors.green[50]!,
            ],
          ),
        ),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header Row
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.green[100],
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(
                      Icons.vaccines,
                      color: Colors.green[700],
                      size: 24,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildHighlightedText(
                          record['vaccine_name'] ?? 'Unknown Vaccine',
                          searchQuery,
                          const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: Colors.black87,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Dose ${record['dose'] ?? 'N/A'}',
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.green[600],
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.blue[100],
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      _formatDate(record['date_given']),
                      style: TextStyle(
                        color: Colors.blue[700],
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              
              // Animal Info
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.grey[50],
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.grey[200]!),
                ),
                child: Column(
                  children: [
                    _buildInfoRow(
                      icon: Icons.pets,
                      label: 'Animal',
                      value: record['animal_name'] ?? 'Unknown',
                      iconColor: Colors.orange[600]!,
                      searchQuery: searchQuery,
                    ),
                    const SizedBox(height: 8),
                    _buildInfoRow(
                      icon: Icons.category,
                      label: 'Type',
                      value: '${record['animal_type'] ?? 'Unknown'} ${record['animal_breed'] != null ? '(${record['animal_breed']})' : ''}',
                      iconColor: Colors.purple[600]!,
                      searchQuery: searchQuery,
                    ),
                    const SizedBox(height: 8),
                    _buildInfoRow(
                      icon: Icons.person,
                      label: 'Owner',
                      value: record['owner_full_name'] ?? record['owner_name'] ?? 'Unknown Owner',
                      iconColor: Colors.indigo[600]!,
                      searchQuery: searchQuery,
                    ),
                    if (record['owner_phone'] != null) ...[
                      const SizedBox(height: 8),
                      _buildInfoRow(
                        icon: Icons.phone,
                        label: 'Phone',
                        value: record['owner_phone'],
                        iconColor: Colors.green[600]!,
                        searchQuery: searchQuery,
                      ),
                    ],
                    if (record['administrator'] != null) ...[
                      const SizedBox(height: 8),
                      _buildInfoRow(
                        icon: Icons.medical_services,
                        label: 'Administrator',
                        value: record['administrator'],
                        iconColor: Colors.red[600]!,
                        searchQuery: searchQuery,
                      ),
                    ],
                  ],
                ),
              ),
              
              // Vaccine Details (if available)
              if (record['vaccine_description'] != null || 
                  record['protect_against'] != null ||
                  record['affected'] != null) ...[
                const SizedBox(height: 12),
                ExpansionTile(
                  title: const Text(
                    'Vaccine Details',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  children: [
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (record['vaccine_description'] != null) ...[
                            Text(
                              'Description:',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                color: Colors.grey[700],
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(record['vaccine_description']),
                            const SizedBox(height: 8),
                          ],
                          if (record['protect_against'] != null) ...[
                            Text(
                              'Protects Against:',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                color: Colors.grey[700],
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(record['protect_against']),
                            const SizedBox(height: 8),
                          ],
                          if (record['affected'] != null) ...[
                            Text(
                              'Affected:',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                color: Colors.grey[700],
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(record['affected']),
                          ],
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoRow({
    required IconData icon,
    required String label,
    required String value,
    required Color iconColor,
    String searchQuery = '',
  }) {
    return Row(
      children: [
        Icon(icon, size: 16, color: iconColor),
        const SizedBox(width: 8),
        Text(
          '$label: ',
          style: const TextStyle(
            fontWeight: FontWeight.w500,
            fontSize: 13,
          ),
        ),
        Expanded(
          child: _buildHighlightedText(
            value,
            searchQuery,
            const TextStyle(fontSize: 13),
          ),
        ),
      ],
    );
  }

  Widget _buildHighlightedText(String text, String searchQuery, TextStyle style) {
    if (searchQuery.isEmpty || !text.toLowerCase().contains(searchQuery.toLowerCase())) {
      return Text(text, style: style, overflow: TextOverflow.ellipsis);
    }

    final lowerText = text.toLowerCase();
    final lowerQuery = searchQuery.toLowerCase();
    final index = lowerText.indexOf(lowerQuery);
    
    if (index == -1) {
      return Text(text, style: style, overflow: TextOverflow.ellipsis);
    }

    return RichText(
      overflow: TextOverflow.ellipsis,
      text: TextSpan(
        style: style,
        children: [
          TextSpan(text: text.substring(0, index)),
          TextSpan(
            text: text.substring(index, index + searchQuery.length),
            style: style.copyWith(
              backgroundColor: Colors.yellow[200],
              fontWeight: FontWeight.bold,
            ),
          ),
          TextSpan(text: text.substring(index + searchQuery.length)),
        ],
      ),
    );
  }

  String _formatDate(String? dateString) {
    if (dateString == null) return 'No Date';
    try {
      final date = DateTime.parse(dateString);
      return '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}';
    } catch (e) {
      return dateString;
    }
  }
}