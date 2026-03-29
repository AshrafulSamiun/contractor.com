import 'package:flutter/material.dart';

class TermsScreen extends StatelessWidget {
  const TermsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Terms & Conditions')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: const [
          Text('DeskDrop Terms & Conditions',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700)),
          SizedBox(height: 6),
          Text('Effective Date: January 1, 2026', style: TextStyle(color: Colors.black54)),
          SizedBox(height: 12),
          Text(
            'Overview: DeskDrop is a secure parcel management recording system. By creating an account, '
            'signing up, or logging in, each User agrees to these Terms and assumes full responsibility for compliance. '
            'DeskDrop does not engage in delivery, courier, refund, or transactional services between senders, recipients, '
            'or any third parties.',
            style: TextStyle(height: 1.5),
          ),
          SizedBox(height: 18),
          _SectionTitle('0. Acknowledgment, Acceptance, and User Liability'),
          _SectionBody(
            '0.1 By creating an account, signing up, or logging into DeskDrop (the "Platform"), each User expressly '
            'acknowledges that they have read, understood, and agreed to these Terms.\n'
            '0.2 Each User is personally and fully responsible for all activities conducted through their account.\n'
            '0.3 Organizations are responsible for all staff or agents who access the Platform using the organization\'s credentials.\n'
            '0.4 Failure to comply may result in suspension, termination, and legal remedies.',
          ),
          SizedBox(height: 14),
          _SectionTitle('1. Nature of the Platform'),
          _SectionBody(
            '1.1 DeskDrop provides a parcel management and recording system only.\n'
            '1.2 DeskDrop disclaims liability for loss, damage, theft, delay, misplacement, or misdelivery of parcels.\n'
            '1.3 DeskDrop does not create any contractual, transactional, fiduciary, or business relationship between Users and third parties.\n'
            '1.4 Users acknowledge DeskDrop\'s role is limited to internal record-keeping access.',
          ),
          SizedBox(height: 14),
          _SectionTitle('2. Licensing and Access'),
          _SectionBody(
            '2.1 Each User is granted a limited, revocable, non-transferable license to access the Platform.\n'
            '2.2 Each account is unique; credential sharing is strictly prohibited.\n'
            '2.3 Organizations remain responsible for assigning, monitoring, and revoking access.\n'
            '2.4 DeskDrop may audit account activity to enforce compliance.\n'
            '2.5 Unauthorized sharing may result in suspension and legal action.',
          ),
          SizedBox(height: 14),
          _SectionTitle('3. Prohibition on Unauthorized Parties'),
          _SectionBody(
            '3.1 Only Users authorized for operational parcel recording are permitted access.\n'
            '3.2 Unauthorized parties (developers, IT, consultants, contractors, auditors, vendors, competitors) are prohibited.\n'
            '3.3 Reverse engineering, benchmarking, or replication attempts are violations.\n'
            '3.4 DeskDrop may seek injunctive relief and damages globally.',
          ),
          SizedBox(height: 14),
          _SectionTitle('4. Use Restrictions'),
          _SectionBody(
            '4.1 Use only for internal parcel record-keeping.\n'
            '4.2 Prohibited: bypassing security, reverse engineering, copying workflows or system logic, permitting unauthorized access.\n'
            '4.3 Misuse or circumvention is a serious breach.',
          ),
          SizedBox(height: 14),
          _SectionTitle('5. Data Sharing and Privacy'),
          _SectionBody(
            '5.1 DeskDrop may disclose parcel information only to couriers for delivery verification or to authorities upon lawful request.\n'
            '5.2 DeskDrop does not sell or license user data for commercial purposes.\n'
            '5.3 Users must ensure entered data complies with applicable privacy laws.',
          ),
          SizedBox(height: 14),
          _SectionTitle('6. User Obligations and Indemnity'),
          _SectionBody(
            '6.1 Users are responsible for all entries, updates, and actions in the Platform.\n'
            '6.2 Users shall indemnify DeskDrop from claims arising from misuse or breach.\n'
            '6.3 Users are liable for unauthorized access by their staff or contractors.',
          ),
          SizedBox(height: 14),
          _SectionTitle('7. Intellectual Property'),
          _SectionBody(
            '7.1 All software, databases, interfaces, architecture, and trademarks are exclusive property of DeskDrop.\n'
            '7.2 Unauthorized copying or replication constitutes IP infringement.\n'
            '7.3 DeskDrop reserves all rights not expressly granted.',
          ),
          SizedBox(height: 14),
          _SectionTitle('8. Limitation of Liability'),
          _SectionBody(
            '8.1 DeskDrop is not liable for direct, indirect, incidental, or consequential damages.\n'
            '8.2 Total liability shall not exceed fees paid in the prior 12 months.\n'
            '8.3 Users assume all risks of using the Platform.',
          ),
          SizedBox(height: 14),
          _SectionTitle('9. Termination and Suspension'),
          _SectionBody(
            '9.1 DeskDrop may suspend or terminate any account immediately for violations.\n'
            '9.2 Termination does not relieve liability for prior actions.\n'
            '9.3 DeskDrop may pursue legal remedies for violations.',
          ),
          SizedBox(height: 14),
          _SectionTitle('10. Governing Law and Jurisdiction (Global)'),
          _SectionBody(
            '10.1 These Terms are governed by the laws of DeskDrop\'s jurisdiction of incorporation.\n'
            '10.2 Disputes are resolved by binding international arbitration.\n'
            '10.3 Arbitration may be remote or in a location determined by DeskDrop.\n'
            '10.4 Courts in the incorporation country may enforce awards or injunctive relief.\n'
            '10.5 DeskDrop may seek equitable relief in any jurisdiction worldwide.',
          ),
          SizedBox(height: 14),
          _SectionTitle('11. Entire Agreement'),
          _SectionBody(
            '11.1 These Terms constitute the entire agreement and supersede prior agreements.\n'
            '11.2 Modifications must be in writing and signed by DeskDrop.',
          ),
          SizedBox(height: 14),
          _SectionTitle('12. Enforcement and Remedies'),
          _SectionBody(
            '12.1 Violations may result in injunctive relief, civil and criminal liability, and recovery of legal fees.\n'
            '12.2 Users are jointly and severally liable for actions by authorized staff or agents.\n'
            '12.3 DeskDrop reserves all legal remedies available worldwide.',
          ),
          SizedBox(height: 18),
          Text('If you want, we can provide a matching Privacy & Data Policy as well.',
              style: TextStyle(color: Colors.black54)),
          SizedBox(height: 24),
        ],
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle(this.text);
  final String text;

  @override
  Widget build(BuildContext context) {
    return Text(text, style: const TextStyle(fontWeight: FontWeight.w700));
  }
}

class _SectionBody extends StatelessWidget {
  const _SectionBody(this.text);
  final String text;

  @override
  Widget build(BuildContext context) {
    return Text(text, style: const TextStyle(height: 1.5));
  }
}
