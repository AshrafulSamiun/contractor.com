import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class AppDrawer extends StatelessWidget {
  const AppDrawer({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Drawer(
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          DrawerHeader(
            decoration: const BoxDecoration(color: Color(0xFF2F6FE4)),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const CircleAvatar(radius: 28, backgroundColor: Colors.white),
                const SizedBox(height: 12),
                Text(
                  auth.user?['name']?.toString() ?? 'ParcelTrack',
                  style: const TextStyle(color: Colors.white, fontSize: 18),
                ),
                Text(
                  auth.user?['email']?.toString() ?? 'info@parceltrack.com',
                  style: const TextStyle(color: Colors.white70, fontSize: 12),
                ),
              ],
            ),
          ),
          _item(context, 'Home', '/'),
          _item(context, 'About', '/about'),
          _item(context, 'Plans', '/plans'),
          _item(context, 'Contact', '/contact'),
          _item(context, 'Terms & Conditions', '/terms'),
          if (auth.isAuthenticated) _item(context, 'Dashboard', '/dashboard'),
          if (!auth.isAuthenticated) _item(context, 'Login', '/login'),
          if (!auth.isAuthenticated) _item(context, 'Register', '/register'),
          if (auth.isAuthenticated)
            ListTile(
              leading: const Icon(Icons.logout),
              title: const Text('Logout'),
              onTap: () async {
                await auth.logout();
                if (context.mounted) Navigator.popAndPushNamed(context, '/');
              },
            ),
        ],
      ),
    );
  }

  ListTile _item(BuildContext context, String label, String route) {
    return ListTile(
      leading: const Icon(Icons.chevron_right),
      title: Text(label),
      onTap: () => Navigator.popAndPushNamed(context, route),
    );
  }
}
