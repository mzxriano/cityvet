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
        '/incidents/report',
        data: formData,
        options: Options(
          headers: {
            'Content-Type': 'multipart/form-data',
            'Accept': 'application/json',
          },
        ),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        return {
          'success': true,
          'message': response.data['message'] ?? 'Incident reported successfully',
          'data': response.data['data'] ?? {},
        };
      } else {
        return {
          'success': false,
          'message': response.data['message'] ?? 'Failed to report incident',
          'data': {},
        };
      }
    } on DioException catch (dioError) {
      String errorMessage = 'Network error occurred';
      
      if (dioError.response != null) {
        switch (dioError.response!.statusCode) {
          case 400:
            errorMessage = dioError.response!.data['message'] ?? 'Bad request';
            break;
          case 401:
            errorMessage = 'Unauthorized access';
            break;
          case 403:
            errorMessage = 'Access denied';
            break;
          case 422:
            final errors = dioError.response!.data['errors'] as Map<String, dynamic>?;
            if (errors != null) {
              errorMessage = errors.values.first.first;
            } else {
              errorMessage = dioError.response!.data['message'] ?? 'Validation failed';
            }
            break;
          case 500:
            errorMessage = 'Server error occurred';
            break;
          default:
            errorMessage = dioError.response!.data['message'] ?? 'Unknown error occurred';
        }
      } else if (dioError.type == DioExceptionType.connectionTimeout) {
        errorMessage = 'Connection timeout';
      } else if (dioError.type == DioExceptionType.receiveTimeout) {
        errorMessage = 'Receive timeout';
      } else if (dioError.type == DioExceptionType.connectionError) {
        errorMessage = 'No internet connection';
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

  // Fetch incidents for barangay personnel
  Future<Map<String, dynamic>> fetchIncidentsForBarangay() async {
    try {
      final response = await _dio.get(
        '/incidents/barangay',
        options: Options(
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        ),
      );

      if (response.statusCode == 200) {
        List<dynamic> incidentsData = response.data['data'] ?? [];
        List<IncidentModel> incidents = incidentsData
            .map((incident) => IncidentModel.fromJson(incident))
            .toList();

        return {
          'success': true,
          'message': 'Incidents fetched successfully',
          'data': incidents,
        };
      } else {
        return {
          'success': false,
          'message': response.data['message'] ?? 'Failed to fetch incidents',
          'data': [],
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'An unexpected error occurred: ${e.toString()}',
        'data': [],
      };
    }
  }

  // Update incident status
  Future<Map<String, dynamic>> updateIncidentStatus({
    required int incidentId,
    required String status,
  }) async {
    try {
      final response = await _dio.put(
        '/incidents/$incidentId/status',
        data: {'status': status},
        options: Options(
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        ),
      );

      if (response.statusCode == 200) {
        return {
          'success': true,
          'message': response.data['message'] ?? 'Status updated successfully',
          'data': response.data['data'] ?? {},
        };
      } else {
        return {
          'success': false,
          'message': response.data['message'] ?? 'Failed to update status',
          'data': {},
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'An unexpected error occurred: ${e.toString()}',
        'data': {},
      };
    }
  }

  // Get incident details
  Future<Map<String, dynamic>> getIncidentDetails(int incidentId) async {
    try {
      final response = await _dio.get(
        '/incidents/$incidentId',
        options: Options(
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        ),
      );

      if (response.statusCode == 200) {
        IncidentModel incident = IncidentModel.fromJson(response.data['data']);
        return {
          'success': true,
          'message': 'Incident details fetched successfully',
          'data': incident,
        };
      } else {
        return {
          'success': false,
          'message': response.data['message'] ?? 'Failed to fetch incident details',
          'data': null,
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'An unexpected error occurred: ${e.toString()}',
        'data': null,
      };
    }
  }
}
