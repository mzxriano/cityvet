import 'package:cityvet_app/components/qr_scanner.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/utils/role_constant.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:cityvet_app/views/login_view.dart';
import 'package:cityvet_app/views/main_screens/animal/animal_view.dart';
import 'package:cityvet_app/views/main_screens/community/community_view.dart';
import 'package:cityvet_app/views/main_screens/home/home_view.dart';
import 'package:cityvet_app/views/main_screens/notification_view.dart';
import 'package:cityvet_app/views/profile/profile_view.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:cityvet_app/services/api_service.dart';
import 'package:cityvet_app/models/notification_model.dart';
import 'package:cityvet_app/viewmodels/animal_view_model.dart';
import 'package:cityvet_app/views/main_screens/animal/animal_preview.dart';

class MainLayout extends StatefulWidget {
  const MainLayout({super.key});

  @override
  State<MainLayout> createState() => _MainLayoutState();
}

class _MainLayoutState extends State<MainLayout> with TickerProviderStateMixin {
  int _currentIndex = 0;
  int _previousIndex = 0;
  int _unreadNotifications = 0;
  bool _isLoading = false;
  ApiService? _apiService;

  @override
  void initState() {
    super.initState();
    _initializeServices();
  }

  void _initializeServices() {
    _apiService = ApiService();
    _fetchUnreadNotifications();
    _setupNotificationListener();
  }

  void _setupNotificationListener() {
    _apiService?.onNewNotification = (notification) {
      if (mounted) {
        _fetchUnreadNotifications(); // Refresh unread count
      }
    };
  }

  Future<void> _fetchUnreadNotifications() async {
    final token = await AuthStorage().getToken();
    if (token == null || _apiService == null) return;

    try {
      final data = await _apiService!.getNotifications(token);
      final notifications = data
          .map<NotificationModel>((n) => NotificationModel.fromJson(n))
          .toList();
      
      if (mounted) {
        setState(() {
          _unreadNotifications = notifications.where((n) => !n.read).length;
        });
      }
    } catch (e) {
      debugPrint('Error fetching notifications: $e');
    }
  }

  List<Widget> _getPages(bool isVet) {
    return [
      const HomeView(),
      const CommunityView(),
      if (isVet) const QrScannerPage(),
      const AnimalView(),
      const NotificationView(),
    ];
  }

  List<NavigationItem> _getNavigationItems(bool isVet) {
    return [
      NavigationItem(icon: FontAwesomeIcons.house, pageIndex: 0),
      NavigationItem(icon: FontAwesomeIcons.users, pageIndex: 1),
      if (isVet) NavigationItem(icon: null, pageIndex: null),
      NavigationItem(icon: FontAwesomeIcons.paw, pageIndex: isVet ? 3 : 2),
      NavigationItem(
        icon: FontAwesomeIcons.bell,
        pageIndex: isVet ? 4 : 3,
        showBadge: _unreadNotifications > 0,
      ),
    ];
  }

  void _onTabSelected(int index) {
    final userViewModel = Provider.of<UserViewModel>(context, listen: false);
    final isVet = userViewModel.user?.role == Role.veterinarian;
    final navItems = _getNavigationItems(isVet);
    
    if (index >= navItems.length || navItems[index].pageIndex == null) {
      return; // Invalid index or QR placeholder
    }

    if (_currentIndex != index) {
      setState(() {
        _previousIndex = _currentIndex;
        _currentIndex = index;
      });
    }
  }

  Future<void> _handleLogout() async {
    if (_isLoading) return;

    setState(() => _isLoading = true);

    try {
      final storage = AuthStorage();
      final token = await storage.getToken();

      if (token == null) {
        _showMessage('No token found. Please log in again.');
        return;
      }

      final response = await Dio().post(
        'http://192.168.1.109:8000/api/auth/user/logout',
        options: Options(
          headers: {'Authorization': 'Bearer $token'},
        ),
      );

      if (response.statusCode == 200) {
        await storage.deleteToken();
        
        if (mounted) {
          _showMessage(response.data['message'] ?? 'Logged out successfully');
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (_) => const LoginView()),
          );
        }
      } else {
        _showMessage(response.data['error'] ?? 'Unknown error occurred.');
      }
    } catch (e) {
      _showMessage('Failed to log out: ${e.toString()}');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _showMessage(String message) {
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(message)),
      );
    }
  }

  void _showCommandPalette() async {
    final animalViewModel = Provider.of<AnimalViewModel>(context, listen: false);
    final animals = animalViewModel.animals;

    final pages = [
      {'label': 'Profile', 'builder': (_) => const ProfileView()},
      {'label': 'Notifications', 'builder': (_) => const NotificationView()},
    ];
    String query = '';
    await showDialog(
      context: context,
      builder: (context) {
        List<Map<String, dynamic>> filteredPages = pages;
        List<Map<String, dynamic>> filteredAnimals = [];
        return StatefulBuilder(
          builder: (context, setState) {
            filteredPages = pages.where((page) =>
              query.isEmpty || (page['label'] as String).toLowerCase().contains(query.toLowerCase())
            ).toList();

            // Only search animals if query is not empty
            if (query.isNotEmpty) {
              filteredAnimals = animals
                .where((animal) =>
                  animal.name.toLowerCase().contains(query.toLowerCase()) ||
                  (animal.breed?.toLowerCase().contains(query.toLowerCase()) ?? false) ||
                  animal.type.toLowerCase().contains(query.toLowerCase()) ||
                  animal.color.toLowerCase().contains(query.toLowerCase())
                )
                .map((animal) => {
                  'label': 'Animal: ${animal.name} (${animal.type})',
                  'builder': (_) => AnimalPreview(animalModel: animal),
                })
                .toList();
            } else {
              filteredAnimals = [];
            }

            final results = [...filteredPages, ...filteredAnimals];

            return AlertDialog(
              title: const Text('Search for a page or animal'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  TextField(
                    autofocus: true,
                    decoration: const InputDecoration(
                      hintText: 'Type to search pages or animals...'
                    ),
                    onChanged: (value) {
                      setState(() {
                        query = value;
                      });
                    },
                  ),
                  const SizedBox(height: 16),
                  SizedBox(
                    width: 300,
                    height: 200,
                    child: ListView.builder(
                      itemCount: results.length,
                      itemBuilder: (context, index) {
                        final item = results[index];
                        return ListTile(
                          title: Text(item['label']),
                          onTap: () {
                            Navigator.of(context).pop();
                            Navigator.push(
                              context,
                              MaterialPageRoute(builder: item['builder']),
                            );
                          },
                        );
                      },
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    return Consumer<UserViewModel>(
      builder: (context, userViewModel, _) {
        final isVet = userViewModel.user?.role == Role.veterinarian;
        final pages = _getPages(isVet);
        final navItems = _getNavigationItems(isVet);
        final selectedPageIndex = navItems[_currentIndex].pageIndex ?? 0;

        return Scaffold(
          appBar: _buildAppBar(userViewModel),
          backgroundColor: const Color(0xFFEEEEEE),
          drawer: _buildDrawer(userViewModel),
          body: _buildBody(pages, selectedPageIndex),
          floatingActionButton: _buildFloatingActionButton(isVet),
          floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
          bottomNavigationBar: _buildBottomNavigationBar(navItems, isVet),
        );
      },
    );
  }

  AppBar _buildAppBar(UserViewModel userViewModel) {
    return AppBar(
      leading: Builder(
        builder: (BuildContext context) {
          return IconButton(
            onPressed: () => Scaffold.of(context).openDrawer(),
            icon: const Icon(Icons.menu),
          );
        },
      ),
      title: Text(
        'Hello, ${userViewModel.user?.firstName ?? 'User'}',
        style: TextStyle(
          fontFamily: Config.primaryFont,
          fontSize: Config.fontMedium,
        ),
      ),
      actions: [
        IconButton(
          icon: const Icon(Icons.search),
          tooltip: 'Search for a page',
          onPressed: _showCommandPalette,
        ),
      ],
    );
  }

  Drawer _buildDrawer(UserViewModel userViewModel) {
    final user = userViewModel.user;
    
    return Drawer(
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          _buildDrawerHeader(user),
          _buildDrawerItem(
            'Profile',
            () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ProfileView()),
            ),
          ),
          _buildDrawerItem(
            'Archives',
            () {
              Navigator.pop(context);
              _onTabSelected(1);
            },
          ),
          const Divider(thickness: 0.5, color: Color(0xFFDDDDDD)),
          _buildDrawerItem(
            'Logout',
            _handleLogout,
            isLoading: _isLoading,
          ),
        ],
      ),
    );
  }

  DrawerHeader _buildDrawerHeader(user) {
    return DrawerHeader(
      decoration: BoxDecoration(color: Config.primaryColor),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          CircleAvatar(
            radius: 30,
            backgroundImage: user?.imageUrl != null
                ? NetworkImage(user!.imageUrl!)
                : null,
            child: user?.imageUrl == null
                ? const Icon(Icons.person, size: 30)
                : null,
          ),
          const SizedBox(width: 20),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  '${user?.firstName ?? 'User'} ${user?.lastName ?? 'Lastname'}',
                  maxLines: 3,
                  softWrap: true,
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontMedium,
                    fontWeight: Config.fontW600,
                    color: Config.color524F4F,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  user?.phoneNumber ?? '09xxxxxxxx',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: Config.fontSmall,
                    color: Config.tertiaryColor,
                  ),
                ),
                Text(
                  user?.email ?? 'user@gmail.com',
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
          ),
        ],
      ),
    );
  }

  ListTile _buildDrawerItem(String title, VoidCallback onTap, {bool isLoading = false}) {
    return ListTile(
      title: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            title,
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontMedium,
              color: Config.tertiaryColor,
            ),
          ),
          if (isLoading)
            const SizedBox(
              width: 16,
              height: 16,
              child: CircularProgressIndicator(strokeWidth: 2),
            )
          else
            Icon(
              Icons.arrow_forward_ios_rounded,
              color: Config.tertiaryColor,
            ),
        ],
      ),
      onTap: isLoading ? null : onTap,
    );
  }

  Widget _buildBody(List<Widget> pages, int selectedPageIndex) {
    return Padding(
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
          key: ValueKey<int>(selectedPageIndex),
          width: double.infinity,
          height: double.infinity,
          child: pages[selectedPageIndex],
        ),
      ),
    );
  }

  Widget? _buildFloatingActionButton(bool isVet) {
    if (!isVet) return null;

    return FloatingActionButton(
      onPressed: () => _onTabSelected(2),
      backgroundColor: Colors.white,
      splashColor: Config.primaryColor,
      shape: const CircleBorder(),
      child: const FaIcon(FontAwesomeIcons.qrcode, color: Colors.grey),
    );
  }

BottomAppBar _buildBottomNavigationBar(List<NavigationItem> navItems, bool isVet) {
  return BottomAppBar(
    color: Colors.white,
    shape: const CircularNotchedRectangle(),
    notchMargin: 10.0,
    child: Row(
      mainAxisAlignment: MainAxisAlignment.spaceAround,
      children: [
        // First two items (Home, Community)
        for (int i = 0; i < 2; i++)
          if (navItems[i].icon != null)
            _buildNavItem(
              icon: navItems[i].icon!,
              index: i,
              onTap: () => _onTabSelected(i),
              isSelected: _currentIndex == i,
              showBadge: navItems[i].showBadge,
            ),
        
        // Spacer for FAB (only for vets)
        if (isVet) 
          const SizedBox(width: 60), // Adjusted width
        
        // Remaining items (Animals, Notifications)
        for (int i = isVet ? 3 : 2; i < navItems.length; i++)
          if (navItems[i].icon != null)
            _buildNavItem(
              icon: navItems[i].icon!,
              index: i,
              onTap: () => _onTabSelected(i),
              isSelected: _currentIndex == i,
              showBadge: navItems[i].showBadge,
            ),
      ],
    ),
  );
}

  Widget _buildNavItem({
    required IconData icon,
    required int index,
    required VoidCallback onTap,
    required bool isSelected,
    bool showBadge = false,
  }) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 10),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Stack(
                clipBehavior: Clip.none,
                children: [
                  AnimatedScale(
                    scale: isSelected ? 1.1 : 1.0,
                    duration: const Duration(milliseconds: 200),
                    child: FaIcon(
                      icon,
                      color: isSelected ? Config.primaryColor : Colors.grey,
                    ),
                  ),
                  if (showBadge)
                    Positioned(
                      right: -2,
                      top: -2,
                      child: Container(
                        width: 10,
                        height: 10,
                        decoration: const BoxDecoration(
                          color: Colors.red,
                          shape: BoxShape.circle,
                        ),
                      ),
                    ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _apiService?.onNewNotification = null;
    super.dispose();
  }
}

// Helper class for navigation items
class NavigationItem {
  final IconData? icon;
  final int? pageIndex;
  final bool showBadge;

  NavigationItem({
    required this.icon,
    required this.pageIndex,
    this.showBadge = false,
  });
}