import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/views/login_view.dart';
import 'package:cityvet_app/views/main_screens/animal_view.dart';
import 'package:cityvet_app/views/main_screens/community/community_view.dart';
import 'package:cityvet_app/views/main_screens/home_view.dart';
import 'package:cityvet_app/views/main_screens/notification_view.dart';
import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';

class MainLayout extends StatefulWidget {
  const MainLayout({super.key});

  @override
  State<MainLayout> createState() => _MainLayoutState();
}

class _MainLayoutState extends State<MainLayout> with TickerProviderStateMixin {
  int _currentIndex = 0;
  int _previousIndex = 0;

  final List<Widget> _pages = const [
    HomeView(),
    CommunityView(),
    Center(child: Text('QR Scanner')),
    UsersPage(),
    NotificationView(),
  ];

  void _onTabSelected(int index) {
    if (_currentIndex != index) {
      setState(() {
        _previousIndex = _currentIndex;
        _currentIndex = index;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);
    return Scaffold(
      appBar: AppBar(
        leading: Builder(
          builder: (BuildContext context) {
            return IconButton(
              onPressed: () {
                Scaffold.of(context).openDrawer();
              },
              icon: Icon(Icons.menu),
            );
          },
        ),
        title: Text(
          'Hello, Juan',
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontSize: Config.fontMedium,
          ),
        ),
      ),
      backgroundColor: Color(0xFFEEEEEE),
      drawer: Drawer(
        child: ListView(
          padding: EdgeInsets.zero,
          children: <Widget>[
            DrawerHeader(
              decoration: BoxDecoration(
                color: Config.primaryColor,
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  CircleAvatar(
                    backgroundImage: AssetImage(''),
                    radius: 30,
                  ),
                  const SizedBox(width: 20,),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        'Juan Dela Cruz',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontMedium,
                          fontWeight: Config.fontW600,
                          color: Colors.black,
                        ),
                      ),
                      Text(
                        '09123456789',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontSmall,
                          color: Config.tertiaryColor,
                        ),
                      ),
                      Text(
                        '@juandelacruz@gmail.com',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontXS,
                          color: Config.tertiaryColor,
                        ),
                      ),
                    ],
                  )
                ],
              )
            ),
            ListTile(
              title: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Profile',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontMedium,
                      color: Config.tertiaryColor,
                    ),
                  ),
                  Icon(Icons.arrow_forward_ios_rounded, color: Config.tertiaryColor,),
                ],
              ),
              onTap: () {
                Navigator.pop(context); 
                _onTabSelected(0);
              },
            ),
            ListTile(
              title: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Archives',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontMedium,
                      color: Config.tertiaryColor
                    ),
                  ),
                  Icon(Icons.arrow_forward_ios_rounded, color: Config.tertiaryColor,),
                ],
              ),
              onTap: () {
                Navigator.pop(context); 
                _onTabSelected(1); 
              },
            ),
            Divider(thickness: 0.5, color: Color(0xFFDDDDDD),),
            ListTile(
              title: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Logout',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontMedium,
                      color: Config.tertiaryColor,
                    ),
                  ),
                  Icon(Icons.arrow_forward_ios_rounded, color: Config.tertiaryColor,),
                ],
              ),
              onTap: () {
                Navigator.pushReplacement(context, MaterialPageRoute(builder: (context) => LoginView())); 
              },
            ),
          ],
        ),
      ),
      body: Padding(
        padding: Config.paddingScreen,
        child: AnimatedSwitcher(
          duration: const Duration(milliseconds: 300),
          transitionBuilder: (Widget child, Animation<double> animation) {
           
            final bool slideLeft = _currentIndex > _previousIndex;
            
            return SlideTransition(
              position: Tween<Offset>(
                begin: slideLeft ? const Offset(1.0, 0.0) : const Offset(-1.0, 0.0),
                end: Offset.zero,
              ).animate(CurvedAnimation(
                parent: animation,
                curve: Curves.easeInOutCubic,
              )),
              child: FadeTransition(
                opacity: animation,
                child: child,
              ),
            );
          },
          child: SizedBox(
            key: ValueKey<int>(_currentIndex),
            width: double.infinity,
            height: double.infinity,
            child: _pages[_currentIndex],
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _onTabSelected(2),
        backgroundColor: Colors.white,
        splashColor: Config.primaryColor,
        shape: CircleBorder(),
        child: const FaIcon(FontAwesomeIcons.qrcode, color: Colors.grey),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
      bottomNavigationBar: BottomAppBar(
        color: Colors.white,
        shape: const CircularNotchedRectangle(),
        notchMargin: 10.0,
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            _buildNavItem(icon: FontAwesomeIcons.house, index: 0),
            _buildNavItem(icon: FontAwesomeIcons.users, index: 1),
            const SizedBox(width: 40),
            _buildNavItem(icon: FontAwesomeIcons.paw, index: 3),
            _buildNavItem(icon: FontAwesomeIcons.bell, index: 4),
          ],
        ),
      ),
    );
  }

  Widget _buildNavItem({required IconData icon, required int index}) {
    final isSelected = _currentIndex == index;
    return Expanded(
      child: GestureDetector(
        onTap: () => _onTabSelected(index),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 10),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              AnimatedScale(
                scale: isSelected ? 1.1 : 1.0,
                duration: const Duration(milliseconds: 200),
                child: FaIcon(
                  icon, 
                  color: isSelected ? Config.primaryColor : Colors.grey,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}