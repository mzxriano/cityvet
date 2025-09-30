import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
import 'package:geocoding/geocoding.dart';
import 'package:image_picker/image_picker.dart';
import 'package:latlong2/latlong.dart';
import 'package:provider/provider.dart';
import '../models/incident_model.dart';
import '../utils/config.dart';
import '../viewmodels/incident_viewmodel.dart';

class ReportIncidentView extends StatefulWidget {
  const ReportIncidentView({Key? key}) : super(key: key);

  @override
  State<ReportIncidentView> createState() => _ReportIncidentViewState();
}

class _ReportIncidentViewState extends State<ReportIncidentView> {
  MapController? _mapController;
  bool _mapLoaded = true;
  List<IncidentModel> _incidents = [];
  IncidentModel? _selectedIncident;
  
  // Report form state
  bool _showReportForm = false;
  LatLng? _selectedLocation;
  bool _isLoadingLocation = false;
  
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
    final incidentViewModel = Provider.of<IncidentViewModel>(context, listen: false);
    await incidentViewModel.loadIncidents(refresh: true);
    
    if (mounted) {
      setState(() {
        _incidents = incidentViewModel.incidents;
      });
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
      List<Location> locations = await locationFromAddress(query);
      if (locations.isNotEmpty) {
        Location location = locations.first;
        LatLng newLocation = LatLng(location.latitude, location.longitude);

        setState(() {
          _selectedLocation = newLocation;
          _locationController.text = query;
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

  Future<void> _submitReport() async {
    if (!_validateForm()) return;

    setState(() {
      _isSubmittingReport = true;
    });

    try {
      final incident = IncidentModel(
        victimName: _victimNameController.text,
        age: int.tryParse(_ageController.text) ?? 0,
        species: _speciesController.text,
        biteProvocation: _biteProvocationController.text,
        latitude: _selectedLocation!.latitude,
        longitude: _selectedLocation!.longitude,
        locationAddress: _locationController.text,
        incidentTime: _selectedDateTime,
        remarks: _remarksController.text.isEmpty ? null : _remarksController.text,
        photoPath: _selectedImage?.path,
        reportedAt: DateTime.now(),
        reportedBy: 'Current User',
      );

      setState(() {
        _incidents.add(incident);
        _isSubmittingReport = false;
        _showReportForm = false;
      });

      // Clear form
      _victimNameController.clear();
      _ageController.clear();
      _speciesController.clear();
      _biteProvocationController.clear();
      _remarksController.clear();
      _locationController.clear();
      _selectedLocation = null;
      _selectedImage = null;
      _selectedDateTime = DateTime.now();

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Incident reported successfully'),
          backgroundColor: Colors.green,
        ),
      );
    } catch (e) {
      setState(() {
        _isSubmittingReport = false;
      });
      _showError('Failed to submit report: ${e.toString()}');
    }
  }

  Widget _buildReportForm() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Report Incident',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                ),
              ),
              IconButton(
                onPressed: _hideReportForm,
                icon: const Icon(Icons.close),
              ),
            ],
          ),
          const SizedBox(height: 20),

          // Victim Name
          TextField(
            controller: _victimNameController,
            decoration: InputDecoration(
              labelText: 'Victim Name',
              labelStyle: TextStyle(fontFamily: Config.primaryFont),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Age and Species in row
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _ageController,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(
                    labelText: 'Age',
                    labelStyle: TextStyle(fontFamily: Config.primaryFont),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: TextField(
                  controller: _speciesController,
                  decoration: InputDecoration(
                    labelText: 'Species',
                    labelStyle: TextStyle(fontFamily: Config.primaryFont),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Bite Provocation
          TextField(
            controller: _biteProvocationController,
            decoration: InputDecoration(
              labelText: 'Bite Provocation',
              labelStyle: TextStyle(fontFamily: Config.primaryFont),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Location
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _locationController,
                  decoration: InputDecoration(
                    labelText: 'Location',
                    labelStyle: TextStyle(fontFamily: Config.primaryFont),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                  onSubmitted: _searchLocation,
                ),
              ),
              const SizedBox(width: 8),
              IconButton(
                onPressed: _isLoadingLocation ? null : _getCurrentLocation,
                icon: _isLoadingLocation 
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Icon(Icons.my_location),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Date and Time
          InkWell(
            onTap: () async {
              final date = await showDatePicker(
                context: context,
                initialDate: _selectedDateTime,
                firstDate: DateTime(2020),
                lastDate: DateTime.now(),
              );
              if (date != null) {
                final time = await showTimePicker(
                  context: context,
                  initialTime: TimeOfDay.fromDateTime(_selectedDateTime),
                );
                if (time != null) {
                  setState(() {
                    _selectedDateTime = DateTime(
                      date.year,
                      date.month,
                      date.day,
                      time.hour,
                      time.minute,
                    );
                  });
                }
              }
            },
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 16),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Incident Date: ${_formatDateTime(_selectedDateTime)}',
                    style: TextStyle(fontFamily: Config.primaryFont),
                  ),
                  const Icon(Icons.calendar_today),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Remarks
          TextField(
            controller: _remarksController,
            maxLines: 3,
            decoration: InputDecoration(
              labelText: 'Remarks (Optional)',
              labelStyle: TextStyle(fontFamily: Config.primaryFont),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Photo
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              border: Border.all(color: Colors.grey),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Column(
              children: [
                if (_selectedImage != null) ...[
                  Image.file(
                    _selectedImage!,
                    height: 200,
                    width: double.infinity,
                    fit: BoxFit.cover,
                  ),
                  const SizedBox(height: 10),
                ],
                ElevatedButton.icon(
                  onPressed: _showImagePickerOptions,
                  icon: const Icon(Icons.camera_alt),
                  label: Text(
                    _selectedImage != null ? 'Change Photo' : 'Add Photo',
                    style: TextStyle(fontFamily: Config.primaryFont),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Submit Button
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _isSubmittingReport ? null : _submitReport,
              style: ElevatedButton.styleFrom(
                backgroundColor: Config.primaryColor,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              child: _isSubmittingReport
                ? const CircularProgressIndicator(color: Colors.white)
                : Text(
                    'Submit Report',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
            ),
          ),
          const SizedBox(height: 20),
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
              initialCenter: const LatLng(9.9432, 123.2841), // Dumaguete City
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
                  color: Colors.red.withOpacity(0.3),
                  borderColor: Colors.red,
                  borderStrokeWidth: 2,
                  radius: 20,
                )).toList(),
              ),
              // Clickable markers
              MarkerLayer(
                markers: _incidents.map((incident) => Marker(
                  point: LatLng(incident.latitude, incident.longitude),
                  child: GestureDetector(
                    onTap: () => _onIncidentTap(incident),
                    child: Container(
                      width: 40,
                      height: 40,
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
            Positioned(
              bottom: 20,
              left: 20,
              right: 20,
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.1),
                      blurRadius: 6,
                      offset: const Offset(0, 3),
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
