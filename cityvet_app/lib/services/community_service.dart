import 'dart:io';
import 'package:dio/dio.dart';

class CommunityService {
  final Dio dio = Dio(BaseOptions(
    baseUrl: 'http://192.168.1.109:8000/api/auth',
    headers: {
      'Accept': 'application/json',
    },
  ));

  Future<Response> fetchPosts(String token) async {
    return await dio.get(
      '/community',
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );
  }

  Future<Response> createPost({required String content, required List<File> images, required String token}) async {
    final formData = FormData();
    formData.fields.add(MapEntry('content', content));
    for (var image in images) {
      formData.files.add(MapEntry(
        'images[]',
        await MultipartFile.fromFile(image.path, filename: image.path.split('/').last),
      ));
    }
    return await dio.post(
      '/community',
      data: formData,
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );
  }

  Future<Response> likePost(int postId, String token) async {
    return await dio.post(
      '/community/$postId/like',
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );
  }

  Future<Response> fetchComments(int postId, String token) async {
    return await dio.get(
      '/community/$postId/comments',
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );
  }

  Future<Response> addComment({
    required int postId,
    required String content,
    String? parentId,
    required String token,
  }) async {
    final data = {'content': content};
    if (parentId != null) {
      data['parent_id'] = parentId;
    }
    return await dio.post(
      '/community/$postId/comment',
      data: data,
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );
  }
} 