import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../widgets/captcha_widget.dart';
import '../widgets/app_drawer.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _loginCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  String _captchaKey = '';
  String _captchaValue = '';
  String? _error;

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(title: const Text('Login')),
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
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Welcome back', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 4),
                    const Text('Sign in to continue managing parcels.', style: TextStyle(color: Colors.black54)),
                    const SizedBox(height: 14),
                    if (_error != null)
                      Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.all(10),
                        color: Colors.red.shade50,
                        child: Text(_error!, style: const TextStyle(color: Colors.red)),
                      ),
                    Form(
                      key: _formKey,
                      child: Column(
                        children: [
                          TextFormField(
                            controller: _loginCtrl,
                            decoration: const InputDecoration(labelText: 'Username or Email'),
                            validator: (v) => (v == null || v.isEmpty) ? 'Required' : null,
                          ),
                          const SizedBox(height: 12),
                          TextFormField(
                            controller: _passCtrl,
                            obscureText: true,
                            decoration: const InputDecoration(labelText: 'Password'),
                            validator: (v) => (v == null || v.isEmpty) ? 'Required' : null,
                          ),
                          const SizedBox(height: 12),
                          CaptchaWidget(
                            onChanged: (key, value) {
                              _captchaKey = key;
                              _captchaValue = value;
                            },
                          ),
                          const SizedBox(height: 16),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton(
                              onPressed: auth.loading ? null : () => _submit(auth),
                              child: Text(auth.loading ? 'Signing in...' : 'Sign In'),
                            ),
                          ),
                        ],
                      ),
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

  Future<void> _submit(AuthProvider auth) async {
    setState(() => _error = null);
    if (!_formKey.currentState!.validate()) return;
    if (_captchaKey.isEmpty || _captchaValue.isEmpty) {
      setState(() => _error = 'Please enter the captcha.');
      return;
    }
    final result =
        await auth.login(_loginCtrl.text.trim(), _passCtrl.text.trim(), _captchaKey, _captchaValue);
    if (!mounted) return;
    if (result.verificationRequired) {
      Navigator.popAndPushNamed(context, '/verify');
      return;
    }
    if (result.success) {
      Navigator.popAndPushNamed(context, '/dashboard');
    } else {
      setState(() => _error = result.message ?? 'Login failed.');
    }
  }
}
