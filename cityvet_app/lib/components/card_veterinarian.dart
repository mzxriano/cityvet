import 'package:cityvet_app/components/card.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';

class CardVeterinarian extends StatefulWidget {
  const CardVeterinarian({super.key});

  @override
  State<CardVeterinarian> createState() => _CardVeterinarianState();
}

class _CardVeterinarianState extends State<CardVeterinarian> {
  @override
  Widget build(BuildContext context) {
    Config().init(context); // Ensure your Config utility is initialized

    return CustomCard(
      width: double.infinity,
      color: Colors.white,
      widget: Row(
        children: [
          CircleAvatar(
            radius: 40,
            backgroundColor: Colors.grey[200],
            child: Image.asset('assets/images/default_avatar.png', width: 40, height: 40,),
          ),
          const SizedBox(width: 25.0),

          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Name
                Text(
                  'Dr. Sarah Cruz',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontBig,
                  ),
                ),
                const SizedBox(height: 10),

                Row(
                  children: [
                    const Icon(Icons.email, size: 18, color: Config.tertiaryColor),
                    const SizedBox(width: 5),
                    Flexible(
                      child: Text(
                        'cruz123@gmail.com',
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
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
                      '+639152623657',
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontSmall,
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
