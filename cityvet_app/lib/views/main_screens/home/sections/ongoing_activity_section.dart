import 'package:cityvet_app/components/card.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/home_view_model.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class OngoingActivitySection extends StatelessWidget {
  final HomeViewModel homeViewModel;
  final VoidCallback onSeeRecent;
  final Function(dynamic) onActivityTap;

  const OngoingActivitySection({
    super.key,
    required this.homeViewModel,
    required this.onSeeRecent,
    required this.onActivityTap,
  });

  @override
  Widget build(BuildContext context) {
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
              onPressed: onSeeRecent,
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
        else if (homeViewModel.ongoingActivities != null && 
                 homeViewModel.ongoingActivities!.isNotEmpty)
          GestureDetector(
            onTap: () => onActivityTap(homeViewModel.ongoingActivities!.first),
            child: CustomCard(
              width: double.infinity,
              color: Colors.white,
              widget: Builder(
                builder: (context) {
                  final activity = homeViewModel.ongoingActivities!.first; 
                  final barangayName = activity.barangays.isNotEmpty 
                      ? activity.barangays.first.name.toString() 
                      : 'N/A';
                  return Column(
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
                          const Icon(
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
                        barangayName,
                        style: const TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
                          color: Config.tertiaryColor,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        DateFormat('MMM d, yyyy • h:mm a')
                            .format(homeViewModel.ongoingActivities!.first.date),
                        style: const TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
                          color: Config.tertiaryColor,
                        ),
                      ),
                    ],
                  );
                }
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
}
