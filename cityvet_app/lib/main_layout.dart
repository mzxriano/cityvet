import 'package:cityvet_app/components/qr_scanner.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:cityvet_app/views/login_view.dart';
import 'package:cityvet_app/views/main_screens/animal/animal_view.dart';
import 'package:cityvet_app/views/main_screens/community/community_view.dart';
import 'package:cityvet_app/views/main_screens/home_view.dart';
import 'package:cityvet_app/views/main_screens/notification_view.dart';
import 'package:cityvet_app/views/profile/profile_view.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:cityvet_app/services/api_service.dart';
import 'package:cityvet_app/models/notification_model.dart';

class MainLayout extends StatefulWidget {
  const MainLayout({super.key});

  @override
  State<MainLayout> createState() => _MainLayoutState();
}

class _MainLayoutState extends State<MainLayout> with TickerProviderStateMixin {
  int _currentIndex = 0;
  int _previousIndex = 0;
  int _unreadNotifications = 0;

  final List<Widget> _pages = const [
    HomeView(),
    CommunityView(),
    QrScannerPage(),
    AnimalView(),
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
  void initState() {
    super.initState();
    fetchUnreadNotifications();
    // Listen for new notifications and show popup
    final api = ApiService();
    api.onNewNotification = (notification) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                Icon(Icons.notifications, color: Colors.white),
                SizedBox(width: 8),
                Expanded(child: Text(notification.title + ': ' + notification.body)),
              ],
            ),
            backgroundColor: Colors.blue,
            duration: Duration(seconds: 4),
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            margin: EdgeInsets.all(16),
          ),
        );
      }
    };
  }

  Future<void> fetchUnreadNotifications() async {
    final token = await AuthStorage().getToken();
    if (token == null) return;
    final api = ApiService();
    try {
      final data = await api.getNotifications(token);
      final notifications = data.map<NotificationModel>((n) => NotificationModel.fromJson(n)).toList();
      setState(() {
        _unreadNotifications = notifications.where((n) => !n.read).length;
      });
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    return Consumer<UserViewModel>(
      builder: (context, ref, _) {
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
              'Hello, ${ref.user?.firstName ?? 'User'}',
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
                        radius: 30,
                        backgroundImage: ref.user?.imageUrl != null ? 
                          NetworkImage(ref.user!.imageUrl!) : 
                          null,
                      ),
                      const SizedBox(width: 20,),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              '${ref.user?.firstName ?? 'User'} ${ref.user?.lastName ?? 'Lastname'}',
                              maxLines: 3,
                              softWrap: true,
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontMedium,
                                fontWeight: Config.fontW600,
                                color: Config.color524F4F,
                              ),
                            ),
                            SizedBox(height: 4),
                            Text(
                              ref.user?.phoneNumber ?? '09xxxxxxxx',
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontSmall,
                                color: Config.tertiaryColor,
                              ),
                            ),
                            Text(
                              ref.user?.email ?? 'user@gmail.com',
                              softWrap: true,
                              maxLines: 2,
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontXS,
                                color: Config.tertiaryColor,
                              ),
                            ),
                          ],
                        ),
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
                    Navigator.push(context, MaterialPageRoute(builder: (_) => ProfileView()));
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
                  onTap: () async {
                    // Get the token from storage
                    final storage = AuthStorage();
                    final token = await storage.getToken();

                    if (token == null) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text('No token found. Please log in again.')),
                      );
                      return;
                    }

                    // Create Dio instance
                    final dio = Dio();
                    try {
                      // Send a request to the backend to invalidate the token
                      var response = await dio.post(
                        'http://192.168.1.109:8000/api/auth/user/logout',
                        options: Options(
                          headers: {'Authorization': 'Bearer $token'},
                        ),
                      );

                      // Check if the response is successful
                      if (response.statusCode == 200) {
                        // Delete the token from storage
                        await storage.deleteToken();

                        // Show success message and navigate to login page
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text(response.data['message'])),
                        );
                        Navigator.pushReplacement(
                          context,
                          MaterialPageRoute(builder: (_) => LoginView()),
                        );
                      } else {
                        // Show error message if status code isn't 200
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text(response.data['error'] ?? 'Unknown error occurred.')),
                        );
                      }
                    } catch (e) {
                      // Handle network or other errors
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text('Failed to log out: ${e.toString()}')),
                      );
                    }
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