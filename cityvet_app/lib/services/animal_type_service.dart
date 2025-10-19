import 'dart:convert';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class AnimalTypeService {
  final storage = const FlutterSecureStorage();

  Future<Map<String, dynamic>> getAnimalTypesAndBreeds() async {
    try {
      final token = await storage.read(key: 'auth_token');
      
      final response = await http.get(
        Uri.parse('${ApiConstant.baseUrl}/animal-types'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        
        // Transform API response to the format expected by the forms
        Map<String, List<String>> petBreeds = {};
        List<Map<String, dynamic>> animalTypes = [];
        
        if (data['data'] != null && data['data'] is List) {
          for (var type in data['data']) {
            String typeName = type['name'] ?? '';
            animalTypes.add({
              'id': type['id'],
              'name': typeName,
              'display_name': type['display_name'] ?? typeName,
              'category': type['category'],
              'icon': type['icon'],
            });
            
            // Extract breeds for this type
            List<String> breeds = [];
            if (type['breeds'] != null && type['breeds'] is List) {
              breeds = (type['breeds'] as List)
                  .map((breed) => breed['name'] as String)
                  .toList();
            }
            petBreeds[typeName] = breeds;
          }
        }
        
        return {
          'petBreeds': petBreeds,
          'animalTypes': animalTypes,
        };
      } else {
        throw Exception('Failed to load animal types: ${response.statusCode}');
      }
    } catch (e) {
      print('Error fetching animal types: $e');
      return {
        'petBreeds': _getDefaultBreeds(),
        'animalTypes': [],
      };
    }
  }

  // Fallback data if API is unavailable
  Map<String, List<String>> _getDefaultBreeds() {
    return {
      'dog': ['Aspin', 'Shih Tzu', 'Golden Retriever', 'Labrador', 'German Shepherd', 'Poodle', 'Bulldog', 'Beagle'],
      'cat': ['Puspin', 'Persian', 'Siamese', 'Maine Coon', 'British Shorthair', 'Ragdoll', 'Russian Blue'],
      'cattle': ['Holstein', 'Brahman', 'Simmental', 'Native', 'Jersey', 'Angus'],
      'goat': ['Boer', 'Anglo-Nubian', 'Native', 'Saanen', 'Toggenburg'],
      'chicken': ['Native', 'Rhode Island Red', 'Leghorn', 'Broiler', 'Layer', 'Bantam'],
      'duck': ['Mallard', 'Pekin', 'Native', 'Muscovy', 'Khaki Campbell'],
      'carabao': ['Native', 'Murrah', 'River Type', 'Swamp Type'],
    };
  }
}
