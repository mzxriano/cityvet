import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:cityvet_app/views/vaccination_page_view.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

class QrScannerPage extends StatefulWidget {
  final int? activityId; 

  const QrScannerPage({
    super.key,
    this.activityId,
  });

  @override
  State<QrScannerPage> createState() => _QrScannerPageState();
}

class _QrScannerPageState extends State<QrScannerPage> with TickerProviderStateMixin {
  late MobileScannerController controller;
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;
  
  AnimalModel? animal;
  bool isScanning = true;
  bool isLoading = false;
  bool isTorchOn = false;
  bool showManualInput = false;
  bool _isDisposed = false;
  bool _isNavigating = false;
  
  final TextEditingController _codeController = TextEditingController();
  final FocusNode _codeFocusNode = FocusNode();

  @override
  void initState() {
    super.initState();
    _initializeControllers();
  }

  void _initializeControllers() {
    controller = MobileScannerController(
      detectionSpeed: DetectionSpeed.noDuplicates,
      returnImage: false,
    );
    
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );
    
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeInOut),
    );
    
    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, 1),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _animationController, curve: Curves.easeOutBack));
  }

  @override
  void dispose() {
    _isDisposed = true;
    _animationController.dispose();
    _codeController.dispose();
    _codeFocusNode.dispose();
    _safelyDisposeController();
    super.dispose();
  }

  void _safelyDisposeController() {
    try {
      controller.dispose();
    } catch (e) {
      // Silently handle any disposal errors
      print('Controller disposal error: $e');
    }
  }

  Future<void> _safelyStopController() async {
    if (_isDisposed) return;
    
    try {
      await controller.stop();
    } catch (e) {
      print('Controller stop error: $e');
    }
  }

  Future<void> _safelyStartController() async {
    if (_isDisposed || showManualInput) return;
    
    try {
      await controller.start();
    } catch (e) {
      print('Controller start error: $e');
    }
  }

  Future<void> fetchData(String code) async {
    if (_isNavigating || _isDisposed) return;

    final token = await AuthStorage().getToken();
    if (token == null) return;

    if (!mounted) return;
    setState(() => isLoading = true);

    try {
      final response = await Dio(BaseOptions(
        baseUrl: ApiConstant.baseUrl,
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      )).get('/animals/$code');

      if (!mounted) return;

      setState(() {
        animal = AnimalModel.fromJson(response.data['data']);
      });

      // Stop camera before navigation
      await _safelyStopController();
      
      if (!mounted) return;

      _isNavigating = true;
      
      await Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => VaccinationPage(
            animalModel: animal!,
            activityId: widget.activityId, // Pass the activityId from QR scanner
          ),
        ),
      );

      // Handle return from navigation
      _isNavigating = false;
      if (mounted && !_isDisposed) {
        _resetToScanningMode();
      }
      
    } on DioException catch (e) {
      if (!mounted) return;
      final exception = e.response?.data;
      print(exception);
      _showErrorSnackBar(DioExceptionHandler.handleException(e));
    } catch (e) {
      if (!mounted) return;
      print('Unexpected error: $e');
      _showErrorSnackBar('An unexpected error occurred');
    } finally {
      if (mounted) {
        setState(() {
          isLoading = false;
        });
      }
    }
  }

  void _resetToScanningMode() {
    if (_isDisposed || !mounted) return;
    
    setState(() {
      showManualInput = false;
      isScanning = true;
      isLoading = false;
    });
    
    // Clear manual input
    _codeController.clear();
    _codeFocusNode.unfocus();
    
    // Reset animation
    _animationController.reverse();
    
    // Restart camera after a short delay
    Future.delayed(const Duration(milliseconds: 500), () {
      if (!_isDisposed && mounted && !showManualInput) {
        _safelyStartController();
      }
    });
  }

  void _showErrorSnackBar(String message) {
    if (!mounted) return;
    
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.error_outline, color: Colors.white),
            const SizedBox(width: 8),
            Expanded(child: Text(message)),
          ],
        ),
        backgroundColor: Colors.red,
        behavior: SnackBarBehavior.floating,
        margin: const EdgeInsets.all(16),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    );
  }

  void _showSuccessSnackBar(String message) {
    if (!mounted) return;
    
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle_outline, color: Colors.white),
            const SizedBox(width: 8),
            Expanded(child: Text(message)),
          ],
        ),
        backgroundColor: Colors.green,
        behavior: SnackBarBehavior.floating,
        margin: const EdgeInsets.all(16),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    );
  }

  void _toggleTorch() async {
    if (_isDisposed || showManualInput) return;
    
    try {
      await controller.toggleTorch();
      if (mounted) {
        setState(() {
          isTorchOn = !isTorchOn;
        });
      }
    } catch (e) {
      print('Torch toggle error: $e');
    }
  }

  void _toggleManualInput() {
    if (_isDisposed || !mounted) return;
    
    setState(() {
      showManualInput = !showManualInput;
    });
    
    if (showManualInput) {
      // Stop camera when switching to manual input
      _safelyStopController();
      _animationController.forward();
      Future.delayed(const Duration(milliseconds: 300), () {
        if (mounted && !_isDisposed) {
          _codeFocusNode.requestFocus();
        }
      });
    } else {
      // Start camera when switching back to scanning
      _animationController.reverse();
      _codeFocusNode.unfocus();
      _codeController.clear();
      setState(() {
        isScanning = true;
      });
      
      // Restart camera after animation
      Future.delayed(const Duration(milliseconds: 300), () {
        if (!_isDisposed && mounted) {
          _safelyStartController();
        }
      });
    }
  }

  void _submitManualCode() async {
    if (_isDisposed || !mounted) return;
    
    final code = _codeController.text.trim();
    
    if (code.isEmpty) {
      _showErrorSnackBar('Please enter a valid code');
      return;
    }

    // Provide haptic feedback
    HapticFeedback.lightImpact();
    
    // Hide keyboard
    _codeFocusNode.unfocus();
    
    await fetchData(code);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          // Camera view
          if (!showManualInput) ...[
            MobileScanner(
              controller: controller,
              onDetect: (capture) async {
                if (_isNavigating || _isDisposed || !mounted) return;
                
                final barcode = capture.barcodes.first;
                final String? code = barcode.rawValue;

                if (code != null && isScanning && !showManualInput) {
                  setState(() => isScanning = false);

                  // Provide haptic feedback
                  HapticFeedback.heavyImpact();

                  final uri = Uri.tryParse(code);

                  if (uri != null && uri.pathSegments.isNotEmpty) {
                    final extractedCode = uri.pathSegments.last;
                    print("Scanned code: $extractedCode");
                    _showSuccessSnackBar('QR Code detected!');
                    await fetchData(extractedCode);
                    print('Show by qr code: $animal');
                  } else {
                    _showErrorSnackBar("Invalid QR code format");
                    if (mounted) {
                      setState(() => isScanning = true);
                    }
                  }
                }
              },
            ),
            // Simple overlay
            _buildOverlay(),
          ],

          // Manual input background
          if (showManualInput) _buildManualInputBackground(),

          // Header
          _buildHeader(),

          // Scanning frame (only show when not in manual input mode)
          if (!showManualInput) _buildScanningFrame(),

          // Instructions
          _buildInstructions(),

          // Bottom controls
          _buildBottomControls(),

          // Manual input panel
          if (showManualInput) _buildManualInputPanel(),

          // Loading indicator
          if (isLoading) _buildLoadingIndicator(),
        ],
      ),
    );
  }

  Widget _buildOverlay() {
    return Container(
      color: const Color.fromRGBO(0, 0, 0, 0.1),
      child: Center(
        child: Container(
          width: 250,
          height: 250,
          decoration: BoxDecoration(
            color: Colors.transparent,
            borderRadius: BorderRadius.circular(12),
            boxShadow: const [
              BoxShadow(
                color: Color.fromRGBO(0, 0, 0, 0.4),
                spreadRadius: 1000,
                blurRadius: 0,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildManualInputBackground() {
    return Container(
      color: const Color.fromRGBO(0, 0, 0, 0.9),
      child: Center(
        child: Container(
          margin: const EdgeInsets.all(32),
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.grey[900],
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.grey[700]!),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                Icons.qr_code_2,
                color: Colors.grey[400],
                size: 64,
              ),
              const SizedBox(height: 16),
              Text(
                'Enter Code Manually',
                style: TextStyle(
                  color: Colors.grey[300],
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Type the animal code if you can\'t scan the QR code',
                style: TextStyle(
                  color: Colors.grey[500],
                  fontSize: 14,
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Row(
          children: [
            Expanded(
              child: Text(
                showManualInput ? 'Enter Code' : 'Scan QR Code',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 18,
                  fontWeight: FontWeight.w500,
                ),
                textAlign: TextAlign.center,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildScanningFrame() {
    return Center(
      child: Container(
        width: 250,
        height: 250,
        decoration: BoxDecoration(
          border: Border.all(color: Colors.white, width: 2),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Stack(
          children: [
            // Animated scanning line
            AnimatedBuilder(
              animation: _animationController,
              builder: (context, child) {
                return Positioned(
                  top: 20 + (210 * _animationController.value),
                  left: 20,
                  right: 20,
                  child: Container(
                    height: 2,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          Colors.transparent,
                          Colors.green.withOpacity(0.8),
                          Colors.transparent,
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
            // Corner brackets
            ...List.generate(4, (index) {
              final isTop = index < 2;
              final isLeft = index % 2 == 0;
              
              return Positioned(
                top: isTop ? 8 : null,
                bottom: isTop ? null : 8,
                left: isLeft ? 8 : null,
                right: isLeft ? null : 8,
                child: Container(
                  width: 20,
                  height: 20,
                  decoration: BoxDecoration(
                    border: Border(
                      top: isTop ? const BorderSide(color: Colors.green, width: 3) : BorderSide.none,
                      bottom: !isTop ? const BorderSide(color: Colors.green, width: 3) : BorderSide.none,
                      left: isLeft ? const BorderSide(color: Colors.green, width: 3) : BorderSide.none,
                      right: !isLeft ? const BorderSide(color: Colors.green, width: 3) : BorderSide.none,
                    ),
                  ),
                ),
              );
            }),
          ],
        ),
      ),
    );
  }

  Widget _buildInstructions() {
    return Positioned(
      bottom: 160,
      left: 0,
      right: 0,
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 32),
        child: Text(
          showManualInput 
            ? 'Enter the animal code in the field below'
            : 'Position QR code within the frame',
          style: const TextStyle(
            color: Colors.white,
            fontSize: 16,
          ),
          textAlign: TextAlign.center,
        ),
      ),
    );
  }

  Widget _buildBottomControls() {
    return Positioned(
      bottom: 60,
      left: 0,
      right: 0,
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
        children: [
          if (!showManualInput) ...[
            _buildControlButton(
              icon: isTorchOn ? Icons.flash_on : Icons.flash_off,
              label: 'Flash',
              onTap: _toggleTorch,
              isActive: isTorchOn,
            ),
            _buildControlButton(
              icon: Icons.refresh,
              label: 'Refresh',
              onTap: () {
                if (mounted) {
                  setState(() {
                    isScanning = true;
                    isLoading = false;
                  });
                  _safelyStartController();
                }
              },
            ),
          ],
          _buildControlButton(
            icon: showManualInput ? Icons.qr_code_scanner : Icons.keyboard,
            label: showManualInput ? 'Scan QR' : 'Enter Code',
            onTap: _toggleManualInput,
            isActive: showManualInput,
          ),
        ],
      ),
    );
  }

  Widget _buildControlButton({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
    bool isActive = false,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          color: isActive ? Colors.green.withOpacity(0.3) : const Color.fromRGBO(0, 0, 0, 0.5),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isActive ? Colors.green : Colors.white.withOpacity(0.5),
          ),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              color: isActive ? Colors.green : Colors.white,
              size: 20,
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                color: isActive ? Colors.green : Colors.white,
                fontSize: 12,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildManualInputPanel() {
    return Positioned(
      bottom: 0,
      left: 0,
      right: 0,
      child: SlideTransition(
        position: _slideAnimation,
        child: FadeTransition(
          opacity: _fadeAnimation,
          child: Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: Colors.grey[900],
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(20),
                topRight: Radius.circular(20),
              ),
              border: Border.all(color: Colors.grey[700]!),
            ),
            child: SafeArea(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Handle bar
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: Colors.grey[600],
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                  
                  // Input field
                  TextField(
                    controller: _codeController,
                    focusNode: _codeFocusNode,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.w500,
                      letterSpacing: 2,
                    ),
                    decoration: InputDecoration(
                      hintText: 'Enter animal code',
                      hintStyle: TextStyle(
                        color: Colors.grey[500],
                        fontSize: 16,
                      ),
                      filled: true,
                      fillColor: Colors.grey[800],
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide.none,
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Colors.green, width: 2),
                      ),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
                      prefixIcon: Icon(
                        Icons.qr_code_2,
                        color: Colors.grey[400],
                      ),
                    ),
                    textInputAction: TextInputAction.done,
                    onSubmitted: (_) => _submitManualCode(),
                  ),
                  const SizedBox(height: 16),
                  
                  // Submit button
                  ElevatedButton(
                    onPressed: _submitManualCode,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.green,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      elevation: 0,
                    ),
                    child: const Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.search, size: 20),
                        SizedBox(width: 8),
                        Text(
                          'Find Animal',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildLoadingIndicator() {
    return Container(
      color: const Color.fromRGBO(0, 0, 0, 0.8),
      child: Center(
        child: Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.grey[900],
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey[700]!),
          ),
          child: const Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(Colors.green),
              ),
              SizedBox(height: 16),
              Text(
                'Processing...',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}