import 'dart:io';

import 'package:cityvet_app/models/user_model.dart';
import 'package:dio/dio.dart';

class UserService {

Future<Map<String, dynamic>> fetchUser(String token) async {

  final Dio dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api/auth',
    headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    },
  ));

  final user = await dio.get('/user');

  print(user.data);

  return user.data;

}

Future<Response> editProfile(String token, UserModel user, {File? imageFile}) async {
  final Dio dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api/auth',
    headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    },
  ));

  dynamic requestData;

      if (imageFile != null) {
      // Use FormData for image upload
      FormData formData = FormData.fromMap({
        'first_name': user.firstName,
        'last_name': user.lastName,
        'phone_number': user.phoneNumber,
        'barangay_id': user.barangay?.id,
        'street': user.street,
        'birth_date': user.birthDate,
      });
      
      // Add image
      formData.files.add(MapEntry('image', await MultipartFile.fromFile(imageFile.path)));
            
      requestData = formData;
    } else {
      // Use regular form data (no image)
      requestData = user.toJson();
      
    }

  final response = await dio.post('/user/edit', data: requestData);

  print('user response ${response.data}');

  return response;
}

}