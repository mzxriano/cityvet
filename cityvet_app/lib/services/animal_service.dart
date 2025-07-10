import 'package:cityvet_app/models/animal_model.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:dio/dio.dart';

class AnimalService {

  final AuthStorage storage = AuthStorage();

  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api/auth',
    headers: {
      'Accept': 'application/json',
    },
  ));

  Future<Response> createAnimal(AnimalModel animalModel) async {
    final token = await storage.getToken();

    final response = await _dio.post(
      '/animals', 
      data: animalModel.toJson(),
      options: Options(
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        }
      )
      );

    return response;

  }

  Future<Response> fetchAnimals() async {
    final token = await storage.getToken();
    print(token.toString());

    final response = await _dio.get(
      '/animals', 
      options: Options(
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      }
    )
    );
    return response;
  }

  Future<Response> editAnimal(String token, AnimalModel animalModel) async {
    print(animalModel.id);
    print(animalModel.birthDate == null);
    final response = await _dio.put(
      '/animals/${animalModel.id}',
      options: Options(
        headers: {
          'Authorization': 'Bearer $token',
        }
      ), 
      data: animalModel.toJson());
      
    return response;
  }

}