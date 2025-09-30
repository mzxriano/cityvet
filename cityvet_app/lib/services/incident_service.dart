import 'dart:io';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:dio/dio.dart';
import 'package:cityvet_app/models/incident_model.dart';

class IncidentService {
  final Dio _dio = Dio();

  IncidentService() {
    _dio.options.baseUrl = ApiConstant.baseUrl;
    _dio.options.connectTimeout = const Duration(seconds: 30);
    _dio.options.receiveTimeout = const Duration(seconds: 30);
  }

  Future<Map<String, dynamic>> reportIncident({
    required String victimName,
    required int age,
    required String species,
    required String biteProvocation,
    required double latitude,
    required double longitude,
    required String locationAddress,
    required DateTime incidentTime,
    String? remarks,
    File? photoFile,
  }) async {
    try {
      FormData formData = FormData.fromMap({
        'victim_name': victimName,
        'age': age,
        'species': species,
        'bite_provocation': biteProvocation,
        'latitude': latitude,
        'longitude': longitude,
        'location_address': locationAddress,
        'incident_time': incidentTime.toIso8601String(),
        'remarks': remarks ?? '',
      });

      // Add photo if provided
      if (photoFile != null) {
        formData.files.add(MapEntry(
          'photo',
          await MultipartFile.fromFile(
            photoFile.path,
            filename: 'incident_${DateTime.now().millisecondsSinceEpoch}.jpg',
          ),
        ));
      }

      final response = await _dio.post(
        '/api/incidents',
        data: formData,
        options: Options(
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'multipart/form-data',
          },
        ),
      );

      return {
        'success': response.statusCode == 201,
        'message': response.data['message'] ?? 'Incident reported successfully',
        'data': response.data['incident'] != null
            ? IncidentModel.fromJson(response.data['incident'])
            : null,
      };
    } on DioException catch (e) {
      String errorMessage = 'Failed to report incident';
      
      if (e.response != null) {
        if (e.response!.data != null && e.response!.data['message'] != null) {
          errorMessage = e.response!.data['message'];
        } else if (e.response!.data != null && e.response!.data['errors'] != null) {
          Map<String, dynamic> errors = e.response!.data['errors'];
          errorMessage = errors.values.first[0];
        }
      } else if (e.type == DioExceptionType.connectionTimeout ||
                 e.type == DioExceptionType.receiveTimeout) {
        errorMessage = 'Connection timeout. Please check your internet connection.';
      } else if (e.type == DioExceptionType.unknown) {
        errorMessage = 'Network error. Please check your internet connection.';
      }

      return {
        'success': false,
        'message': errorMessage,
        'data': null,
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'An unexpected error occurred: ${e.toString()}',
        'data': null,
      };
    }
  }

  Future<Map<String, dynamic>> getIncidents({
    int page = 1,
    int limit = 10,
    String? search,
    String? species,
    DateTime? fromDate,
    DateTime? toDate,
  }) async {
    try {
      Map<String, dynamic> queryParams = {
        'page': page,
        'limit': limit,
      };

      if (search != null && search.isNotEmpty) {
        queryParams['search'] = search;
      }
      if (species != null && species.isNotEmpty) {
        queryParams['species'] = species;
      }
      if (fromDate != null) {
        queryParams['from_date'] = fromDate.toIso8601String();
      }
      if (toDate != null) {
        queryParams['to_date'] = toDate.toIso8601String();
      }

      final response = await _dio.get(
        '/api/incidents',
        queryParameters: queryParams,
        options: Options(
          headers: {
            'Accept': 'application/json',
          },
        ),
      );

      List<IncidentModel> incidents = [];
      if (response.data['incidents'] != null) {
        incidents = (response.data['incidents'] as List)
            .map((json) => IncidentModel.fromJson(json))
            .toList();
      }

      return {
        'success': true,
        'message': 'Incidents retrieved successfully',
        'data': incidents,
        'total': response.data['total'] ?? 0,
        'current_page': response.data['current_page'] ?? 1,
        'total_pages': response.data['total_pages'] ?? 1,
      };
    } on DioException catch (e) {
      String errorMessage = 'Failed to retrieve incidents';
      
      if (e.response != null && e.response!.data != null && e.response!.data['message'] != null) {
        errorMessage = e.response!.data['message'];
      } else if (e.type == DioExceptionType.connectionTimeout ||
                 e.type == DioExceptionType.receiveTimeout) {
        errorMessage = 'Connection timeout. Please check your internet connection.';
      } else if (e.type == DioExceptionType.unknown) {
        errorMessage = 'Network error. Please check your internet connection.';
      }

      return {
        'success': false,
        'message': errorMessage,
        'data': <IncidentModel>[],
        'total': 0,
        'current_page': 1,
        'total_pages': 1,
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'An unexpected error occurred: ${e.toString()}',
        'data': <IncidentModel>[],
        'total': 0,
        'current_page': 1,
        'total_pages': 1,
      };
    }
  }

  Future<Map<String, dynamic>> getIncidentById(int id) async {
    try {
      final response = await _dio.get(
        '/api/incidents/$id',
        options: Options(
          headers: {
            'Accept': 'application/json',
          },
        ),
      );

      return {
        'success': true,
        'message': 'Incident retrieved successfully',
        'data': response.data['incident'] != null
            ? IncidentModel.fromJson(response.data['incident'])
            : null,
      };
    } on DioException catch (e) {
      String errorMessage = 'Failed to retrieve incident';
      
      if (e.response != null && e.response!.data != null && e.response!.data['message'] != null) {
        errorMessage = e.response!.data['message'];
      }

      return {
        'success': false,
        'message': errorMessage,
        'data': null,
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'An unexpected error occurred: ${e.toString()}',
        'data': null,
      };
    }
  }

  Future<Map<String, dynamic>> getIncidentStatistics() async {
    try {
      final response = await _dio.get(
        '/api/incidents/statistics',
        options: Options(
          headers: {
            'Accept': 'application/json',
          },
        ),
      );

      return {
        'success': true,
        'message': 'Statistics retrieved successfully',
        'data': response.data,
      };
    } on DioException catch (e) {
      String errorMessage = 'Failed to retrieve statistics';
      
      if (e.response != null && e.response!.data != null && e.response!.data['message'] != null) {
        errorMessage = e.response!.data['message'];
      }

      return {
        'success': false,
        'message': errorMessage,
        'data': {},
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'An unexpected error occurred: ${e.toString()}',
        'data': {},
      };
    }
  }
}
