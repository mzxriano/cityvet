import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
import 'package:geocoding/geocoding.dart';
import 'package:image_picker/image_picker.dart';
import 'package:latlong2/latlong.dart';
import '../models/incident_model.dart';
import '../services/incident_service.dart';
import '../utils/config.dart';

class ReportIncidentView extends StatefulWidget {
  const ReportIncidentView({Key? key}) : super(key: key);

  @override
  State<ReportIncidentView> createState() => _ReportIncidentViewState();
}

class _ReportIncidentViewState extends State<ReportIncidentView> {
  MapController? _mapController;
  List<IncidentModel> _incidents = [];
  IncidentModel? _selectedIncident;
  
  // Report form state
  bool _showReportForm = false;
  LatLng? _selectedLocation;
  bool _isLoadingLocation = false;
  List<String> _locationSuggestions = [];
  
  // Form controllers
  final _victimNameController = TextEditingController();
  final _ageController = TextEditingController();
  final _speciesController = TextEditingController();
  final _biteProvocationController = TextEditingController();
  final _locationController = TextEditingController();
  final _remarksController = TextEditingController();
  
  // Additional form state
  File? _selectedImage;
  DateTime _selectedDateTime = DateTime.now();
  bool _isSubmittingReport = false;
  final ImagePicker _imagePicker = ImagePicker();

  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(milliseconds: 500), () {
      _loadIncidents();
    });
  }

  Future<void> _loadIncidents() async {
    try {
      final incidentService = IncidentService();
      final result = await incidentService.getIncidents(); // Use getIncidents instead of fetchIncidentsForBarangay
      
      if (mounted) {
        setState(() {
          if (result['success']) {
            // Only show confirmed incidents to regular users on the public map
            _incidents = (result['data'] as List<IncidentModel>)
                .where((incident) => incident.status == 'confirmed')
                .toList();
          } else {
            _incidents = [];
            print('Failed to load incidents: ${result['message']}');
          }
        });
      }
    } catch (e) {
      print('Error loading incidents: $e');
      if (mounted) {
        setState(() {
          _incidents = [];
        });
      }
    }
  }

  void _onIncidentTap(IncidentModel incident) {
    setState(() {
      _selectedIncident = incident;
    });
  }

  void _hideIncidentDetails() {
    setState(() {
      _selectedIncident = null;
    });
  }

  String _formatDateTime(DateTime dateTime) {
    return '${dateTime.day}/${dateTime.month}/${dateTime.year} at ${dateTime.hour}:${dateTime.minute.toString().padLeft(2, '0')}';
  }

  void _showReportFormDialog() {
    setState(() {
      _showReportForm = true;
      _selectedIncident = null;
    });
  }

  void _hideReportForm() {
    setState(() {
      _showReportForm = false;
    });
  }

  Future<void> _getCurrentLocation() async {
    setState(() {
      _isLoadingLocation = true;
    });

    try {
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        throw Exception('Location services are disabled.');
      }

      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          throw Exception('Location permissions are denied');
        }
      }

      if (permission == LocationPermission.deniedForever) {
        throw Exception('Location permissions are permanently denied.');
      }

      Position position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      );

      List<Placemark> placemarks = await placemarkFromCoordinates(
        position.latitude,
        position.longitude,
      );

      if (placemarks.isNotEmpty) {
        Placemark place = placemarks.first;
        String locality = place.locality?.toLowerCase() ?? '';
        String subAdministrativeArea = place.subAdministrativeArea?.toLowerCase() ?? '';
        String administrativeArea = place.administrativeArea?.toLowerCase() ?? '';

        // Check if location is within Urdaneta
        bool isInUrdaneta = locality.contains('urdaneta') || 
                           subAdministrativeArea.contains('urdaneta') ||
                           administrativeArea.contains('urdaneta');

        if (!isInUrdaneta) {
          setState(() {
            _isLoadingLocation = false;
          });
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Current location must be within Urdaneta City'),
              backgroundColor: Colors.red,
            ),
          );
          return;
        }

        String address = '${place.street ?? ''}, ${place.locality ?? ''}, ${place.country ?? ''}';
        
        setState(() {
          _selectedLocation = LatLng(position.latitude, position.longitude);
          _locationController.text = address.trim().replaceAll(RegExp(r'^,\s*'), '');
          _isLoadingLocation = false;
        });

        _mapController?.move(_selectedLocation!, 16);
      }
    } catch (e) {
      setState(() {
        _isLoadingLocation = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to get location: ${e.toString()}')),
      );
    }
  }

  Future<void> _searchLocation(String query) async {
    if (query.isEmpty) return;

    try {
      // Add Urdaneta to search query if not present
      String searchQuery = query.toLowerCase().contains('urdaneta') 
          ? query 
          : '$query, Urdaneta City, Pangasinan, Philippines';

      List<Location> locations = await locationFromAddress(searchQuery);
      if (locations.isNotEmpty) {
        Location location = locations.first;
        
        // Verify the location is within Urdaneta bounds (approximate)
        List<Placemark> placemarks = await placemarkFromCoordinates(
          location.latitude,
          location.longitude,
        );
        
        if (placemarks.isNotEmpty) {
          Placemark place = placemarks.first;
          String locality = place.locality?.toLowerCase() ?? '';
          String subAdministrativeArea = place.subAdministrativeArea?.toLowerCase() ?? '';
          String administrativeArea = place.administrativeArea?.toLowerCase() ?? '';

          bool isInUrdaneta = locality.contains('urdaneta') || 
                             subAdministrativeArea.contains('urdaneta') ||
                             administrativeArea.contains('urdaneta');

          if (!isInUrdaneta) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Location must be within Urdaneta City'),
                backgroundColor: Colors.red,
              ),
            );
            return;
          }
        }

        LatLng newLocation = LatLng(location.latitude, location.longitude);

        setState(() {
          _selectedLocation = newLocation;
          _locationController.text = searchQuery;
        });

        _mapController?.move(newLocation, 16);
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to search location: ${e.toString()}')),
      );
    }
  }

  Future<void> _pickImage(ImageSource source) async {
    try {
      final XFile? image = await _imagePicker.pickImage(source: source);
      if (image != null) {
        setState(() {
          _selectedImage = File(image.path);
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to pick image: ${e.toString()}')),
      );
    }
  }

  void _showImagePickerOptions() {
    showModalBottomSheet(
      context: context,
      builder: (BuildContext context) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(
                leading: const Icon(Icons.camera_alt),
                title: const Text('Camera'),
                onTap: () {
                  Navigator.of(context).pop();
                  _pickImage(ImageSource.camera);
                },
              ),
              ListTile(
                leading: const Icon(Icons.photo_library),
                title: const Text('Gallery'),
                onTap: () {
                  Navigator.of(context).pop();
                  _pickImage(ImageSource.gallery);
                },
              ),
            ],
          ),
        );
      },
    );
  }

  bool _validateForm() {
    if (_victimNameController.text.isEmpty) {
      _showError('Please enter victim name');
      return false;
    }
    if (_ageController.text.isEmpty) {
      _showError('Please enter age');
      return false;
    }
    if (_speciesController.text.isEmpty) {
      _showError('Please enter species');
      return false;
    }
    if (_biteProvocationController.text.isEmpty) {
      _showError('Please enter bite provocation');
      return false;
    }
    if (_selectedLocation == null) {
      _showError('Please select a location');
      return false;
    }
    return true;
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.red),
    );
  }

  void _updateLocationSuggestions(String query) {
    if (query.length < 2) {
      _locationSuggestions.clear();
      return;
    }

    // Common locations in Urdaneta City
    List<String> commonLocations = [
      'Barangay Poblacion, Urdaneta City',
      'City Plaza, Urdaneta City', 
      'Urdaneta City Hall',
      'Urdaneta Public Market',
      'MacArthur Highway, Urdaneta City',
      'Barangay San Jose, Urdaneta City',
      'Barangay Bolaney, Urdaneta City',
      'Barangay Camambugan, Urdaneta City',
      'Barangay Camantiles, Urdaneta City',
      'Barangay Camanang, Urdaneta City',
      'Barangay Consolacion, Urdaneta City',
      'Barangay Dilan-Paurido, Urdaneta City',
      'Barangay Dr. Pedro T. Orata, Urdaneta City',
      'Barangay Labit Proper, Urdaneta City',
      'Barangay Mabanogbog, Urdaneta City',
      'Barangay Macalong, Urdaneta City',
      'Barangay Nancalobasaan, Urdaneta City',
      'Barangay Nancamaliran East, Urdaneta City',
      'Barangay Nancamaliran West, Urdaneta City',
      'Barangay Oltama, Urdaneta City',
      'Barangay Palina East, Urdaneta City',
      'Barangay Palina West, Urdaneta City',
      'Barangay Pinmaludpod, Urdaneta City',
      'Barangay San Vicente, Urdaneta City',
      'Barangay Santa Maria, Urdaneta City',
      'Barangay Sugcong, Urdaneta City',
      'Barangay Tulong, Urdaneta City',
    ];

    _locationSuggestions = commonLocations
        .where((location) => location.toLowerCase().contains(query.toLowerCase()))
        .take(5)
        .toList();
  }

  Widget _buildSectionTitle(String title, IconData icon) {
    return Row(
      children: [
        Icon(icon, color: Colors.grey[700], size: 18),
        const SizedBox(width: 8),
        Text(
          title,
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontSize: 16,
            fontWeight: FontWeight.w500,
            color: Colors.grey[800],
          ),
        ),
      ],
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    required String hint,
    required IconData icon,
    TextInputType? keyboardType,
    int maxLines = 1,
    Widget? suffixIcon,
    Function(String)? onChanged,
    Function(String)? onSubmitted,
  }) {
    return TextField(
      controller: controller,
      keyboardType: keyboardType,
      maxLines: maxLines,
      onChanged: onChanged,
      onSubmitted: onSubmitted,
      style: TextStyle(
        fontFamily: Config.primaryFont,
        fontSize: 14,
      ),
      decoration: InputDecoration(
        labelText: label,
        hintText: hint,
        prefixIcon: Icon(icon, color: Colors.grey[600], size: 20),
        suffixIcon: suffixIcon,
        labelStyle: TextStyle(
          fontFamily: Config.primaryFont,
          color: Colors.grey[600],
          fontSize: 14,
        ),
        hintStyle: TextStyle(
          fontFamily: Config.primaryFont,
          color: Colors.green[600],
          fontSize: 14,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: Colors.black, width: 1),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: Colors.black, width: 1),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: Colors.green, width: 2),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
      ),
    );
  }

  Widget _buildDateTimeSelector() {
    return Row(
      children: [
        Expanded(
          child: InkWell(
            onTap: () async {
              final date = await showDatePicker(
                context: context,
                initialDate: _selectedDateTime,
                firstDate: DateTime(2020),
                lastDate: DateTime.now(),
              );
              if (date != null) {
                setState(() {
                  _selectedDateTime = DateTime(
                    date.year,
                    date.month,
                    date.day,
                    _selectedDateTime.hour,
                    _selectedDateTime.minute,
                  );
                });
              }
            },
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.black),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  Icon(Icons.calendar_today, color: Colors.grey[600], size: 20),
                  const SizedBox(width: 8),
                  Text(
                    '${_selectedDateTime.day}/${_selectedDateTime.month}/${_selectedDateTime.year}',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: 14,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: InkWell(
            onTap: () async {
              final time = await showTimePicker(
                context: context,
                initialTime: TimeOfDay.fromDateTime(_selectedDateTime),
              );
              if (time != null) {
                setState(() {
                  _selectedDateTime = DateTime(
                    _selectedDateTime.year,
                    _selectedDateTime.month,
                    _selectedDateTime.day,
                    time.hour,
                    time.minute,
                  );
                });
              }
            },
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.black),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  Icon(Icons.access_time, color: Colors.grey[600], size: 20),
                  const SizedBox(width: 8),
                  Text(
                    '${_selectedDateTime.hour}:${_selectedDateTime.minute.toString().padLeft(2, '0')}',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: 14,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }

  Future<void> _submitReport() async {
    if (!_validateForm()) return;

    setState(() {
      _isSubmittingReport = true;
    });

    try {
      // Import incident service
      final incidentService = IncidentService();
      
      // Submit incident to the API
      final result = await incidentService.reportIncident(
        victimName: _victimNameController.text,
        age: int.tryParse(_ageController.text) ?? 0,
        species: _speciesController.text,
        biteProvocation: _biteProvocationController.text,
        latitude: _selectedLocation!.latitude,
        longitude: _selectedLocation!.longitude,
        locationAddress: _locationController.text,
        incidentTime: _selectedDateTime,
        remarks: _remarksController.text.isEmpty ? null : _remarksController.text,
        photoFile: _selectedImage,
      );

      if (result['success']) {
        // Clear form on success
        _victimNameController.clear();
        _ageController.clear();
        _speciesController.clear();
        _biteProvocationController.clear();
        _remarksController.clear();
        _locationController.clear();
        _selectedLocation = null;
        _selectedImage = null;
        _selectedDateTime = DateTime.now();

        setState(() {
          _isSubmittingReport = false;
          _showReportForm = false;
        });

        // Reload incidents from server to show the new one
        await _loadIncidents();

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message'] ?? 'Incident reported successfully'),
            backgroundColor: Colors.green,
          ),
        );
      } else {
        setState(() {
          _isSubmittingReport = false;
        });
        _showError(result['message'] ?? 'Failed to submit report');
      }
    } catch (e) {
      setState(() {
        _isSubmittingReport = false;
      });
      _showError('Failed to submit report: ${e.toString()}');
    }
  }

  Widget _buildReportForm() {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(20),
          topRight: Radius.circular(20),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black12,
            blurRadius: 10,
            offset: Offset(0, -5),
          ),
        ],
      ),
      child: Column(
        children: [
          // Header with gradient background
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 20),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [Config.primaryColor, Config.primaryColor.withOpacity(0.8)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(20),
                topRight: Radius.circular(20),
              ),
            ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(Icons.report, color: Colors.white, size: 24),
                      ),
                      const SizedBox(width: 12),
                      Text(
                        'Report Animal Incident',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                    ],
                  ),
                  Container(
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: IconButton(
                      onPressed: _hideReportForm,
                      icon: const Icon(Icons.close, color: Colors.white),
                    ),
                  ),
                ],
              ),
            ),
          // Form Content
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Victim Name Section
                  _buildSectionTitle('Victim Information', Icons.person),
          const SizedBox(height: 12),
          _buildTextField(
            controller: _victimNameController,
            label: 'Victim Name',
            hint: 'Enter full name',
            icon: Icons.person_outline,
          ),
          const SizedBox(height: 16),

          // Age and Species in row
          Row(
            children: [
              Expanded(
                child: _buildTextField(
                  controller: _ageController,
                  label: 'Age',
                  hint: 'Enter age',
                  icon: Icons.cake_outlined,
                  keyboardType: TextInputType.number,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildTextField(
                  controller: _speciesController,
                  label: 'Animal Species',
                  hint: 'e.g., Dog, Cat',
                  icon: Icons.pets,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Bite Provocation Section
          _buildSectionTitle('Incident Details', Icons.warning),
          const SizedBox(height: 12),
          _buildTextField(
            controller: _biteProvocationController,
            label: 'Bite Provocation',
            hint: 'e.g., Provoked, Unprovoked',
            icon: Icons.warning_amber_outlined,
          ),
          const SizedBox(height: 16),

          // Location Section
          _buildSectionTitle('Location Information', Icons.location_on),
          const SizedBox(height: 12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _locationController,
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: 14,
                      ),
                      decoration: InputDecoration(
                        labelText: 'Location (within Urdaneta City)',
                        hintText: 'e.g., Barangay Poblacion, City Plaza',
                        prefixIcon: Icon(Icons.location_on_outlined, color: Colors.grey[600], size: 20),
                        suffixIcon: IconButton(
                          onPressed: () => _searchLocation(_locationController.text),
                          icon: Icon(Icons.search, color: Colors.grey[600], size: 20),
                          tooltip: 'Search location',
                        ),
                        labelStyle: TextStyle(
                          fontFamily: Config.primaryFont,
                          color: Colors.grey[600],
                          fontSize: 14,
                        ),
                        hintStyle: TextStyle(
                          fontFamily: Config.primaryFont,
                          color: Colors.green[600],
                          fontSize: 14,
                        ),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                          borderSide: const BorderSide(color: Colors.black, width: 1),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                          borderSide: const BorderSide(color: Colors.black, width: 1),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                          borderSide: const BorderSide(color: Colors.green, width: 2),
                        ),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                      ),
                      onSubmitted: _searchLocation,
                      onChanged: (value) {
                        setState(() {
                          _updateLocationSuggestions(value);
                        });
                      },
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    decoration: BoxDecoration(
                      border: Border.all(color: Colors.black),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: IconButton(
                      onPressed: _isLoadingLocation ? null : _getCurrentLocation,
                      tooltip: 'Use current location',
                      icon: _isLoadingLocation 
                        ? SizedBox(
                            width: 16,
                            height: 16,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.grey[600],
                            ),
                          )
                        : Icon(Icons.my_location, color: Colors.grey[600], size: 20),
                    ),
                  ),
                ],
              ),
              if (_locationSuggestions.isNotEmpty) ...[
                const SizedBox(height: 8),
                Container(
                  constraints: const BoxConstraints(maxHeight: 150),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    border: Border.all(color: Colors.grey.shade200),
                    borderRadius: BorderRadius.circular(12),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.1),
                        blurRadius: 8,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: ListView.builder(
                    shrinkWrap: true,
                    itemCount: _locationSuggestions.length,
                    itemBuilder: (context, index) {
                      return ListTile(
                        dense: true,
                        leading: Icon(Icons.location_on_outlined, color: Config.primaryColor.withOpacity(0.6), size: 18),
                        title: Text(
                          _locationSuggestions[index],
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: 14,
                          ),
                        ),
                        onTap: () {
                          _locationController.text = _locationSuggestions[index];
                          _searchLocation(_locationSuggestions[index]);
                          setState(() {
                            _locationSuggestions.clear();
                          });
                        },
                      );
                    },
                  ),
                ),
              ],
            ],
          ),
          const SizedBox(height: 16),

          // Date and Time Section
          _buildSectionTitle('Incident Date & Time', Icons.schedule),
          const SizedBox(height: 12),
          _buildDateTimeSelector(),
          const SizedBox(height: 16),

          // Remarks Section
          _buildSectionTitle('Additional Information', Icons.note),
          const SizedBox(height: 12),
          _buildTextField(
            controller: _remarksController,
            label: 'Remarks (Optional)',
            hint: 'Any additional details about the incident',
            icon: Icons.note_alt_outlined,
            maxLines: 3,
          ),
          const SizedBox(height: 16),

          // Photo Section
          _buildSectionTitle('Photo Evidence', Icons.camera_alt),
          const SizedBox(height: 12),
          Container(
            width: double.infinity,
            decoration: BoxDecoration(
              border: Border.all(color: Colors.black),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Column(
              children: [
                if (_selectedImage != null) ...[
                  ClipRRect(
                    borderRadius: const BorderRadius.only(
                      topLeft: Radius.circular(8),
                      topRight: Radius.circular(8),
                    ),
                    child: Image.file(
                      _selectedImage!,
                      height: 150,
                      width: double.infinity,
                      fit: BoxFit.cover,
                    ),
                  ),
                  const SizedBox(height: 12),
                ],
                Padding(
                  padding: const EdgeInsets.all(12),
                  child: SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: _showImagePickerOptions,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green[50],
                        foregroundColor: Colors.green[700],
                        side: BorderSide(color: Colors.green[600]!),
                        padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 24),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      icon: Icon(Icons.add_a_photo, size: 20, color: Colors.green[700]),
                      label: Text(
                        _selectedImage != null ? 'Change Photo' : 'Add Photo Evidence',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),

          // Submit Button
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _isSubmittingReport ? null : _submitReport,
              style: ElevatedButton.styleFrom(
                backgroundColor: _isSubmittingReport ? Colors.grey : Config.primaryColor,
                foregroundColor: Colors.white,
                side: BorderSide(color: _isSubmittingReport ? Colors.grey : Colors.black),
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              child: _isSubmittingReport
                ? Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      ),
                      SizedBox(width: 8),
                      Text(
                        'Submitting...',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: 14,
                        ),
                      ),
                    ],
                  )
                : Text(
                    'Submit Incident Report',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
            ),
          ),
          const SizedBox(height: 24),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Report Animal Incident',
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontWeight: FontWeight.bold,
          ),
        ),
        backgroundColor: Config.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            onPressed: _loadIncidents,
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: Stack(
        children: [
          // Map
          FlutterMap(
            mapController: _mapController ??= MapController(),
            options: MapOptions(
              initialCenter: const LatLng(15.9759, 120.5715), // Urdaneta City
              initialZoom: 13,
              minZoom: 3,
              maxZoom: 18,
            ),
            children: [
              TileLayer(
                urlTemplate: 'https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png',
                subdomains: const ['a', 'b', 'c', 'd'],
              ),
              // Incident circles
              CircleLayer(
                circles: _incidents.map((incident) => CircleMarker(
                  point: LatLng(incident.latitude, incident.longitude),
                  color: Colors.red.withOpacity(0.8),
                  borderStrokeWidth: 0,
                  radius: 8,
                )).toList(),
              ),
              // Clickable markers
              MarkerLayer(
                markers: _incidents.map((incident) => Marker(
                  point: LatLng(incident.latitude, incident.longitude),
                  child: GestureDetector(
                    onTap: () => _onIncidentTap(incident),
                    child: Container(
                      width: 20,
                      height: 20,
                      decoration: const BoxDecoration(
                        color: Colors.transparent,
                        shape: BoxShape.circle,
                      ),
                    ),
                  ),
                )).toList(),
              ),
            ],
          ),

          // Incident details overlay
          if (_selectedIncident != null && !_showReportForm)
            Center(
              child: Container(
                margin: const EdgeInsets.all(40),
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.2),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Incident Details',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                        IconButton(
                          onPressed: _hideIncidentDetails,
                          icon: const Icon(Icons.close),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Victim: ${_selectedIncident!.victimName}',
                      style: TextStyle(fontFamily: Config.primaryFont),
                    ),
                    Text(
                      'Species: ${_selectedIncident!.species}',
                      style: TextStyle(fontFamily: Config.primaryFont),
                    ),
                    Text(
                      'Age: ${_selectedIncident!.age}',
                      style: TextStyle(fontFamily: Config.primaryFont),
                    ),
                    Text(
                      'Incident Date: ${_formatDateTime(_selectedIncident!.incidentTime)}',
                      style: TextStyle(fontFamily: Config.primaryFont),
                    ),
                    Text(
                      'Bite Provocation: ${_selectedIncident!.biteProvocation}',
                      style: TextStyle(fontFamily: Config.primaryFont),
                    ),
                    Text(
                      'Location: ${_selectedIncident!.locationAddress}',
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        color: Colors.grey[600],
                      ),
                    ),
                    if (_selectedIncident!.remarks != null && _selectedIncident!.remarks!.isNotEmpty) ...[
                      const SizedBox(height: 8),
                      Text(
                        'Remarks: ${_selectedIncident!.remarks}',
                        style: TextStyle(fontFamily: Config.primaryFont),
                      ),
                    ],
                  ],
                ),
              ),
            ),

          // Report form bottom sheet
          if (_showReportForm)
            Positioned(
              bottom: 0,
              left: 0,
              right: 0,
              child: Container(
                height: MediaQuery.of(context).size.height * 0.75,
                decoration: const BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.only(
                    topLeft: Radius.circular(20),
                    topRight: Radius.circular(20),
                  ),
                ),
                child: _buildReportForm(),
              ),
            ),
        ],
      ),
      floatingActionButton: !_showReportForm ? FloatingActionButton.extended(
        onPressed: _showReportFormDialog,
        backgroundColor: Colors.red[600],
        foregroundColor: Colors.white,
        icon: const Icon(Icons.report),
        label: Text(
          'Report Incident',
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontWeight: FontWeight.w600,
          ),
        ),
      ) : null,
    );
  }
}