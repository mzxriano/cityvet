import 'package:cityvet_app/utils/text.dart';
import 'package:dio/dio.dart';

class DioExceptionHandler {

  static String handleException(DioException e) {

    switch(e.type) {
      case DioExceptionType.connectionTimeout:
        print(AppText.connectionTimeout);
        return AppText.connectionTimeout;
      case DioExceptionType.sendTimeout:
        print(AppText.requestTimeout);
        return AppText.requestTimeout;
      case DioExceptionType.receiveTimeout:
        print(AppText.responseTimeout);
        return AppText.responseTimeout;
      case DioExceptionType.cancel:
        print(AppText.requestCancelled);
        return AppText.requestCancelled;
      case DioExceptionType.badCertificate:
        print(AppText.badCertificate);
        return AppText.badCertificate;
      case DioExceptionType.connectionError:
        print(AppText.connectionError);
        return AppText.connectionError;
      default:
        print(AppText.somethingWrong);
        return AppText.somethingWrong;
    }
  }


}