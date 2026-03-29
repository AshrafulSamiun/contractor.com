import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';

class ApiResponse {
  final bool success;
  final dynamic data;
  final String? message;
  final Map<String, dynamic>? errors;
  final bool verificationRequired;
  final int statusCode;
  final Map<String, dynamic> raw;

  ApiResponse({
    required this.success,
    this.data,
    this.message,
    this.errors,
    this.verificationRequired = false,
    this.statusCode = 200,
    Map<String, dynamic>? raw,
  }) : raw = raw ?? const {};

  factory ApiResponse.fromJson(Map<String, dynamic> json, {int statusCode = 200}) {
    return ApiResponse(
      success: json['success'] == true,
      data: json['data'],
      message: json['message']?.toString(),
      errors: json['errors'] as Map<String, dynamic>?,
      verificationRequired: json['verification_required'] == true,
      statusCode: statusCode,
      raw: json,
    );
  }
}

class ApiClient {
  ApiClient({String? token}) : _token = token;

  final String? _token;

  Uri _uri(String path) {
    final clean = path.startsWith('/') ? path : '/$path';
    return Uri.parse('${AppConfig.apiBaseUrl}$clean');
  }

  Map<String, String> _headers() {
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (_token != null && _token!.isNotEmpty) {
      headers['Authorization'] = 'Bearer $_token';
    }
    return headers;
  }

  Future<ApiResponse> get(String path) async {
    final res = await http.get(_uri(path), headers: _headers());
    return _parse(res);
  }

  Future<ApiResponse> post(String path, Map<String, dynamic> body) async {
    final res = await http.post(
      _uri(path),
      headers: _headers(),
      body: jsonEncode(body),
    );
    return _parse(res);
  }

  ApiResponse _parse(http.Response res) {
    final Map<String, dynamic> jsonBody =
        res.body.isNotEmpty ? jsonDecode(res.body) : <String, dynamic>{};
    final response = ApiResponse.fromJson(jsonBody, statusCode: res.statusCode);
    if (res.statusCode >= 400 && response.message == null) {
      return ApiResponse(
        success: false,
        data: response.data,
        message: 'Request failed (${res.statusCode})',
        errors: response.errors,
        verificationRequired: response.verificationRequired,
        statusCode: res.statusCode,
        raw: jsonBody,
      );
    }
    return response;
  }
}
