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
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        elevation: 0,
        backgroundColor: Colors.white,
        leading: IconButton(
          onPressed: () => Navigator.pop(context),
          icon: Config.backButtonIcon,
        ),
        title: Text(
          'Vaccination Report',
          style: TextStyle(
            color: Config.tertiaryColor,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
        actions: [
          if (widget.activityId != null)
            IconButton(
              onPressed: () => _showVaccinateAnimalDialog(context),
              icon: Icon(Icons.qr_code, color: Config.tertiaryColor),
              tooltip: 'Vaccinate Animal',
            ),
        ],
      ),
      body: isLoading
          ? Center(child: CircularProgressIndicator())
          : errorMessage != null
              ? Center(child: Text(errorMessage!))
              : report == null
                  ? Center(child: Text('No data available'))
                  : SingleChildScrollView(
                      padding: EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Card(
                            child: Padding(
                              padding: EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Date: ${report!['date']}',
                                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                                  ),
                                  SizedBox(height: 8),
                                  Text(
                                    'Total Vaccinated: ${report!['total_vaccinated_animals']}',
                                    style: TextStyle(fontSize: 16),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          SizedBox(height: 16),
                          Text(
                            'Vaccinated Animals',
                            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                          ),
                          SizedBox(height: 12),
                          ...(report!['vaccinated_animals'] as List).map((animal) => 
                            Card(
                              margin: EdgeInsets.only(bottom: 8),
                              child: ListTile(
                                title: Text(animal['name']),
                                subtitle: Text('${animal['type']} • ${animal['owner']}'),
                                trailing: Text('${animal['vaccinations'].length} vaccines'),
                              ),
                            )
                          ).toList(),
                        ],
                      ),
                    ),
    );
  }

  void _showVaccinateAnimalDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Vaccinate Animal'),
        content: Text('Scan an animal\'s QR code to vaccinate it during this activity.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _navigateToVaccinationPage(context);
            },
            child: Text('Scan QR Code'),
          ),
        ],
      ),
    );
  }

  void _navigateToVaccinationPage(BuildContext context) {
    // Navigate directly to QR scanner with activity context
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => QrScannerPage(
          activityId: int.tryParse(widget.activityId ?? ''),
        ),
      ),
    );
  }
} 