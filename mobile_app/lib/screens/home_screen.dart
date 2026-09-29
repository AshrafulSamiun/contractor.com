import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';
import '../widgets/app_drawer.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(title: const Text('ParcelTrack')),
      body: ListView(
        children: [
          const _HeroSlider(),
          _trustSection(),
          _sectionTitle('Why Choose Us?'),
          _featureCard(
            'C1',
            'Fully digital parcel lifecycle',
            'End-to-end tracking without paper.',
          ),
          _featureCard(
            'C2',
            'Real-time notifications',
            'Instant SMS and email alerts.',
          ),
          _featureCard(
            'C3',
            'Secure delivery verification',
            'Digital signatures and photo proof.',
          ),
          _featureCard(
            'C4',
            'Detailed audit trails',
            'Complete history of every parcel.',
          ),
          _featureCard(
            'C5',
            'Scalable for any building size',
            'From small offices to large complexes.',
          ),
          _processSection(),
          _contactSection(),
        ],
      ),
    );
  }

  Widget _sectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 18, 16, 8),
      child: Text(
        title,
        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
      ),
    );
  }

  Widget _trustSection() {
    return Container(
      padding: const EdgeInsets.all(16),
      color: const Color(0xFFF8FAFC),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Trusted by busy teams',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 6),
          const Text('Reduce parcel handoff time and keep residents informed.'),
          const SizedBox(height: 12),
          Row(
            children: const [
              Expanded(
                child: _TrustCard(title: '10k+', subtitle: 'Parcels processed'),
              ),
              SizedBox(width: 10),
              Expanded(
                child: _TrustCard(title: '99.9%', subtitle: 'Confirm rate'),
              ),
              SizedBox(width: 10),
              Expanded(
                child: _TrustCard(title: '35%', subtitle: 'Faster pickup'),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 12,
            children: const [
              _LogoChip('Atlas Realty'),
              _LogoChip('Northgate'),
              _LogoChip('Skyline'),
              _LogoChip('HarborOne'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _featureCard(String badge, String title, String subtitle) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Card(
        elevation: 2,
        child: ListTile(
          leading: CircleAvatar(
            backgroundColor: const Color(0xFF2F6FE4),
            child: Text(badge, style: const TextStyle(color: Colors.white)),
          ),
          title: Text(title),
          subtitle: Text(subtitle),
        ),
      ),
    );
  }

  Widget _processSection() {
    return Container(
      margin: const EdgeInsets.only(top: 16),
      padding: const EdgeInsets.all(16),
      color: const Color(0xFF2F6FE4),
      child: Column(
        children: [
          const Text(
            'How It Works?',
            style: TextStyle(
              color: Colors.white,
              fontSize: 18,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 8),
          _processStep(
            '1',
            'Parcel arrives at reception (staff scans barcode)',
          ),
          _processStep('2', 'Staff registers parcel in system'),
          _processStep('3', 'Receiver gets instant notification'),
          _processStep('4', 'Receiver collects parcel with verification'),
          _processStep('5', 'System saves delivery record'),
        ],
      ),
    );
  }

  Widget _processStep(String number, String text) {
    return Container(
      margin: const EdgeInsets.symmetric(vertical: 6),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 14,
            backgroundColor: const Color(0xFF2F6FE4),
            child: Text(
              number,
              style: const TextStyle(color: Colors.white, fontSize: 12),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(child: Text(text)),
        ],
      ),
    );
  }

  Widget _contactSection() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: const [
              Text(
                'Contact Us',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
              ),
              SizedBox(height: 8),
              Text('Email: info@parceltrack.com'),
              Text('Phone: +1 (555) 123-4567'),
            ],
          ),
        ),
      ),
    );
  }
}

class _HeroSlider extends StatefulWidget {
  const _HeroSlider();

  @override
  State<_HeroSlider> createState() => _HeroSliderState();
}

class _HeroSliderState extends State<_HeroSlider> {
  VideoPlayerController? _videoController;
  final List<String> _videoAssets = [
    'assets/hero/hero-1.mp4',
    'assets/hero/hero-2.mp4',
    'assets/hero/hero-3.mp4',
  ];
  int _currentIndex = 0;
  bool _isSwitching = false;

  @override
  void initState() {
    super.initState();
    _loadVideo(_currentIndex);
  }

  @override
  void dispose() {
    _videoController?.removeListener(_videoListener);
    _videoController?.dispose();
    super.dispose();
  }

  void _videoListener() {
    final controller = _videoController;
    if (controller == null || _isSwitching) {
      return;
    }
    final value = controller.value;
    if (value.isInitialized &&
        !value.isPlaying &&
        value.position >= value.duration &&
        value.duration != Duration.zero) {
      _isSwitching = true;
      _switchToNext();
    }
  }

  Future<void> _switchToNext() async {
    final nextIndex = (_currentIndex + 1) % _videoAssets.length;
    await _loadVideo(nextIndex);
    _isSwitching = false;
  }

  Future<void> _loadVideo(int index) async {
    final oldController = _videoController;
    final controller = VideoPlayerController.asset(_videoAssets[index]);
    await controller.setLooping(false);
    await controller.setVolume(0);
    await controller.initialize();
    controller.addListener(_videoListener);
    if (!mounted) {
      controller.removeListener(_videoListener);
      await controller.dispose();
      return;
    }
    setState(() {
      _videoController = controller;
      _currentIndex = index;
    });
    oldController?.removeListener(_videoListener);
    await oldController?.dispose();
    await controller.play();
  }

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 260,
      child: Stack(
        fit: StackFit.expand,
        children: [
          if (_videoController?.value.isInitialized ?? false)
            FittedBox(
              fit: BoxFit.cover,
              child: SizedBox(
                width: _videoController!.value.size.width,
                height: _videoController!.value.size.height,
                child: VideoPlayer(_videoController!),
              ),
            ),
          Container(color: const Color(0xAA0B3C8B)),
          Center(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text(
                    'Modern parcel management for premium buildings',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Contractor.com helps you manage jobs, teams, vehicles, and billing in one place so your business stays organized and productive.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.white70),
                  ),
                  const SizedBox(height: 12),
                  ElevatedButton(
                    onPressed: () => Navigator.pushNamed(context, '/register'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: Colors.black87,
                    ),
                    child: const Text('Create Account'),
                  ),
                  const SizedBox(height: 8),
                  OutlinedButton(
                    onPressed: () => Navigator.pushNamed(context, '/login'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.white,
                      side: const BorderSide(color: Colors.white),
                    ),
                    child: const Text('Log In'),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _TrustCard extends StatelessWidget {
  const _TrustCard({required this.title, required this.subtitle});

  final String title;
  final String subtitle;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: const [
          BoxShadow(color: Colors.black12, blurRadius: 6, offset: Offset(0, 3)),
        ],
      ),
      child: Column(
        children: [
          Text(
            title,
            style: const TextStyle(
              color: Color(0xFF1D4ED8),
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 11),
          ),
        ],
      ),
    );
  }
}

class _LogoChip extends StatelessWidget {
  const _LogoChip(this.label);

  final String label;

  @override
  Widget build(BuildContext context) {
    return Chip(label: Text(label));
  }
}
