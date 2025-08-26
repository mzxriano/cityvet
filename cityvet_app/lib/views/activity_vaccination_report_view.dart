import 'package:cityvet_app/services/api_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/components/qr_scanner.dart';
import 'package:flutter/material.dart';

class ActivityVaccinationReportView extends StatefulWidget {
  final String? activityId;
  final String? date;

  const ActivityVaccinationReportView({
    super.key,
    this.activityId,
    this.date,
  });

  @override
  State<ActivityVaccinationReportView> createState() => _ActivityVaccinationReportViewState();
}

class _ActivityVaccinationReportViewState extends State<ActivityVaccinationReportView> {
  bool isLoading = false;
  Map<String, dynamic>? report;
  String? errorMessage;

  @override
  void initState() {
    super.initState();
    _loadVaccinationReport();
  }

  Future<void> _loadVaccinationReport() async {
    setState(() {
      isLoading = true;
      errorMessage = null;
    });

    try {
      final token = await AuthStorage().getToken();
      if (token == null) {
        setState(() {
          errorMessage = 'Authentication required';
          isLoading = false;
        });
        return;
      }

      final api = ApiService();
      Map<String, dynamic> data;

      if (widget.activityId != null) {
        data = await api.getVaccinatedAnimalsByActivity(token, int.parse(widget.activityId!));
      } else {
        data = await api.getVaccinatedAnimals(token, widget.date ?? '2025-01-01');
      }

      setState(() {
        report = data;
        isLoading = false;
      });
    } catch (e) {
      setState(() {
        errorMessage = 'Failed to load vaccination report: $e';
        isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        elevation: 0,
        backgroundColor: Colors.white,
        leading: IconButton(
          onPressed: () => Navigator.pop(context),
          icon: const Icon(Icons.arrow_back, color: Colors.black),
        ),
        title: const Text(
          'Vaccination Report',
          style: TextStyle(
            color: Colors.black,
            fontWeight: FontWeight.w500,
            fontSize: 18,
          ),
        ),
        centerTitle: false,
        actions: [
          if (widget.activityId != null)
            TextButton.icon(
              onPressed: () => _showVaccinateAnimalDialog(context),
              icon: const Icon(Icons.qr_code_scanner, size: 20),
              label: const Text('Scan'),
              style: TextButton.styleFrom(
                foregroundColor: Colors.blue,
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              ),
            ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadVaccinationReport,
        child: isLoading
            ? const Center(
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  color: Colors.blue,
                ),
              )
            : errorMessage != null
                ? _buildErrorState()
                : report == null
                    ? _buildEmptyState()
                    : _buildReportContent(),
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.error_outline,
              size: 48,
              color: Colors.grey,
            ),
            const SizedBox(height: 16),
            Text(
              'Unable to load report',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w500,
                color: Colors.grey[700],
              ),
            ),
            const SizedBox(height: 8),
            Text(
              errorMessage!,
              textAlign: TextAlign.center,
              style: TextStyle(
                color: Colors.grey[600],
                fontSize: 14,
              ),
            ),
            const SizedBox(height: 16),
            TextButton(
              onPressed: _loadVaccinationReport,
              child: const Text('Try again'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return const Center(
      child: Padding(
        padding: EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.pets_outlined,
              size: 48,
              color: Colors.grey,
            ),
            SizedBox(height: 16),
            Text(
              'No vaccination data',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w500,
                color: Colors.grey,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildReportContent() {
    final totalVaccinated = report!['total_vaccinated_animals'] ?? 0;
    final vaccinatedAnimals = report!['vaccinated_animals'] as List? ?? [];

    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Summary Section
          Container(
            width: double.infinity,
            margin: const EdgeInsets.all(16),
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.blue.shade50,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.blue.shade100),
            ),
            child: Column(
              children: [
                Text(
                  '$totalVaccinated',
                  style: const TextStyle(
                    fontSize: 32,
                    fontWeight: FontWeight.w600,
                    color: Colors.blue,
                  ),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Animals Vaccinated',
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.black87,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),

          // Animals List Section
          if (vaccinatedAnimals.isNotEmpty) ...[
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Text(
                'Vaccinated Animals',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: Colors.grey[800],
                ),
              ),
            ),
            const SizedBox(height: 12),
            
            ListView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              padding: const EdgeInsets.symmetric(horizontal: 16),
              itemCount: vaccinatedAnimals.length,
              itemBuilder: (context, index) {
                final animal = vaccinatedAnimals[index];
                return Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.grey.shade200),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        animal['name'] ?? 'Unnamed Animal',
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                          color: Colors.black87,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Text(
                            animal['type'] ?? 'Unknown',
                            style: TextStyle(
                              fontSize: 13,
                              color: Colors.grey[600],
                            ),
                          ),
                          if (animal['owner'] != null) ...[
                            Text(
                              ' • ',
                              style: TextStyle(
                                color: Colors.grey[400],
                                fontSize: 13,
                              ),
                            ),
                            Expanded(
                              child: Text(
                                animal['owner'],
                                style: TextStyle(
                                  fontSize: 13,
                                  color: Colors.grey[600],
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ],
                      ),
                      if (animal['vaccinations'] != null) ...[
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.green.shade50,
                            borderRadius: BorderRadius.circular(4),
                            border: Border.all(color: Colors.green.shade200),
                          ),
                          child: Text(
                            '${animal['vaccinations'].length} vaccines',
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.green.shade700,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                );
              },
            ),
          ] else ...[
            const Padding(
              padding: EdgeInsets.all(24),
              child: Center(
                child: Text(
                  'No animals have been vaccinated yet',
                  style: TextStyle(
                    color: Colors.grey,
                    fontSize: 14,
                  ),
                ),
              ),
            ),
          ],

          const SizedBox(height: 24),
        ],
      ),
    );
  }

  void _showVaccinateAnimalDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text(
          'Vaccinate Animal',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
        content: const Text(
          'Scan an animal\'s QR code to record vaccination for this activity.',
          style: TextStyle(fontSize: 15),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () {
              Navigator.pop(context);
              _navigateToVaccinationPage(context);
            },
            child: const Text('Scan QR Code'),
          ),
        ],
      ),
    );
  }

  void _navigateToVaccinationPage(BuildContext context) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => QrScannerPage(
          activityId: int.tryParse(widget.activityId ?? ''),
        ),
      ),
    ).then((_) {
      _loadVaccinationReport();
    });
  }
}