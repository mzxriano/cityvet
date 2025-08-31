import 'dart:convert';
import 'dart:io';
import 'dart:typed_data';
import 'dart:ui' as ui;

import 'package:cityvet_app/components/anima_type.dart';
import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/animal_preview_view_model.dart';
import 'package:cityvet_app/viewmodels/animal_view_model.dart';
import 'package:cityvet_app/views/main_screens/animal/animal_edit.dart';
import 'package:cityvet_app/views/main_screens/animal/animal_vaccination_record_page.dart';
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:path_provider/path_provider.dart';
import 'package:gal/gal.dart';


class AnimalPreview extends StatefulWidget {
  final AnimalModel animalModel;
  const AnimalPreview({super.key, required this.animalModel});

  @override
  State<AnimalPreview> createState() => _AnimalPreviewState();
}

class _AnimalPreviewState extends State<AnimalPreview> {
  final GlobalKey _qrKey = GlobalKey();
  final GlobalKey _qrCardKey = GlobalKey(); 

  final _boxShadow = BoxShadow(
      color: Color.fromRGBO(0, 0, 0, 0.25),
      blurRadius: 5,
      spreadRadius: 0,
      offset: Offset(0, 0),
  );

  Future<void> _downloadQRWithDetails() async {
    try {
      print('Starting download process...');
      
      // Check if has access to gallery
      final hasAccess = await Gal.hasAccess();
      if (!hasAccess) {
        // Request access
        final granted = await Gal.requestAccess();
        if (!granted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Gallery permission is required to save images')),
          );
          return;
        }
      }

      // Capture the image
      RenderRepaintBoundary boundary = _qrCardKey.currentContext!.findRenderObject() as RenderRepaintBoundary;
      ui.Image image = await boundary.toImage(pixelRatio: 3.0);
      ByteData? byteData = await image.toByteData(format: ui.ImageByteFormat.png);
      Uint8List imageBytes = byteData!.buffer.asUint8List();

      // Save to temporary file first
      final tempDir = await getTemporaryDirectory();
      String timestamp = DateTime.now().millisecondsSinceEpoch.toString();
      String petName = widget.animalModel.name.replaceAll(' ', '_').replaceAll(RegExp(r'[^\w\s-]'), '');
      String fileName = '${petName}_QRCard_$timestamp.png';
      final file = File('${tempDir.path}/$fileName');
      await file.writeAsBytes(imageBytes);

      // Save to gallery using gal
      await Gal.putImage(file.path);

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('QR card saved to gallery successfully!'),
          backgroundColor: Colors.green,
          duration: Duration(seconds: 3),
        ),
      );

    } catch (e, stackTrace) {
      print('Error: $e');
      print('Stack trace: $stackTrace');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error saving to gallery: ${e.toString()}'),
          backgroundColor: Colors.red,
          duration: Duration(seconds: 3),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final animalViewModel = Provider.of<AnimalViewModel>(context);
    final myAnimal = animalViewModel.animals.firstWhere(
      (a) => a.id == widget.animalModel.id,
      orElse: () => widget.animalModel,
    );

    final double getScreenHeight = MediaQuery.of(context).size.height;
    final double getScreenWidth = MediaQuery.of(context).size.width;
    final double imageHeight = getScreenHeight * 0.55;

    final double screenWidth = getScreenWidth * 0.06;

    var animalType = AnimalTypeWidget();

    bool isBdateNull = myAnimal.birthDate == null;

    Config().init(context);
    return ChangeNotifierProvider<AnimalPreviewViewModel>(
      create: (_) => AnimalPreviewViewModel(),
      child: Consumer<AnimalViewModel>(
        builder: (context, ref, child) {

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
                  onPressed: () {
                    Navigator.push(context, MaterialPageRoute(builder: (_) => AnimalEdit(animalModel: myAnimal,)));
                  }, 
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
                    child: myAnimal.imageUrl != null ?
                      Image.network(myAnimal.imageUrl!) : 
                      Image.asset('assets/images/logo.png'),
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
                                          _buildAttributeBox('Type', myAnimal.type),
                                          const SizedBox(width: 15,),
                                          _buildAttributeBox('Gender', myAnimal.gender),
                                          const SizedBox(width: 15,),
                                          _buildAttributeBox('Weight', myAnimal.weight?.toString() ?? 'No specified weight'),
                                          const SizedBox(width: 15,),
                                          _buildAttributeBox('Height', myAnimal.height?.toString() ?? 'No specified height'),
                                          const SizedBox(width: 15,),
                                          _buildAttributeBox('Color', myAnimal.color),
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
                                      'Owner',
                                      style: TextStyle(
                                        fontFamily: Config.primaryFont,
                                        fontSize: Config.fontSmall,
                                        color: Config.tertiaryColor,
                                      ),
                                    ),
                                    Config.heightSmall,
                                    Text(
                                      myAnimal.owner ?? 'No owner',
                                      style: TextStyle(
                                        fontFamily: Config.primaryFont,
                                        fontSize: Config.fontMedium,
                                        color: Color(0xFF524F4F),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              Config.heightMedium,

                              // Vaccination record widget
                              _buildRecordButton('Vaccination Record', (){
                                print(myAnimal.vaccinations);
                                Navigator.push(context, MaterialPageRoute(builder: (_) => VaccinationRecord(animal: myAnimal)));
                              }),
                              Config.heightBig,

                              // QR Code Section with Note and Download
                              Center(
                                child: Column(
                                  children: [
                                    // Note about QR code
                                    Container(
                                      width: double.infinity,
                                      margin: EdgeInsets.only(bottom: 15),
                                      padding: EdgeInsets.symmetric(vertical: 12, horizontal: 20),
                                      decoration: BoxDecoration(
                                        color: Color(0xFFE3F2FD),
                                        borderRadius: BorderRadius.circular(12),
                                        border: Border.all(color: Color(0xFF2196F3), width: 1),
                                      ),
                                      child: Row(
                                        children: [
                                          Icon(Icons.info_outline, color: Color(0xFF1976D2), size: 20),
                                          SizedBox(width: 10),
                                          Expanded(
                                            child: Text(
                                              'This QR code is for vaccination purposes. Veterinarian or Staff will scan it to ensure a smooth and efficient vaccination process.',
                                              style: TextStyle(
                                                fontFamily: Config.primaryFont,
                                                fontSize: Config.fontSmall,
                                                color: Color(0xFF1976D2),
                                                fontWeight: FontWeight.w500,
                                              ),
                                              textAlign: TextAlign.start,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    
                                    // Simplified QR Card for Download (with RepaintBoundary)
                                    RepaintBoundary(
                                      key: _qrCardKey,
                                      child: Container(
                                        width: 280,
                                        padding: EdgeInsets.all(20),
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius: BorderRadius.circular(15),
                                          boxShadow: [
                                            BoxShadow(
                                              color: Colors.black.withOpacity(0.1),
                                              blurRadius: 10,
                                              spreadRadius: 2,
                                              offset: Offset(0, 2),
                                            ),
                                          ]
                                        ),
                                        child: Column(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            // Pet Name
                                            Text(
                                              myAnimal.name,
                                              style: TextStyle(
                                                fontFamily: Config.primaryFont,
                                                fontSize: 24,
                                                fontWeight: FontWeight.bold,
                                                color: Config.primaryColor ?? Color(0xFF2196F3),
                                              ),
                                              textAlign: TextAlign.center,
                                            ),
                                            SizedBox(height: 15),
                                            
                                            // QR Code
                                            Container(
                                              width: 180,
                                              height: 180,
                                              padding: EdgeInsets.all(8),
                                              decoration: BoxDecoration(
                                                color: Colors.white,
                                                borderRadius: BorderRadius.circular(10),
                                                border: Border.all(color: Colors.grey.shade300, width: 1),
                                              ),
                                              child: myAnimal.qrCode != null 
                                                ? Image.memory(
                                                    base64Decode(myAnimal.qrCode!),
                                                    fit: BoxFit.contain,
                                                  )
                                                : Icon(Icons.qr_code, size: 100, color: Colors.grey),
                                            ),
                                            
                                            SizedBox(height: 15),
                                            
                                            // Pet Code
                                            Container(
                                              padding: EdgeInsets.symmetric(horizontal: 15, vertical: 8),
                                              decoration: BoxDecoration(
                                                color: Color(0xFFF5F5F5),
                                                borderRadius: BorderRadius.circular(8),
                                                border: Border.all(color: Colors.grey.shade300),
                                              ),
                                              child: Text(
                                                myAnimal.code ?? 'No code',
                                                style: TextStyle(
                                                  fontFamily: Config.primaryFont,
                                                  fontSize: 16,
                                                  color: Config.color524F4F,
                                                  fontWeight: FontWeight.w600,
                                                  letterSpacing: 1.5,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                    
                                    const SizedBox(height: 20,),
                                    
                                    // Download button
                                    ElevatedButton.icon(
                                      onPressed: myAnimal.qrCode != null ? _downloadQRWithDetails : null,
                                      icon: Icon(Icons.download, size: 18),
                                      label: Text('Download QR Card'),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Config.primaryColor ?? Color(0xFF2196F3),
                                        foregroundColor: Colors.white,
                                        padding: EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                                        shape: RoundedRectangleBorder(
                                          borderRadius: BorderRadius.circular(10),
                                        ),
                                        elevation: 3,
                                      ),
                                    ),
                                  ],
                                ),
                              )
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
                              myAnimal.name,
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontBig,
                                fontWeight: Config.fontW600
                              ),
                            ),
                            const SizedBox(height: 5,),
                            Text(
                              (myAnimal.breed ?? 'Unknown Breed'),
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontMedium,
                                color: Config.tertiaryColor
                              ),
                            ),
                            const SizedBox(height: 5,),
                            Text(
                              myAnimal.ageString,
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: isBdateNull ? Config.fontSmall - 1 : Config.fontMedium,
                                fontStyle: isBdateNull ? FontStyle.italic : FontStyle.normal,
                                color: isBdateNull ? Colors.grey : Config.tertiaryColor,
                              ),
                            ),
                            const SizedBox(height: 5,),
                            animalType['Pet'],
                          ],
                        ),
                        AnimalGenderWidget()[myAnimal.gender],
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
              fontFamily: Config.primaryFont,
              color: Config.tertiaryColor,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: TextStyle(
              fontSize: isPlaceholder ? Config.fontXS - 1 : Config.fontSmall,
              fontFamily: Config.primaryFont,
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
                color: Config.tertiaryColor,
              ),
            ),
            Icon(Icons.arrow_forward_ios_rounded, color: Config.tertiaryColor,),
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
      'male': Color(0xFF334EAC),
      'female': Color(0xFFDFA6A1),
    };

    final color = genderColors[gender] ?? Colors.grey;

    return Container(
      padding: const EdgeInsets.all(5),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(10),
      ),
      child: Center(
        child: Icon((gender == 'male') ? Icons.male : Icons.female, size: 50, color: Colors.white,)
      ),
    );
  }
}