import 'package:cityvet_app/components/card.dart';
import 'package:cityvet_app/components/card_veterinarian.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';

class MainLayout extends StatefulWidget {
  const MainLayout({super.key});

  @override
  State<MainLayout> createState() => _MainLayoutState();
}

class _MainLayoutState extends State<MainLayout> {
  int _currentPage = 0;
  final PageController _pageController = PageController();

  void _onTabSelected(int index) {
    setState(() {
      _currentPage = index;
    });
    _pageController.animateToPage(
      index,
      duration: const Duration(milliseconds: 300),
      curve: Curves.easeInOut,
    );
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);
    return Scaffold(
      backgroundColor: Color(0xFFEEEEEE),
      body: PageView(
        controller: _pageController,
        physics: NeverScrollableScrollPhysics(),
        onPageChanged: (value) {
          setState(() {
            _currentPage = value;
          });
        },
        children: const <Widget>[
          Center(child: Column(
            children: [
              CardVeterinarian()
            ],
          )),
          Center(child: Text('Community')),
          Center(child: Text('QR Scanner')),
          Center(child: Text('Animals')),
          Center(child: Text('Notifications')),
        ],
      ),

      floatingActionButton: FloatingActionButton(
        onPressed: () => _onTabSelected(2), 
        backgroundColor: Colors.white,
        splashColor: Config.primaryColor,
        shape: CircleBorder(),
        child: const FaIcon(FontAwesomeIcons.qrcode, color: Colors.grey,),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,

      bottomNavigationBar: BottomAppBar(
        color: Colors.white,
        shape: const CircularNotchedRectangle(),
        notchMargin: 10.0,
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            _buildNavItem(icon: FontAwesomeIcons.house, index: 0,),
            _buildNavItem(icon: FontAwesomeIcons.users, index: 1,),
            const SizedBox(width: 40),
             _buildNavItem(icon: FontAwesomeIcons.paw, index: 3,),
            _buildNavItem(icon: FontAwesomeIcons.bell, index: 4,),
          ],
        ),
      ),
    );
  }

  // Widget for navigation items
  Widget _buildNavItem({required IconData icon, required int index}) {
    final isSelected = _currentPage == index;
    return Expanded(
      child: GestureDetector(
        onTap: () => _onTabSelected(index),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 10),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              FaIcon(icon, color: isSelected ? Config.primaryColor : Colors.grey),
            ],
          ),
        ),
      ),
    );
  }
}
