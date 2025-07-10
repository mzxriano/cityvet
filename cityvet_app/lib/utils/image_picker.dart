import 'dart:io';

import 'package:image_picker/image_picker.dart';

class CustomImagePicker {

  final ImagePicker imagePicker = ImagePicker();

  Future<File?> pickFromGallery() async {
    final pickedFile = await imagePicker.pickImage(source: ImageSource.gallery);

    if(pickedFile == null) return null;

    print('path : ${pickedFile.path}');

    return File(pickedFile.path);
  }

  Future<File?> pickFromCamera() async {
    final pickedFile = await imagePicker.pickImage(source: ImageSource.camera);

    if(pickedFile == null) return null;

    return File(pickedFile.path);
  }

}