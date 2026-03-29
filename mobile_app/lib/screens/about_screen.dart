import 'package:flutter/material.dart';
import '../widgets/app_drawer.dart';

class AboutScreen extends StatelessWidget {
  const AboutScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(title: const Text('About')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: const [
          Text(
            'ParcelTrack is a smart, cloud-based parcel management solution built for modern buildings.',
            style: TextStyle(fontSize: 16),
          ),
          SizedBox(height: 12),
          Text(
            'We help organizations manage incoming and outgoing parcels efficiently, securely, and transparently.',
          ),
          SizedBox(height: 12),
          Text(
            'Our platform enables building staff to register parcels quickly, notify recipients instantly via SMS or email, and capture secure proof of delivery.',
          ),
        ],
      ),
    );
  }
}
