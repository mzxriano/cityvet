import 'package:cityvet_app/utils/api_constant.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';

class VaccinationHistoryView extends StatefulWidget {
  const VaccinationHistoryView({super.key});

  @override
  State<VaccinationHistoryView> createState() => _VaccinationHistoryViewState();
}

class _VaccinationHistoryViewState extends State<VaccinationHistoryView> {
  List<dynamic> vaccinationRecords = [];
  bool isLoading = true;
  String errorMessage = '';
  final Dio _dio = Dio();
  bool _isDisposed = false;

  @override
  void initState() {
    super.initState();
    _initializeAndFetch();
  }

  @override
  void dispose() {
    _isDisposed = true;
    _dio.close(); // Clean up Dio instance
    super.dispose();
  }

  // Combined initialization and fetch to avoid timing issues
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
    _dio.options.connectTimeout = const Duration(seconds: 15); // Increased timeout
    _dio.options.receiveTimeout = const Duration(seconds: 15); // Increased timeout
    _dio.options.sendTimeout = const Duration(seconds: 15); // Added send timeout

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
    if (_isDisposed || !mounted) return; // Check if widget is still active

    try {
      if (mounted) {
        setState(() {
          isLoading = true;
          errorMessage = '';
        });
      }

      final response = await _dio.get('/vaccination-records');
      
      // Check if widget is still mounted before updating state
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

  // Wrapper for refresh that ensures proper initialization
  Future<void> _refreshData() async {
    if (_isDisposed || !mounted) return;
    
    try {
      // Re-setup Dio in case token changed
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Vaccination History'),
        backgroundColor: Colors.green[600],
        foregroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          onPressed: () => Navigator.pop(context), 
          icon: Config.backButtonIcon
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: isLoading ? null : _refreshData, // Disable during loading
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              Colors.green[50]!,
              Colors.white,
            ],
          ),
        ),
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
                : vaccinationRecords.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.vaccines_outlined,
                              size: 64,
                              color: Colors.grey[400],
                            ),
                            const SizedBox(height: 16),
                            const Text(
                              'No vaccination records found',
                              style: TextStyle(
                                fontSize: 18,
                                color: Colors.grey,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            const SizedBox(height: 8),
                            const Text(
                              'Vaccination records will appear here once available',
                              style: TextStyle(
                                color: Colors.grey,
                                fontSize: 14,
                              ),
                              textAlign: TextAlign.center,
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _refreshData,
                        child: ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: vaccinationRecords.length,
                          itemBuilder: (context, index) {
                            final record = vaccinationRecords[index];
                            return VaccinationCard(record: record);
                          },
                        ),
                      ),
      ),
    );
  }
}

class VaccinationCard extends StatelessWidget {
  final dynamic record;

  const VaccinationCard({super.key, required this.record});

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
                        Text(
                          record['vaccine_name'] ?? 'Unknown Vaccine',
                          style: const TextStyle(
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
                    ),
                    const SizedBox(height: 8),
                    _buildInfoRow(
                      icon: Icons.category,
                      label: 'Type',
                      value: '${record['animal_type'] ?? 'Unknown'} ${record['animal_breed'] != null ? '(${record['animal_breed']})' : ''}',
                      iconColor: Colors.purple[600]!,
                    ),
                    const SizedBox(height: 8),
                    _buildInfoRow(
                      icon: Icons.person,
                      label: 'Owner',
                      value: record['owner_full_name'] ?? record['owner_name'] ?? 'Unknown Owner',
                      iconColor: Colors.indigo[600]!,
                    ),
                    if (record['owner_phone'] != null) ...[
                      const SizedBox(height: 8),
                      _buildInfoRow(
                        icon: Icons.phone,
                        label: 'Phone',
                        value: record['owner_phone'],
                        iconColor: Colors.green[600]!,
                      ),
                    ],
                    if (record['administrator'] != null) ...[
                      const SizedBox(height: 8),
                      _buildInfoRow(
                        icon: Icons.medical_services,
                        label: 'Administrator',
                        value: record['administrator'],
                        iconColor: Colors.red[600]!,
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
          child: Text(
            value,
            style: const TextStyle(fontSize: 13),
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
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