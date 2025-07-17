import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class AuthStorage {
  final FlutterSecureStorage _storage = FlutterSecureStorage();

  static const String _keyAuthToken = 'auth_token';
  static const String _keyUserId = 'user_id';

  Future<void> saveToken(String token) async {
    await _storage.write(key: _keyAuthToken, value: token);
  }

  Future<String?> getToken() async {
    return await _storage.read(key: _keyAuthToken);
  }

  Future<void> deleteToken() async {
    await _storage.delete(key: _keyAuthToken);
  }

  Future<void> clearAll() async {
    await _storage.deleteAll();
  }

  Future<void> saveUserId(String userId) async {
    await _storage.write(key: _keyUserId, value: userId);
  }

  Future<String?> getUserId() async {
    return await _storage.read(key: _keyUserId);
  }

}