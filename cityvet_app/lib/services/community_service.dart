import 'dart:io';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:dio/dio.dart';

class CommunityService {
  final Dio dio = Dio(BaseOptions(
    baseUrl: ApiConstant.baseUrl,
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

  Future<Response> fetchUserPosts(String token) async {
    return await dio.get(
      '/community/user',
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

  Future<Response> updatePost({
    required int postId,
    required String content,
    required List<File> images,
    required String token,
  }) async {
    // If no images, send as JSON
    if (images.isEmpty) {
      print('Sending JSON update request:');
      print('Content: $content');
      
      return await dio.patch(
        '/community/$postId',
        data: {'content': content},
        options: Options(
          headers: {
            'Authorization': 'Bearer $token',
            'Content-Type': 'application/json',
          },
        ),
      );
    }
    
    // If images, send as FormData
    final formData = FormData();
    formData.fields.add(MapEntry('content', content));
    
    for (var image in images) {
      formData.files.add(MapEntry(
        'images[]',
        await MultipartFile.fromFile(image.path, filename: image.path.split('/').last),
      ));
    }
    
    print('Sending FormData update request:');
    print('Content: $content');
    print('Images count: ${images.length}');
    
    return await dio.patch(
      '/community/$postId',
      data: formData,
      options: Options(
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'multipart/form-data',
        },
      ),
    );
  }

  Future<Response> deletePost(int postId, String token) async {
    return await dio.delete(
      '/community/$postId',
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );
  }
} 