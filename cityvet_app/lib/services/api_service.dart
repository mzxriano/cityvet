import 'package:dio/dio.dart';

class ApiService {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api',
    headers: {
      'Accept': 'application/json',
    },
  ));

  Future<List<dynamic>> getUsers() async {
    try {
      final response = await _dio.get('/users');

      if (response.statusCode == 200) {
        return response.data;
      } else {
         throw Exception('Failed to load users: ${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }

  Future<List<dynamic>> getVaccines(String token) async {
    try {
      final response = await _dio.get('/auth/vaccines', options: Options(headers: {'Authorization': 'Bearer $token'}));
      if (response.statusCode == 200) {
        return response.data;
      } else {
        throw Exception('Failed to load vaccines: \\${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }

  Future<void> attachVaccinesToAnimal(String token, int animalId, List<Map<String, dynamic>> vaccines) async {
    try {
      final response = await _dio.post(
        '/auth/animals/$animalId/vaccines',
        data: {
          'vaccines': vaccines,
        },
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      if (response.statusCode != 200) {
        throw Exception('Failed to attach vaccines: \\${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }

  Future<List<dynamic>> getAnimalVaccinations(String token, int animalId) async {
    try {
      final response = await _dio.get('/auth/animals/$animalId', options: Options(headers: {'Authorization': 'Bearer $token'}));
      if (response.statusCode == 200) {
        return response.data['vaccinations'] ?? [];
      } else {
        throw Exception('Failed to load animal vaccinations: \\${response.statusCode}');
      }
    } catch (e) {
      print('Request failed: $e');
      throw Exception('Error: $e');
    }
  }
}
