import 'dart:io';
import 'package:image_picker/image_picker.dart';

class CustomImagePicker {
  final ImagePicker imagePicker = ImagePicker();
  
  bool _isPickingImage = false;
  
  Future<File?> pickFromGallery() async {
    if (_isPickingImage) return null;
    
    _isPickingImage = true;
    
    try {
      final pickedFile = await imagePicker.pickImage(source: ImageSource.gallery);
      
      if (pickedFile == null) return null;
      
      print('path : ${pickedFile.path}');
      return File(pickedFile.path);
    } catch (e) {
      print('Error picking image from gallery: $e');
      return null;
    } finally {
      _isPickingImage = false;
    }
  }
  
  Future<File?> pickFromCamera() async {
    if (_isPickingImage) return null;
    
    _isPickingImage = true;
    
    try {
      final pickedFile = await imagePicker.pickImage(source: ImageSource.camera);
      
      if (pickedFile == null) return null;
      
      print('path : ${pickedFile.path}');
      return File(pickedFile.path);
    } catch (e) {
      print('Error picking image from camera: $e');
      return null;
    } finally {
      _isPickingImage = false;
    }
  }

  Future<List<File>?> pickMultipleImages() async {
    final picker = ImagePicker();
    final pickedFiles = await picker.pickMultiImage();
    List<File> selectedImages = [];

    if (pickedFiles.isNotEmpty) {
        for (final pickedFile in pickedFiles) {
          final file = File(pickedFile.path);
          // Check if file exists and has size > 0
          if (await file.exists() && await file.length() > 0) {
            selectedImages.add(file);
            print('Valid image selected: ${pickedFile.path}, size: ${await file.length()}');
          } else {
            print('Invalid or empty image skipped: ${pickedFile.path}');
          }
        }
        return selectedImages.isNotEmpty ? selectedImages : null;
    }

    return null;
  }

}