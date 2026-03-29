import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class VerifyScreen extends StatefulWidget {
  const VerifyScreen({super.key});

  @override
  State<VerifyScreen> createState() => _VerifyScreenState();
}

class _VerifyScreenState extends State<VerifyScreen> {
  final _codeCtrl = TextEditingController();
  String? _error;
  bool _success = false;
  int _cooldown = 45;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _startCooldown();
  }

  @override
  void dispose() {
    _timer?.cancel();
    _codeCtrl.dispose();
    super.dispose();
  }

  void _startCooldown() {
    _timer?.cancel();
    setState(() => _cooldown = 45);
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) return;
      if (_cooldown <= 1) {
        timer.cancel();
        setState(() => _cooldown = 0);
      } else {
        setState(() => _cooldown -= 1);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final viaLabel = auth.verifyVia == 'sms' ? 'phone' : 'email';
    return Scaffold(
      appBar: AppBar(title: const Text('Verification')),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Color(0xFF2F6FE4), Color(0xFF1F4FBF)],
          ),
        ),
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            const SizedBox(height: 12),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Verify your account',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 6),
                    Text('We sent a 6-digit code to your $viaLabel.'),
                    const SizedBox(height: 12),
                    if (_error != null)
                      Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.all(10),
                        color: Colors.red.shade50,
                        child: Text(_error!, style: const TextStyle(color: Colors.red)),
                      ),
                    if (_success)
                      Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.all(10),
                        color: Colors.green.shade50,
                        child: const Text('Verified! Redirecting...',
                            style: TextStyle(color: Colors.green)),
                      ),
                    TextField(
                      controller: _codeCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Verification code',
                        hintText: 'Enter 6-digit code',
                      ),
                    ),
                    const SizedBox(height: 10),
                    const Text('Code valid for 10 minutes.', style: TextStyle(color: Colors.black54)),
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: auth.loading ? null : () => _verify(auth),
                        child: Text(auth.loading ? 'Verifying...' : 'Verify Code'),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        TextButton(
                          onPressed: (_cooldown > 0 || auth.loading) ? null : () => _resend(auth),
                          child: Text(_cooldown > 0 ? 'Resend in ${_cooldown}s' : 'Resend code'),
                        ),
                        const SizedBox(width: 8),
                        const Expanded(
                          child: Text('Did not receive it? Check spam or SMS inbox.',
                              style: TextStyle(color: Colors.black54)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    TextButton(
                      onPressed: () => Navigator.popAndPushNamed(context, '/login'),
                      child: const Text('Go to Login'),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _verify(AuthProvider auth) async {
    setState(() => _error = null);
    final code = _codeCtrl.text.trim();
    if (code.isEmpty) {
      setState(() => _error = 'Please enter the code.');
      return;
    }
    final res = await auth.verifyCode(code);
    if (!mounted) return;
    if (res.success) {
      setState(() => _success = true);
      Navigator.popAndPushNamed(context, '/dashboard');
    } else {
      setState(() => _error = res.message ?? 'Verification failed.');
    }
  }

  Future<void> _resend(AuthProvider auth) async {
    setState(() => _error = null);
    final res = await auth.resendCode();
    if (!mounted) return;
    if (res.success) {
      _startCooldown();
    } else {
      setState(() => _error = res.message ?? 'Failed to resend.');
    }
  }
}
