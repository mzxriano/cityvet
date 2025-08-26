import 'package:cityvet_app/utils/api_constant.dart';
import 'package:dio/dio.dart';

class AewService { 

  final Dio _dio = Dio(BaseOptions(
    baseUrl: ApiConstant.baseUrl,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
    connectTimeout: const Duration(seconds: 30),
    receiveTimeout: const Duration(seconds: 30),
  ));

  Future<Response> fetchAEWUsers(String token) async {
    final response = await _dio.get('/aew', options: Options(
      headers: {'Authorization' : 'Bearer $token'}, 
    ));

    return response;
  }

}