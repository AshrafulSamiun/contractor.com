import 'package:flutter/material.dart';
import '../widgets/app_drawer.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class ContactScreen extends StatefulWidget {
  const ContactScreen({super.key});

  @override
  State<ContactScreen> createState() => _ContactScreenState();
}

class _ContactScreenState extends State<ContactScreen> {
  final nameCtrl = TextEditingController();
  final emailCtrl = TextEditingController();
  final phoneCtrl = TextEditingController();
  final subjectCtrl = TextEditingController();
  final messageCtrl = TextEditingController();
  final formKey = GlobalKey<FormState>();
  final notifier = ValueNotifier<String?>(null);
  final loading = ValueNotifier<bool>(false);

  @override
  void dispose() {
    nameCtrl.dispose();
    emailCtrl.dispose();
    phoneCtrl.dispose();
    subjectCtrl.dispose();
    messageCtrl.dispose();
    notifier.dispose();
    loading.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(title: const Text('Contact')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const Text('Talk To Us', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
          const SizedBox(height: 12),
          ValueListenableBuilder<String?>(
            valueListenable: notifier,
            builder: (context, value, _) {
              if (value == null) return const SizedBox.shrink();
              final isError = value.startsWith('Error:');
              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(10),
                color: isError ? Colors.red.shade50 : Colors.green.shade50,
                child: Text(
                  isError ? value.replaceFirst('Error: ', '') : value,
                  style: TextStyle(color: isError ? Colors.red : Colors.green),
                ),
              );
            },
          ),
          Form(
            key: formKey,
            child: Column(
              children: [
                _input('Your Name', controller: nameCtrl, required: true),
                _input('Your Email', controller: emailCtrl, required: true),
                _input('Your Phone', controller: phoneCtrl),
                _input('Subject', controller: subjectCtrl),
                _input('Message', controller: messageCtrl, maxLines: 4, required: true),
              ],
            ),
          ),
          const SizedBox(height: 12),
          ValueListenableBuilder<bool>(
            valueListenable: loading,
            builder: (context, isLoading, _) {
              return ElevatedButton(
                onPressed: isLoading
                    ? null
                    : () async {
                        notifier.value = null;
                        if (!formKey.currentState!.validate()) return;
                        loading.value = true;
                        try {
                          final api = context.read<AuthProvider>().client();
                          final res = await api.post('/contact', {
                            'name': nameCtrl.text.trim(),
                            'email': emailCtrl.text.trim(),
                            'phone': phoneCtrl.text.trim(),
                            'subject': subjectCtrl.text.trim(),
                            'message': messageCtrl.text.trim(),
                          });
                          if (res.success) {
                            notifier.value = 'Thanks! We will reach out shortly.';
                            nameCtrl.clear();
                            emailCtrl.clear();
                            phoneCtrl.clear();
                            subjectCtrl.clear();
                            messageCtrl.clear();
                          } else {
                            notifier.value = 'Error: ${res.message ?? 'Failed to send.'}';
                          }
                        } finally {
                          loading.value = false;
                        }
                      },
                child: Text(isLoading ? 'Sending...' : 'Send Message'),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _input(String label,
      {int maxLines = 1, TextEditingController? controller, bool required = false}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextFormField(
        maxLines: maxLines,
        controller: controller,
        decoration: InputDecoration(labelText: label, border: const OutlineInputBorder()),
        validator: (v) => required && (v == null || v.isEmpty) ? 'Required' : null,
      ),
    );
  }
}
