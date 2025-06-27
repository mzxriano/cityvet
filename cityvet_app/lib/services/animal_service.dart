import 'package:cityvet_app/models/animal_model.dart';
import 'package:dio/dio.dart';

class AnimalService {

  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api',
    headers: {
      'Accept': 'application/json',
    },
  ));

  Future<String> createAnimal(AnimalModel animalModel) async {
    final response = await _dio.post('/create-animal', data:  {
      animalModel.toJson()
    });

    return response.data['message'];

  }

}