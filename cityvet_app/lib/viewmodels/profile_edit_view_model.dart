import 'package:cityvet_app/models/barangay_model.dart';
import 'package:cityvet_app/models/user_model.dart';
import 'package:cityvet_app/services/auth_service.dart';
import 'package:cityvet_app/services/user_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:flutter/material.dart';

class ProfileEditViewModel extends ChangeNotifier {
  final _authService = AuthService();
  final AuthStorage _storage = AuthStorage();

  String? _selectedBarangay;
  String? _error;
  DateTime? _selectedDate;
  String? _formattedBDate;
  List<BarangayModel> _barangays = [];
  bool _isLoading = false;
  bool _isInitialized = false;
  UserModel? _user;
  bool _isSuccessful = false;


  // Constructor that accepts user data
  ProfileEditViewModel(UserModel? user) {
    if (user != null) {
      // Initialize with user data immediately
      _initializeWithUser(user);
    }
  }

  // Getters
  String? get selectedBarangay => _selectedBarangay;
  String? get error => _error;
  DateTime? get selectedDate => _selectedDate;
  String? get formattedBDate => _formattedBDate;
  List<BarangayModel>? get barangays => _barangays;
  bool get isLoading => _isLoading;
  bool get isInitialized => _isInitialized;
  UserModel? get user => _user;
  bool get isSuccessful => _isSuccessful;

  // Private method to initialize with user data
  Future<void> _initializeWithUser(UserModel user) async {
    if (_isInitialized) return;
    
    _isLoading = true;
    notifyListeners();

    try {
      // First fetch barangays
      await fetchBarangays();
      
      // Then set the selected barangay - make sure it exists in the list
      // if (user.barangay != null && user.barangay!.isNotEmpty) {
      //   // Check if the user's barangay exists in the fetched barangays
      //   final barangayExists = _barangays.any((barangay) => 
      //     barangay.id.toString() == user.barangay);
        
      //   if (barangayExists) {
      //     _selectedBarangay = user.barangay;
      //   } else {
      //     // If user's barangay doesn't exist in the list, set to null
      //     _selectedBarangay = null;
      //     print('User barangay ${user.barangay} not found in barangay list');
      //   }
      // }
      
      // Set birth date if available
      // if (user.birthDate != null && user.birthDate!.isNotEmpty) {
      //   try {
      //     _selectedDate = DateTime.parse(user.birthDate!);
      //     setFormatBirthDate();
      //   } catch (e) {
      //     print('Error parsing birth date: $e');
      //   }
      // }
      
      _isInitialized = true;
    } catch (e) {
      print('Error initializing user data: $e');
      setError('Failed to initialize user data.');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Public method for manual initialization (kept for backward compatibility)
  Future<void> initializeUserData(UserModel user) async {
    await _initializeWithUser(user);
  }

  setBarangay(String? barangay) {
    _selectedBarangay = barangay;
    notifyListeners();
  }

  setError(String error) {
    _error = error;
    notifyListeners();
  }

  setBirthDate(DateTime? date) {
    _selectedDate = date;
    setFormatBirthDate();
    notifyListeners();
  }

  setFormatBirthDate() {
    if (_selectedDate != null) {
      _formattedBDate =
          '${selectedDate!.year}-${selectedDate!.month.toString().padLeft(2, '0')}-${selectedDate!.day.toString().padLeft(2, '0')}';
      notifyListeners();
    }
  }

  setBarangays(List<BarangayModel> barangays) {
    _barangays = barangays;
    notifyListeners();
  }

  setUser(UserModel user){
    _user = user;
    notifyListeners();
  }

  setSuccessful(bool isSuccessful) {
    _isSuccessful = isSuccessful;
    notifyListeners();
  }

  Future<void> fetchBarangays() async {
    try {
      final response = await _authService.getBarangays();
      final List<dynamic> barangayList = response.data;
      final barangayModels = barangayList.map((json) => BarangayModel.fromJson(json)).toList();
      setBarangays(barangayModels);
    } catch (e) {
      print('Error fetching barangays: $e');
      setError('Failed to load barangays.');
      throw e; // Re-throw to handle in initializeUserData
    }
  }

  Future<void> editProfile(
    String? firstName,
    String? lastName,
    String? email,
    String? phoneNumber,
    String? barangay,
    String? street,
  ) async {
    try {
      _isLoading = true;
      notifyListeners();

      final token = await _storage.getToken();

      if (token == null) {
        throw Exception("Token not found");
      }

      final user = UserModel(
        firstName: firstName,
        lastName: lastName,
        email: email,
        phoneNumber: phoneNumber,
        barangay: barangay,
        street: street,
      );

      final response = await UserService().editProfile(token, user);

      final UserModel updatedUser = UserModel.fromJson(response.data['user']);
      print(response.data);

      setUser(updatedUser);
      setSuccessful(true);

    } catch (e) {
      setError("Failed to edit profile: $e");
      setSuccessful(false);
      throw Exception("Failed to edit profile: $e");
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Method to clear any errors
  void clearError() {
    _error = null;
    notifyListeners();
  }

  // Method to reset the view model state
  void reset() {
    _selectedBarangay = null;
    _error = null;
    _selectedDate = null;
    _formattedBDate = null;
    _barangays = [];
    _isLoading = false;
    _isInitialized = false;
    notifyListeners();
  }
}