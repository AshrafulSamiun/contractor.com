import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../core/api_client.dart';

class AuthResult {
  final bool success;
  final bool verificationRequired;
  final String? message;

  const AuthResult({
    required this.success,
    this.verificationRequired = false,
    this.message,
  });
}

class AuthProvider extends ChangeNotifier {
  String? _token;
  Map<String, dynamic>? _user;
  bool _loading = false;
  String? _verifySession;
  String _verifyVia = 'email';

  String? get token => _token;
  Map<String, dynamic>? get user => _user;
  bool get isAuthenticated => _token != null && _token!.isNotEmpty;
  bool get loading => _loading;
  String? get verifySession => _verifySession;
  String get verifyVia => _verifyVia;

  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('pm_token');
    _verifySession = prefs.getString('pm_verify_session');
    _verifyVia = prefs.getString('pm_verify_via') ?? 'email';
    notifyListeners();
  }

  ApiClient client() => ApiClient(token: _token);

  Future<AuthResult> login(
    String login,
    String password,
    String captchaKey,
    String captchaValue,
  ) async {
    _loading = true;
    notifyListeners();
    try {
      final res = await client().post('/login', {
        'login': login,
        'password': password,
        'captcha_key': captchaKey,
        'captcha_value': captchaValue,
      });
      if (res.verificationRequired) {
        await _setVerifySession(res);
        return const AuthResult(success: true, verificationRequired: true);
      }
      if (res.success && res.data is Map && res.data['token'] != null) {
        _token = res.data['token'].toString();
        await _saveToken(_token!);
        _user = res.data['user'] as Map<String, dynamic>?;
        return const AuthResult(success: true);
      }
      return AuthResult(success: false, message: res.message ?? 'Login failed.');
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<AuthResult> register(Map<String, dynamic> payload) async {
    _loading = true;
    notifyListeners();
    try {
      final res = await client().post('/register', payload);
      if (res.verificationRequired) {
        await _setVerifySession(res);
        return const AuthResult(success: true, verificationRequired: true);
      }
      if (res.success && res.data is Map && res.data['token'] != null) {
        _token = res.data['token'].toString();
        await _saveToken(_token!);
        _user = res.data['user'] as Map<String, dynamic>?;
        return const AuthResult(success: true);
      }
      return AuthResult(success: false, message: res.message ?? 'Registration failed.');
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<AuthResult> verifyCode(String code) async {
    if (_verifySession == null || _verifySession!.isEmpty) {
      return const AuthResult(success: false, message: 'Verification session expired.');
    }
    _loading = true;
    notifyListeners();
    try {
      final res = await client().post('/verify', {
        'verify_session': _verifySession,
        'code': code,
      });
      if (res.success && res.data is Map && res.data['token'] != null) {
        _token = res.data['token'].toString();
        await _saveToken(_token!);
        _user = res.data['user'] as Map<String, dynamic>?;
        await clearVerifySession();
        return const AuthResult(success: true);
      }
      return AuthResult(success: false, message: res.message ?? 'Verification failed.');
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<AuthResult> resendCode() async {
    if (_verifySession == null || _verifySession!.isEmpty) {
      return const AuthResult(success: false, message: 'Verification session expired.');
    }
    final res = await client().post('/verify/resend', {
      'verify_session': _verifySession,
    });
    if (res.success) {
      return const AuthResult(success: true);
    }
    return AuthResult(success: false, message: res.message ?? 'Failed to resend.');
  }

  Future<void> loadMe() async {
    if (_token == null) return;
    final res = await client().get('/me');
    if (res.success && res.data is Map) {
      _user = res.data as Map<String, dynamic>;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    if (_token == null) return;
    await client().post('/logout', {});
    await clear();
  }

  Future<void> clear() async {
    _token = null;
    _user = null;
    await clearVerifySession();
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('pm_token');
    notifyListeners();
  }

  Future<void> _saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('pm_token', token);
  }

  Future<void> _setVerifySession(ApiResponse res) async {
    if (res.data is Map) {
      _verifySession = res.data['verify_session']?.toString();
      _verifyVia = res.data['verify_via']?.toString() ?? 'email';
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('pm_verify_session', _verifySession ?? '');
      await prefs.setString('pm_verify_via', _verifyVia);
      notifyListeners();
    }
  }

  Future<void> clearVerifySession() async {
    _verifySession = null;
    _verifyVia = 'email';
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('pm_verify_session');
    await prefs.remove('pm_verify_via');
  }
}
