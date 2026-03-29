import 'package:flutter/material.dart';
import '../widgets/app_drawer.dart';

class PlansScreen extends StatelessWidget {
  const PlansScreen({super.key});

  static const _features = [
    'Parcel Logging (record arrivals and departures)',
    'Track Parcel Handovers (to recipient only)',
    'Update Parcel Status (Picked Up, Delivered)',
    'Manage Storage Locations (Back Office, Lockers)',
    'Confirm Parcel Pickups',
    'Track Pending or Held Parcels',
    'Track External Lockers (e.g. third-party lockers)',
    'Delivery by Staff or Locker Access Management',
    'Record Parcel Final Outcomes (Returned, Lost, Damaged)',
    'Manage Multiple Locations',
    'Generate Reports & Basic Analytics',
    'Text Notifications for Parcel Status Updates',
    'Multi-language Support',
    'User Management (Add users, roles, active/inactive)',
    'Staff Administrative Tasks (Daily & Incident Reports, Timesheets)',
    'Full Account & Security Management (Billing, MFA, Recovery)',
  ];

  static const _basic = [
    true,
    true,
    true,
    false,
    false,
    false,
    false,
    false,
    false,
    false,
    false,
    false,
    false,
    false,
    false,
    false,
  ];

  static const _standard = [
    true,
    true,
    true,
    true,
    true,
    true,
    false,
    false,
    false,
    false,
    false,
    false,
    true,
    false,
    false,
    false,
  ];

  static const _enterprise = [
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(title: const Text('Plans')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _header(),
          _planCard('Basic Plan', '\$15 USD', _basic),
          _planCard('Standard Plan', '\$20 USD', _standard, popular: true),
          _planCard('Enterprise Plan', '\$25 USD', _enterprise),
          _notes(),
        ],
      ),
    );
  }

  Widget _header() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: const [
        Text('Find Your Perfect Plan', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600)),
        SizedBox(height: 6),
        Text('Choose the plan that best fits your organization’s needs.'),
        SizedBox(height: 12),
      ],
    );
  }

  Widget _planCard(String title, String price, List<bool> flags, {bool popular = false}) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                const Spacer(),
                if (popular)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(color: const Color(0xFFFBBF24), borderRadius: BorderRadius.circular(20)),
                    child: const Text('Most Popular', style: TextStyle(fontSize: 11)),
                  ),
              ],
            ),
            const SizedBox(height: 6),
            Text(price, style: const TextStyle(color: Color(0xFF2F6FE4), fontWeight: FontWeight.w600)),
            const SizedBox(height: 10),
            ...List.generate(_features.length, (i) => _featureRow(_features[i], flags[i])),
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {},
                child: Text(popular ? 'Selected' : 'Select Plan'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _featureRow(String label, bool enabled) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(enabled ? Icons.check_circle : Icons.cancel,
              size: 18, color: enabled ? Colors.green : Colors.red),
          const SizedBox(width: 8),
          Expanded(child: Text(label, style: const TextStyle(fontSize: 13))),
        ],
      ),
    );
  }

  Widget _notes() {
    return Card(
      color: const Color(0xFFF8FAFC),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: const [
            Text('Notes:', style: TextStyle(fontWeight: FontWeight.w600)),
            SizedBox(height: 6),
            Text('Fees are exclusive of taxes or additional charges.'),
            Text('Fees are per user/license.'),
            Text('Fees are non-refundable.'),
          ],
        ),
      ),
    );
  }
}
