import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:dio/dio.dart';

class NearbyClinicsMap extends StatefulWidget {
  const NearbyClinicsMap({super.key});

  @override
  State<NearbyClinicsMap> createState() => _NearbyClinicsMapState();
}

class _NearbyClinicsMapState extends State<NearbyClinicsMap> {
  MapController? _mapController;
  final Dio _dio = Dio();
  bool _isLoadingClinics = true;
  
  List<Map<String, dynamic>> _clinics = [];
  Map<String, dynamic>? _selectedClinic;

  @override
  void initState() {
    super.initState();
    _loadClinicsFromOpenStreetMap();
  }

  /// Fetch address using reverse geocoding from Nominatim
  Future<String> _reverseGeocode(double lat, double lon) async {
    try {
      final response = await _dio.get(
        'https://nominatim.openstreetmap.org/reverse',
        queryParameters: {
          'format': 'json',
          'lat': lat,
          'lon': lon,
          'zoom': 18,
          'addressdetails': 1,
        },
        options: Options(
          headers: {
            'User-Agent': 'CityVetApp/1.0',
          },
        ),
      );

      if (response.statusCode == 200) {
        final data = response.data;
        final address = data['address'] ?? {};
        
        List<String> addressParts = [];
        
        if (address['house_number'] != null) {
          addressParts.add(address['house_number']);
        }
        if (address['road'] != null) {
          addressParts.add(address['road']);
        }
        if (address['suburb'] != null) {
          addressParts.add(address['suburb']);
        }
        if (address['city'] != null) {
          addressParts.add(address['city']);
        } else if (address['town'] != null) {
          addressParts.add(address['town']);
        } else if (address['municipality'] != null) {
          addressParts.add(address['municipality']);
        }
        if (address['province'] != null || address['state'] != null) {
          addressParts.add(address['province'] ?? address['state']);
        }
        if (address['postcode'] != null) {
          addressParts.add(address['postcode']);
        }
        
        if (addressParts.isNotEmpty) {
          return addressParts.join(', ');
        }
        
        if (data['display_name'] != null) {
          return data['display_name'] as String;
        }
      }
    } catch (e) {
      debugPrint('Reverse geocoding error: $e');
    }
    
    return 'Urdaneta City, Pangasinan';
  }

  /// Extract address from OSM tags
  String _extractAddressFromTags(Map<String, dynamic> tags) {
    final houseNumber = tags['addr:housenumber'] ?? '';
    final street = tags['addr:street'] ?? '';
    final city = tags['addr:city'] ?? '';
    final province = tags['addr:province'] ?? '';
    final postcode = tags['addr:postcode'] ?? '';
    final fullAddress = tags['addr:full'] ?? '';
    
    String address = '';
    
    if (fullAddress.isNotEmpty) {
      return fullAddress;
    }
    
    if (houseNumber.isNotEmpty) {
      address += '$houseNumber ';
    }
    if (street.isNotEmpty) {
      address += street;
    }
    
    if (address.isNotEmpty && city.isNotEmpty && !address.toLowerCase().contains(city.toLowerCase())) {
      address += ', $city';
    } else if (address.isEmpty && city.isNotEmpty) {
      address = city;
    }
    
    if (address.isNotEmpty && province.isNotEmpty && !address.toLowerCase().contains(province.toLowerCase())) {
      address += ', $province';
    }
    
    if (postcode.isNotEmpty && !address.contains(postcode)) {
      address += ' $postcode';
    }
    
    return address.trim();
  }

  Future<void> _loadClinicsFromOpenStreetMap() async {
    try {
      // Overpass API query for veterinary clinics in Urdaneta City area
      const String overpassQuery = '''
        [out:json];
        (
          node["amenity"="veterinary"](15.95,120.55,16.00,120.60);
          way["amenity"="veterinary"](15.95,120.55,16.00,120.60);
        );
        out center;
      ''';

      final response = await _dio.post(
        'https://overpass-api.de/api/interpreter',
        data: overpassQuery,
      );

      if (response.statusCode == 200 && mounted) {
        final data = response.data;
        final List<Map<String, dynamic>> loadedClinics = [];

        debugPrint('OSM Response: ${data['elements']?.length ?? 0} elements found');

        for (var element in data['elements']) {
          final lat = element['lat'] ?? element['center']?['lat'];
          final lon = element['lon'] ?? element['center']?['lon'];
          final tags = element['tags'] ?? {};
          
          final name = tags['name'] ?? 'Veterinary Clinic';
          
          debugPrint('Processing clinic: $name');
          debugPrint('Tags: $tags');
          
          if (lat != null && lon != null) {
            String address = _extractAddressFromTags(tags);
            
            if (address.isEmpty || address == 'Urdaneta City, Pangasinan') {
              debugPrint('Using reverse geocoding for: $name');
              address = await _reverseGeocode(lat.toDouble(), lon.toDouble());
              await Future.delayed(const Duration(milliseconds: 1100));
            }
            
            debugPrint('Final address: $address');
            debugPrint('---');

            loadedClinics.add({
              'name': name,
              'location': LatLng(lat.toDouble(), lon.toDouble()),
              'address': address,
            });
          }
        }

        setState(() {
          // Accurate clinic locations in Urdaneta City
          _clinics = [
            {
              'name': 'UCVC Veterinary Clinic',
              'location': const LatLng(15.97489, 120.55528),
              'address': '5882 Urdaneta Junction - Dagupan Rd, Urdaneta City, Pangasinan',
            },
            {
              'name': 'PetCare Veterinary Clinic',
              'location': const LatLng(15.97469, 120.54944),
              'address': 'Urdaneta City, Pangasinan',
            },
            {
              'name': 'Pet Station Veterinary Clinic',
              'location': const LatLng(15.97603, 120.57025), // Near Urduja Hotel, Alexander St
              'address': 'Urduja Hotel, 653 Alexander Street, Urdaneta City, Pangasinan 2428'
            },
            {
              'name': 'City Veterinary Office',
              'location': const LatLng(15.97613, 120.56691), // City Hall Complex
              'address': 'XHG8+CVM, National Highway, NH7, Urdaneta City, 2428 Pangasinan',
            },
            ...loadedClinics,
          ];
          _isLoadingClinics = false;
          debugPrint('Total clinics loaded: ${_clinics.length}');
        });
      }
    } catch (e) {
      debugPrint('Error loading clinics from OpenStreetMap: $e');
      if (mounted) {
        setState(() {
          // Fallback to default clinics if API fails
          _clinics = [
            {
              'name': 'UCVC Veterinary Clinic',
              'location': const LatLng(15.97489, 120.55528),
              'address': '5882 Urdaneta Junction - Dagupan Rd, Urdaneta City, Pangasinan',
            },
            {
              'name': 'PetCare Veterinary Clinic',
              'location': const LatLng(15.97469, 120.54944),
              'address': 'Urdaneta City, Pangasinan',
            },
            {
              'name': 'Pet Station Veterinary Clinic',
              'location': const LatLng(15.97603, 120.57025), // Near Urduja Hotel, Alexander St
              'address': 'Urduja Hotel, 653 Alexander Street, Urdaneta City, Pangasinan 2428'
            },
            {
              'name': 'City Veterinary Office',
              'location': const LatLng(15.97613, 120.56691), // City Hall Complex
              'address': 'XHG8+CVM, National Highway, NH7, Urdaneta City, 2428 Pangasinan',
            },
          ];
          _isLoadingClinics = false;
        });
      }
    }
  }

  @override
  void dispose() {
    _mapController?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        const Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Nearby Veterinary Clinics',
              style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontMedium,
                fontWeight: Config.fontW600,
              ),
            ),
          ],
        ),
        
        Config.heightSmall,
        
        Container(
          height: 300,
          width: double.infinity,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: Colors.grey.withOpacity(0.2),
              width: 1,
            ),
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: _isLoadingClinics
                ? const Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        CircularProgressIndicator(color: Colors.blue),
                        SizedBox(height: 12),
                        Text(
                          'Loading veterinary clinics...',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: 12,
                            color: Colors.grey,
                          ),
                        ),
                      ],
                    ),
                  )
                : Stack(
              children: [
                FlutterMap(
                  mapController: _mapController ??= MapController(),
                  options: MapOptions(
                    initialCenter: const LatLng(15.9759, 120.5715),
                    initialZoom: 14,
                    minZoom: 3,
                    maxZoom: 18,
                  ),
                  children: [
                    TileLayer(
                      urlTemplate: 'https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png',
                      subdomains: const ['a', 'b', 'c', 'd'],
                    ),
                    
                    MarkerLayer(
                      markers: _clinics.map((clinic) {
                        return Marker(
                          point: clinic['location'] as LatLng,
                          width: 40,
                          height: 40,
                          child: GestureDetector(
                            onTap: () {
                              setState(() {
                                _selectedClinic = clinic;
                              });
                            },
                            child: const Icon(
                              Icons.location_on,
                              color: Colors.blue,
                              size: 40,
                            ),
                          ),
                        );
                      }).toList(),
                    ),
                  ],
                ),
                
                if (_selectedClinic != null)
                  Positioned(
                    bottom: 16,
                    left: 16,
                    right: 16,
                    child: Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.2),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
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
                              Expanded(
                                child: Text(
                                  _selectedClinic!['name'],
                                  style: const TextStyle(
                                    fontFamily: Config.primaryFont,
                                    fontSize: Config.fontSmall,
                                    fontWeight: Config.fontW600,
                                    color: Color(0xFF524F4F),
                                  ),
                                ),
                              ),
                              IconButton(
                                icon: const Icon(Icons.close, size: 20),
                                onPressed: () {
                                  setState(() {
                                    _selectedClinic = null;
                                  });
                                },
                                padding: EdgeInsets.zero,
                                constraints: const BoxConstraints(),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              const Icon(
                                Icons.location_on,
                                size: 16,
                                color: Colors.grey,
                              ),
                              const SizedBox(width: 4),
                              Expanded(
                                child: Text(
                                  _selectedClinic!['address'],
                                  style: const TextStyle(
                                    fontFamily: Config.primaryFont,
                                    fontSize: 12,
                                    color: Colors.grey,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ),
        
        const SizedBox(height: 8),
        Text(
          'Tap markers to view clinic details',
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontSize: 11,
            color: Colors.grey[600],
            fontStyle: FontStyle.italic,
          ),
        ),
      ],
    );
  }
}