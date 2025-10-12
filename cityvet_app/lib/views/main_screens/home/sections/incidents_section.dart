import 'package:cityvet_app/models/incident_model.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/views/main_screens/home/widgets/incident_card.dart';
import 'package:flutter/material.dart';

class IncidentsSection extends StatelessWidget {
  final bool isLoading;
  final List<IncidentModel> incidents;
  final VoidCallback onRefresh;
  final Function(IncidentModel) onIncidentTap;

  const IncidentsSection({
    super.key,
    required this.isLoading,
    required this.incidents,
    required this.onRefresh,
    required this.onIncidentTap,
  });

  @override
  Widget build(BuildContext context) {
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
              onPressed: isLoading ? null : onRefresh,
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
        if (isLoading)
          const Center(child: CircularProgressIndicator())
        else if (incidents.isEmpty)
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
            children: incidents.take(3).map((incident) {
              return IncidentCard(
                incident: incident,
                onTap: () => onIncidentTap(incident),
              );
            }).toList(),
          ),
      ],
    );
  }
}
