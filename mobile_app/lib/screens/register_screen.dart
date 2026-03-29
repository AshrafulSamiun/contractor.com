import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../widgets/captcha_widget.dart';
import '../widgets/app_drawer.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _companyCtrl = TextEditingController();
  final _usernameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _zipCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _pass2Ctrl = TextEditingController();
  String? _country;
  String _verifyVia = 'email';
  bool _agree = false;
  String _captchaKey = '';
  String _captchaValue = '';
  String? _error;
  List<String> _countries = [];
  int _step = 1;

  @override
  void initState() {
    super.initState();
    _loadCountries();
  }

  Future<void> _loadCountries() async {
    final api = context.read<AuthProvider>().client();
    final res = await api.get('/countries');
    if (res.success && res.data is List) {
      final list = res.data as List;
      setState(() {
        _countries = list.map((e) => e['country_name'].toString()).toList();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(title: const Text('Register')),
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
                    const Text('Create your account', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 4),
                    const Text('Start managing parcels in minutes.', style: TextStyle(color: Colors.black54)),
                    const SizedBox(height: 14),
                    _stepper(),
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
                          if (_step == 1) ...[
                            _input(_nameCtrl, 'Full Name', required: true),
                            _input(_companyCtrl, 'Company Name', required: true),
                            _input(_usernameCtrl, 'User Name'),
                            _input(_emailCtrl, 'Email', required: true),
                            _input(_phoneCtrl, 'Phone Number'),
                            DropdownButtonFormField<String>(
                              value: _country,
                              decoration: const InputDecoration(labelText: 'Country'),
                              items: _countries.map((c) => DropdownMenuItem(value: c, child: Text(c))).toList(),
                              onChanged: (val) => setState(() => _country = val),
                              validator: (v) => (v == null || v.isEmpty) ? 'Required' : null,
                            ),
                            const SizedBox(height: 12),
                            _input(_zipCtrl, 'Zip / Postal Code', required: true),
                            const SizedBox(height: 12),
                            SizedBox(
                              width: double.infinity,
                              child: ElevatedButton(
                                onPressed: auth.loading ? null : _nextStep,
                                child: const Text('Continue'),
                              ),
                            ),
                            Align(
                              alignment: Alignment.centerRight,
                              child: TextButton(
                                onPressed: () => Navigator.pushNamed(context, '/terms'),
                                child: const Text('View Terms & Conditions'),
                              ),
                            ),
                          ] else ...[
                            _input(_passCtrl, 'Password', required: true, obscure: true),
                            _input(_pass2Ctrl, 'Confirm Password', required: true, obscure: true),
                            const SizedBox(height: 12),
                            Row(
                              children: [
                                const Text('Send Verification Code via'),
                                const SizedBox(width: 10),
                                DropdownButton<String>(
                                  value: _verifyVia,
                                  items: const [
                                    DropdownMenuItem(value: 'email', child: Text('Email')),
                                    DropdownMenuItem(value: 'sms', child: Text('SMS')),
                                  ],
                                  onChanged: (val) => setState(() => _verifyVia = val ?? 'email'),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),
                            CaptchaWidget(
                              onChanged: (key, value) {
                                _captchaKey = key;
                                _captchaValue = value;
                              },
                            ),
                            const SizedBox(height: 12),
                            CheckboxListTile(
                              value: _agree,
                              onChanged: (v) => setState(() => _agree = v ?? false),
                              title: const Text('I agree to the Terms & Conditions'),
                              controlAffinity: ListTileControlAffinity.leading,
                            ),
                            Align(
                              alignment: Alignment.centerRight,
                              child: TextButton(
                                onPressed: () => Navigator.pushNamed(context, '/terms'),
                                child: const Text('View Terms & Conditions'),
                              ),
                            ),
                            const SizedBox(height: 12),
                            Row(
                              children: [
                                Expanded(
                                  child: OutlinedButton(
                                    onPressed: auth.loading ? null : () => setState(() => _step = 1),
                                    child: const Text('Back'),
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: ElevatedButton(
                                    onPressed: auth.loading ? null : () => _submit(auth),
                                    child: Text(auth.loading ? 'Creating...' : 'Sign Up'),
                                  ),
                                ),
                              ],
                            ),
                          ],
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

  Widget _input(TextEditingController controller, String label, {bool required = false, bool obscure = false}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextFormField(
        controller: controller,
        obscureText: obscure,
        decoration: InputDecoration(labelText: label, border: const OutlineInputBorder()),
        validator: (v) => (required && (v == null || v.isEmpty)) ? 'Required' : null,
      ),
    );
  }

  Future<void> _submit(AuthProvider auth) async {
    setState(() => _error = null);
    if (!_formKey.currentState!.validate()) return;
    if (_passCtrl.text != _pass2Ctrl.text) {
      setState(() => _error = 'Passwords do not match.');
      return;
    }
    if (!_agree) {
      setState(() => _error = 'You must agree to the terms.');
      return;
    }
    if (_captchaKey.isEmpty || _captchaValue.isEmpty) {
      setState(() => _error = 'Please enter the captcha.');
      return;
    }

    final payload = {
      'name': _nameCtrl.text.trim(),
      'username': _usernameCtrl.text.trim(),
      'email': _emailCtrl.text.trim(),
      'phoneNo': _phoneCtrl.text.trim(),
      'password': _passCtrl.text.trim(),
      'password_confirmation': _pass2Ctrl.text.trim(),
      'company': _companyCtrl.text.trim(),
      'country': _country,
      'zip': _zipCtrl.text.trim(),
      'verifyVia': _verifyVia,
      'captcha_key': _captchaKey,
      'captcha_value': _captchaValue,
    };

    final result = await auth.register(payload);
    if (!mounted) return;
    if (result.verificationRequired) {
      Navigator.popAndPushNamed(context, '/verify');
      return;
    }
    if (result.success) {
      Navigator.popAndPushNamed(context, '/dashboard');
    } else {
      setState(() => _error = result.message ?? 'Registration failed.');
    }
  }

  void _nextStep() {
    setState(() => _error = null);
    if (!_formKey.currentState!.validate()) return;
    if (_country == null || _country!.isEmpty) {
      setState(() => _error = 'Country is required.');
      return;
    }
    setState(() => _step = 2);
  }

  Widget _stepper() {
    return Row(
      children: [
        Expanded(child: _stepChip('1', 'Basic', _step == 1)),
        const SizedBox(width: 8),
        Expanded(child: _stepChip('2', 'Security', _step == 2)),
      ],
    );
  }

  Widget _stepChip(String num, String label, bool active) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 10),
      decoration: BoxDecoration(
        color: active ? const Color(0xFFE9F2FF) : const Color(0xFFF1F5F9),
        borderRadius: BorderRadius.circular(30),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          CircleAvatar(
            radius: 10,
            backgroundColor: active ? const Color(0xFF1D4ED8) : const Color(0xFFCBD5F5),
            child: Text(num, style: const TextStyle(fontSize: 11, color: Colors.white)),
          ),
          const SizedBox(width: 6),
          Text(label, style: TextStyle(color: active ? const Color(0xFF1D4ED8) : Colors.black54)),
        ],
      ),
    );
  }
}
