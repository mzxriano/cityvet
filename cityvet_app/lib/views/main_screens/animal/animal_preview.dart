import 'package:cityvet_app/components/anima_type.dart';
import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/animal_preview_view_model.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class AnimalPreview extends StatefulWidget {
  final AnimalModel animalModel;
  const AnimalPreview({super.key, required this.animalModel});

  @override
  State<AnimalPreview> createState() => _AnimalPreviewState();
}

class _AnimalPreviewState extends State<AnimalPreview> {

  AnimalModel? myAnimal;

  final _boxShadow = BoxShadow(
      color: Color.fromRGBO(0, 0, 0, 0.25),
      blurRadius: 5,
      spreadRadius: 0,
      offset: Offset(0, 0),
  );

  @override
  void initState() {
    myAnimal = widget.animalModel;
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    final double getScreenHeight = MediaQuery.of(context).size.height;
    final double getScreenWidth = MediaQuery.of(context).size.width;
    final double imageHeight = getScreenHeight * 0.55;

    final double screenWidth = getScreenWidth * 0.06;

    var animalType = AnimalTypeWidget();

    bool isBdateNull = myAnimal!.birthDate == null;

    Config().init(context);
    return ChangeNotifierProvider<AnimalPreviewViewModel>(
      create: (_) => AnimalPreviewViewModel(),
      child: Consumer<AnimalPreviewViewModel>(
        builder: (context, ref, child) {
          if (myAnimal?.birthDate != null) {
            ref.calculateAge(myAnimal!.birthDate!);
          }

          return Scaffold(
            extendBodyBehindAppBar: true,
            appBar: AppBar(
              backgroundColor: Colors.transparent,
              elevation: 0,
              leading: IconButton(
                onPressed: () {
                  Navigator.pop(context);
                },
                icon: Config.backButtonIcon, color: Colors.white,
              ),
              actions: [
                IconButton(
                  onPressed: () {}, 
                  padding: EdgeInsets.only(right: 20.0),
                  icon: const Icon(Icons.edit_square, color: Colors.white,),
                ),
              ],
            ),
            body: Stack(
              children: [
                // Animal image
                SizedBox(
                  height: imageHeight,
                  width: double.infinity,
                  child: FittedBox(
                    fit: BoxFit.cover,
                    alignment: Alignment.center,
                    child: Image.asset('assets/images/sample_dog.png'),
                  ),
                ),

                // Bottom main content
                Column(
                  children: [
                    SizedBox(height: imageHeight - 20),
                    Expanded(
                      child: SingleChildScrollView(
                        child: Container(
                          width: double.infinity,
                          padding: EdgeInsets.symmetric(horizontal: screenWidth, vertical: 90.0),
                          decoration: const BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.vertical(
                              top: Radius.circular(24),
                            ),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Center(
                                child: SizedBox(
                                  child: SingleChildScrollView(
                                    scrollDirection: Axis.horizontal,
                                    child: Padding(
                                      padding: const EdgeInsets.all(10.0),
                                      child: Row(
                                        children: [
                                          _buildAttributeBox('Type', myAnimal!.type),
                                          const SizedBox(width: 15,),
                                          _buildAttributeBox('Gender', myAnimal!.gender),
                                          const SizedBox(width: 15,),
                                          _buildAttributeBox('Weight', myAnimal?.weight?.toString() ?? 'No specified weight'),
                                          const SizedBox(width: 15,),
                                          _buildAttributeBox('Height', myAnimal?.height?.toString() ?? 'No specified height'),
                                          const SizedBox(width: 15,),
                                          _buildAttributeBox('Color', myAnimal!.color),
                                        ],
                                      ),
                                    ),
                                  ),
                                ),
                              ),
                              Config.heightMedium,
                              Container(
                                width: double.infinity,
                                padding: EdgeInsets.symmetric(vertical: 15, horizontal: 25),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(15),
                                  boxShadow: [
                                    _boxShadow,
                                  ]
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Text(
                                      'Juan Dela Cruz',
                                      style: TextStyle(
                                        fontFamily: Config.primaryFont,
                                        fontSize: Config.fontMedium,
                                        color: Color(0xFF524F4F),
                                      ),
                                    ),
                                    Config.heightSmall,
                                    Text(
                                      'Owner',
                                      style: TextStyle(
                                        fontFamily: Config.primaryFont,
                                        fontSize: Config.fontSmall,
                                        color: Config.tertiaryColor,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              Config.heightMedium,

                              // Register record widget
                              _buildRecordButton('Register Record', (){}),
                              Config.heightMedium,

                              // Vaccination history widget
                              _buildRecordButton('Vaccination History', (){}),
                              Config.heightBig,

                              Center(
                                child: Container(
                                  width: 180,
                                  height: 180,
                                  padding: EdgeInsets.all(10),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(15),
                                    boxShadow: [
                                      _boxShadow,
                                    ]
                                  ),
                                  child: Image.asset('assets/images/qr_code.png', fit: BoxFit.cover,),
                                ),
                              )

                              // Qr code
                            ],
                          ),
                        ),
                      ),
                    )
                  ],
                ),

                // Top card
                Positioned(
                  top: imageHeight - 85, 
                  left: screenWidth,
                  right: screenWidth,
                  child: Container(
                    padding: const EdgeInsets.all(15.0),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.25, red: 0, green: 0, blue: 0),
                          blurRadius: 4,
                          spreadRadius: 0,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              myAnimal!.name,
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontMedium,
                                fontWeight: Config.fontW600
                              ),
                            ),
                            const SizedBox(height: 5,),
                            Text(
                              (myAnimal!.breed!),
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontSmall,
                                color: Config.tertiaryColor
                              ),
                            ),
                            const SizedBox(height: 5,),
                            Text(
                              ref.ageString ?? 'No specified birthdate',
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: isBdateNull ? Config.fontXS - 1 : Config.fontSmall,
                                fontStyle: isBdateNull ? FontStyle.italic : FontStyle.normal,
                                color: isBdateNull ? Colors.grey : Config.tertiaryColor,
                              ),
                            ),
                            const SizedBox(height: 5,),
                            animalType['Pet'],
                          ],
                        ),
                        AnimalGenderWidget()[myAnimal!.gender],
                      ],
                    ),
                  ),
                ),
              ],
            ),
          );
        }
      ),
    );
  }

  // Attr box
Widget _buildAttributeBox(String title, String value) {
  final bool isPlaceholder = value.toLowerCase().contains('no specified');

  return Container(
    width: 100,
    height: 100,
    padding: const EdgeInsets.all(5),
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(12),
      boxShadow: [
        _boxShadow,
      ],
    ),
    child: Center(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        mainAxisAlignment: MainAxisAlignment.center,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            title,
            style: TextStyle(
              fontSize: Config.fontSmall,
              color: Config.tertiaryColor,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: TextStyle(
              fontSize: isPlaceholder ? Config.fontXS - 1 : Config.fontSmall,
              fontWeight: isPlaceholder ? FontWeight.normal : Config.fontW600,
              fontStyle: isPlaceholder ? FontStyle.italic : FontStyle.normal,
              color: isPlaceholder ? Colors.grey : Config.color524F4F,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    ),
  );
}


  Widget _buildRecordButton(String title, GestureTapCallback onTap) {
    return InkWell(
      onTap: onTap,
      child: Container(
        width: double.infinity,
        padding: EdgeInsets.symmetric(vertical: 15, horizontal: 25),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(15),
          boxShadow: [
            _boxShadow,
          ]
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              title,
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontMedium,
                color: Config.color524F4F,
              ),
            ),
            Icon(Icons.arrow_forward_ios_rounded, color: Config.color524F4F,),
          ],
        ),
      ),
    );
  }
}

// Gender widget
class AnimalGenderWidget {

  Widget operator [](String gender) {
    final Map<String, Color> genderColors = {
      'Male': Color(0xFF334EAC),
      'Female': Color(0xFFDFA6A1),
    };

    final color = genderColors[gender] ?? Colors.grey;

    return Container(
      padding: const EdgeInsets.all(5),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(10),
      ),
      child: Center(
        child: Icon((gender == 'Male') ? Icons.male : Icons.female, size: 50, color: Colors.white,)
      ),
    );
  }
}


 


