import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:cityvet_app/models/incident_model.dart';
import 'package:cityvet_app/services/incident_service.dart';

class IncidentViewModel extends ChangeNotifier {
  final IncidentService _incidentService = IncidentService();

  List<IncidentModel> _incidents = [];
  bool _isLoading = false;
  String? _error;
  int _currentPage = 1;
  int _totalPages = 1;
  int _totalIncidents = 0;

  List<IncidentModel> get incidents => _incidents;
  bool get isLoading => _isLoading;
  String? get error => _error;
  int get currentPage => _currentPage;
  int get totalPages => _totalPages;
  int get totalIncidents => _totalIncidents;

  bool get hasNextPage => _currentPage < _totalPages;
  bool get hasPreviousPage => _currentPage > 1;

  void _setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  void _setError(String? error) {
    _error = error;
    notifyListeners();
  }

  Future<bool> reportIncident({
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
    _setLoading(true);
    _setError(null);

    try {
      final result = await _incidentService.reportIncident(
        victimName: victimName,
        age: age,
        species: species,
        biteProvocation: biteProvocation,
        latitude: latitude,
        longitude: longitude,
        locationAddress: locationAddress,
        incidentTime: incidentTime,
        remarks: remarks,
        photoFile: photoFile,
      );

      if (result['success']) {
        // Refresh incidents list to include the new incident
        await loadIncidents(refresh: true);
        return true;
      } else {
        _setError(result['message']);
        return false;
      }
    } catch (e) {
      _setError('Failed to report incident: ${e.toString()}');
      return false;
    } finally {
      _setLoading(false);
    }
  }

  Future<void> loadIncidents({
    bool refresh = false,
    String? search,
    String? species,
    DateTime? fromDate,
    DateTime? toDate,
  }) async {
    if (refresh) {
      _currentPage = 1;
      _incidents.clear();
    }

    _setLoading(true);
    _setError(null);

    try {
      final result = await _incidentService.getIncidents(
        page: _currentPage,
        search: search,
        species: species,
        fromDate: fromDate,
        toDate: toDate,
      );

      if (result['success']) {
        List<IncidentModel> newIncidents = result['data'];
        
        if (refresh) {
          _incidents = newIncidents;
        } else {
          _incidents.addAll(newIncidents);
        }
        
        _totalIncidents = result['total'];
        _currentPage = result['current_page'];
        _totalPages = result['total_pages'];
      } else {
        _setError(result['message']);
      }
    } catch (e) {
      _setError('Failed to load incidents: ${e.toString()}');
    } finally {
      _setLoading(false);
    }
  }

  Future<void> loadMoreIncidents() async {
    if (!hasNextPage || _isLoading) return;
    
    _currentPage++;
    await loadIncidents();
  }

  Future<IncidentModel?> getIncidentById(int id) async {
    _setLoading(true);
    _setError(null);

    try {
      final result = await _incidentService.getIncidentById(id);

      if (result['success']) {
        return result['data'] as IncidentModel?;
      } else {
        _setError(result['message']);
        return null;
      }
    } catch (e) {
      _setError('Failed to load incident: ${e.toString()}');
      return null;
    } finally {
      _setLoading(false);
    }
  }

  Future<Map<String, dynamic>?> getStatistics() async {
    _setLoading(true);
    _setError(null);

    try {
      final result = await _incidentService.getIncidentStatistics();

      if (result['success']) {
        return result['data'] as Map<String, dynamic>?;
      } else {
        _setError(result['message']);
        return null;
      }
    } catch (e) {
      _setError('Failed to load statistics: ${e.toString()}');
      return null;
    } finally {
      _setLoading(false);
    }
  }

  void clearError() {
    _setError(null);
  }

  void refresh() {
    loadIncidents(refresh: true);
  }

  // Filter incidents locally
  List<IncidentModel> filterIncidents({
    String? victimName,
    String? species,
    String? biteProvocation,
    DateTime? fromDate,
    DateTime? toDate,
  }) {
    List<IncidentModel> filtered = List.from(_incidents);

    if (victimName != null && victimName.isNotEmpty) {
      filtered = filtered.where((incident) => 
        incident.victimName.toLowerCase().contains(victimName.toLowerCase())
      ).toList();
    }

    if (species != null && species.isNotEmpty) {
      filtered = filtered.where((incident) => 
        incident.species.toLowerCase().contains(species.toLowerCase())
      ).toList();
    }

    if (biteProvocation != null && biteProvocation.isNotEmpty) {
      filtered = filtered.where((incident) => 
        incident.biteProvocation.toLowerCase().contains(biteProvocation.toLowerCase())
      ).toList();
    }

    if (fromDate != null) {
      filtered = filtered.where((incident) => 
        incident.incidentTime.isAfter(fromDate) || 
        incident.incidentTime.isAtSameMomentAs(fromDate)
      ).toList();
    }

    if (toDate != null) {
      filtered = filtered.where((incident) => 
        incident.incidentTime.isBefore(toDate.add(const Duration(days: 1)))
      ).toList();
    }

    return filtered;
  }

  // Get incidents by species for statistics
  Map<String, int> getIncidentsBySpecies() {
    Map<String, int> speciesCount = {};
    
    for (IncidentModel incident in _incidents) {
      String species = incident.species.toLowerCase();
      speciesCount[species] = (speciesCount[species] ?? 0) + 1;
    }
    
    return speciesCount;
  }

  // Get incidents by provocation type
  Map<String, int> getIncidentsByProvocation() {
    Map<String, int> provocationCount = {};
    
    for (IncidentModel incident in _incidents) {
      String provocation = incident.biteProvocation;
      provocationCount[provocation] = (provocationCount[provocation] ?? 0) + 1;
    }
    
    return provocationCount;
  }

  // Get recent incidents (last 30 days)
  List<IncidentModel> getRecentIncidents() {
    DateTime thirtyDaysAgo = DateTime.now().subtract(const Duration(days: 30));
    
    return _incidents.where((incident) => 
      incident.incidentTime.isAfter(thirtyDaysAgo)
    ).toList();
  }
}
