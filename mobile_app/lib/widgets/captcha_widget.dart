import 'dart:convert';
import 'package:flutter/material.dart';
import '../core/api_client.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class CaptchaWidget extends StatefulWidget {
  const CaptchaWidget({super.key, required this.onChanged});

  final void Function(String key, String value) onChanged;

  @override
  State<CaptchaWidget> createState() => _CaptchaWidgetState();
}

class _CaptchaWidgetState extends State<CaptchaWidget> {
  String _captchaKey = '';
  String _captchaImg = '';
  bool _loading = false;
  final _controller = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadCaptcha();
  }

  Future<void> _loadCaptcha() async {
    setState(() => _loading = true);
    try {
      final api = context.read<AuthProvider>().client();
      final res = await api.get('/captcha');
      if (res.success && res.data is Map) {
        final data = res.data as Map<String, dynamic>;
        _captchaKey = data['key']?.toString() ?? '';
        _captchaImg = _extractImage(data['img']?.toString() ?? '');
        _controller.clear();
        widget.onChanged(_captchaKey, '');
      }
    } finally {
      setState(() => _loading = false);
    }
  }

  String _extractImage(String raw) {
    if (raw.contains('data:image')) return raw;
    final match = RegExp(r'src=\"([^\"]+)\"').firstMatch(raw);
    return match?.group(1) ?? raw;
  }

  ImageProvider? _imageProvider() {
    if (_captchaImg.isEmpty) return null;
    if (_captchaImg.startsWith('data:image')) {
      final base64Data = _captchaImg.split(',').last;
      return MemoryImage(base64Decode(base64Data));
    }
    return NetworkImage(_captchaImg);
  }

  @override
  Widget build(BuildContext context) {
    final imgProvider = _imageProvider();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              width: 160,
              height: 48,
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade300),
                borderRadius: BorderRadius.circular(8),
              ),
              alignment: Alignment.center,
              child: imgProvider == null
                  ? const Text('Captcha')
                  : Image(image: imgProvider, fit: BoxFit.cover),
            ),
            const SizedBox(width: 12),
            ElevatedButton(
              onPressed: _loading ? null : _loadCaptcha,
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.grey.shade200,
                foregroundColor: Colors.black87,
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
              child: Text(_loading ? 'Loading...' : 'Refresh'),
            ),
          ],
        ),
        const SizedBox(height: 10),
        TextField(
          controller: _controller,
          onChanged: (value) => widget.onChanged(_captchaKey, value),
          decoration: const InputDecoration(
            labelText: 'Enter captcha',
            border: OutlineInputBorder(),
          ),
        ),
      ],
    );
  }
}
