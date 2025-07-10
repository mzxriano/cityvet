import 'dart:io';

import 'package:cityvet_app/models/auth_model.dart';
import 'package:cityvet_app/models/barangay_model.dart';
import 'package:cityvet_app/services/auth_service.dart';
import 'package:cityvet_app/utils/dio_exception_handler.dart';
import 'package:cityvet_app/utils/image_picker.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class SignupViewModel extends ChangeNotifier {
  final _authService = AuthService();

  Map<String, dynamic> _fieldErrors = {};
  bool _isPasswordObscured = true;
  bool _isConfirmPasswordObscured = true;
  bool _success = false;
  String? _successMessage;
  String? _selectedBarangay;
  String? _error;
  DateTime? _selectedDate;
  String? _formattedBDate;
  List<BarangayModel> _barangays = [];
  File? _profileImage;

  get fieldErrors => _fieldErrors;
  bool get isPasswordObscured => _isPasswordObscured;
  bool get isConfirmPasswordObscured => _isConfirmPasswordObscured;
  bool get success => _success;
  String? get successMessage => _successMessage;
  String? get selectedBarangay => _selectedBarangay;
  String? get error => _error;
  DateTime? get selectedDate => _selectedDate;
  String? get formattedBDate => _formattedBDate;
  List<BarangayModel>? get barangays => _barangays; 
  File? get profileImage => _profileImage;
 
  setFieldErrors(Map<String, dynamic> fieldErrors) {
    _fieldErrors = fieldErrors;
    notifyListeners();
  }

  setSuccess(bool success) {
    _success = success;
    notifyListeners();
  }

  setPasswordObscured(bool isPasswordObscured) {
    _isPasswordObscured = isPasswordObscured;
    notifyListeners();
  }

  setConfirmPasswordObscured(bool isConfirmPasswordObscured) {
    _isConfirmPasswordObscured = isConfirmPasswordObscured;
    notifyListeners();
  }

  setSuccessMessage(String? message) {
    _successMessage = message;
    notifyListeners();
  }

  setBarangay(String? barangay) {
    _selectedBarangay = barangay;
    notifyListeners();
  }

  setError(String error) {
    _error = error;
    notifyListeners();
  }
 
  void clearErrors() {
    _fieldErrors.clear();
    notifyListeners();
  }

  setBirthDate(DateTime? date) {
    _selectedDate = date;
    setFormatBirthDate();
    notifyListeners();
  }

  setFormatBirthDate() {
    if(_selectedDate != null) {
      _formattedBDate = 
        '${selectedDate!.year}-${selectedDate!.month.toString().padLeft(2, '0')}-${selectedDate!.day.toString().padLeft(2, '0')}'; 
        notifyListeners();
    }
  }

  setBarangays(List<BarangayModel> barangays) {
    _barangays = barangays;
    notifyListeners();
  }

  setProfile(File image) {
    _profileImage = image;
    notifyListeners();
  }

  String? getFieldError(String fieldName) {
    final error = _fieldErrors[fieldName];
    if (error == null) return null;
    
    if (error is List && error.isNotEmpty) {
      return error.first.toString();
    }
    
    return error.toString();
  }

Future<void> register({
  required String firstName,
  required String lastName,
  required String birthDate,
  required String phoneNumber,
  required int barangay_id,
  required String street,
  required String email,
  required String password,
  required String passwordConfirmation,
}) async {
  final AuthModel authModel = AuthModel(
    firstName: firstName,
    lastName: lastName,
    birthDate: birthDate,
    phoneNumber: phoneNumber,
    email: email,
    password: password,
    barangay_id: barangay_id,
    street: street,
  );


  try {
    final data = await _authService.register(authModel, passwordConfirmation);

    if (data == null) return;

    if (data.containsKey('message')) {
      setSuccess(true);
      setSuccessMessage(data['message'].toString());
    }

  } on DioException catch (e) {
    final data = e.response?.data;
    print(data);

    if (data != null && data['errors'] != null) {
      setFieldErrors(Map<String, dynamic>.from(data['errors']));
    } else {
      setError(DioExceptionHandler.handleException(e));
    }

  } catch (e) {
    setError('An unexpected error occurred.');
  }
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
    }
  }

  Future<void> pickImageFromGallery() async {
    final pickedImage = await CustomImagePicker().pickFromGallery();
    if(pickedImage == null) return;
    setProfile(pickedImage);
  }


}
