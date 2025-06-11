import 'package:cityvet_app/components/card.dart';
import 'package:cityvet_app/components/role.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class CardVeterinarian extends StatefulWidget {
  const CardVeterinarian({
    super.key,
    required this.vetName,
    required this.vetEmail,
    required this.vetPhone,
    required this.vetImageUrl,
    });

  final String vetName;
  final String vetEmail;
  final String vetPhone;
  final String vetImageUrl;

  @override
  State<CardVeterinarian> createState() => _CardVeterinarianState();
}

class _CardVeterinarianState extends State<CardVeterinarian> {

  final roleWidget = RoleWidget();

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    return CustomCard(
      width: double.infinity,
      color: Colors.white,
      widget: Row(
        children: [
          CircleAvatar(
            radius: 35,
            backgroundColor: Colors.grey[200],
            child: Image.asset(widget.vetImageUrl, width: 35, height: 35,),
          ),
          const SizedBox(width: 25.0),

          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Name
                Text(
                  widget.vetName,
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                  ),
                ),

                roleWidget['Veterinarian'],

                const SizedBox(height: 10),

                Row(
                  children: [
                    const Icon(Icons.email, size: 18, color: Config.tertiaryColor),
                    const SizedBox(width: 5),
                    Flexible(
                      child: Text(
                        widget.vetEmail,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontXS,
                          color: Config.tertiaryColor,
                          decoration: TextDecoration.underline,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 5),

                Row(
                  children: [
                    const Icon(Icons.phone, size: 18, color: Config.tertiaryColor),
                    const SizedBox(width: 5),
                    Text(
                      widget.vetPhone,
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontXS,
                        color: Config.tertiaryColor,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
