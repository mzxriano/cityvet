import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

class QrScannerPage extends StatefulWidget {
  const QrScannerPage({super.key});

  @override
  State<QrScannerPage> createState() => _QrScannerPageState();
}

class _QrScannerPageState extends State<QrScannerPage> {
  late MobileScannerController controller = MobileScannerController();
  AnimalModel? animal;

  bool isScanning = true;
  bool isLoading = false;

  Future<void> fetchData(String code) async {
    final token = await AuthStorage().getToken();

    if (token == null) return;

    setState(() => isLoading = true);

    try {
      final response = await Dio(BaseOptions(
        baseUrl: 'http://192.168.1.109:8000/api/auth',
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      )).get('/animals/$code');

      setState(() {
        animal = AnimalModel.fromJson(response.data['data']);
      });

      if (context.mounted) {
        showDialog(
          context: context,
          builder: (_) => AlertDialog(
            title: Text("Animal Found"),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text("Name: ${animal!.name}"),
                Text("Type: ${animal!.type}"),
                Text("Breed: ${animal!.breed ?? 'N/A'}"),
                Text("Color: ${animal!.color}"),
                Text("Gender: ${animal!.gender}"),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pop(context); // Close dialog
                  Navigator.pop(context); // Go back
                },
                child: const Text("Close"),
              )
            ],
          ),
        );
      }
    } on DioException catch (e) {
      final exception = e.response?.data;
      print(exception);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(DioExceptionHandler.handleException(e))),
      );
    } catch (e) {
      print('Unexpected error: $e');
    } finally {
      setState(() {
        isLoading = false;
      });
    }
  }


  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          MobileScanner(
            controller: controller,
            onDetect: (capture) async {
              final barcode = capture.barcodes.first;
              final String? code = barcode.rawValue;

              if (code != null && isScanning) {
                setState(() => isScanning = false);

                final uri = Uri.tryParse(code);

                if (uri != null && uri.pathSegments.isNotEmpty) {
                  final code = uri.pathSegments.last;

                  print("Scanned code: $code");

                  await fetchData(code);

                  print('Show by qr code: $animal');
                } else {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text("Invalid QR code")),
                  );
                  setState(() => isScanning = true); // allow retry
                }
              }
              setState(() => isScanning = true);
            },
          ),

          _buildOverlay(),

          /// 🔦 Flash toggle button
          Positioned(
            bottom: 40,
            left: MediaQuery.of(context).size.width * 0.4,
            child: IconButton(
              icon: const Icon(Icons.flash_on, color: Colors.white, size: 32),
              onPressed: () => controller.toggleTorch(),
            ),
          ),

          if(isLoading)
            Center(
              child: CircularProgressIndicator(),
            )
        ],
      ),
    );
  }

  Widget _buildOverlay() {
    return IgnorePointer(
      child: Center(
        child: Container(
          width: 250,
          height: 250,
          decoration: BoxDecoration(
            border: Border.all(color: Colors.greenAccent, width: 3),
            borderRadius: BorderRadius.circular(16),
          ),
        ),
      ),
    );
  }
}
