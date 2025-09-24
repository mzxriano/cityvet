import 'package:cityvet_app/components/qr_scanner.dart';
import 'package:cityvet_app/utils/api_constant.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/utils/role_constant.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:cityvet_app/views/animals_view.dart';
import 'package:cityvet_app/views/login_view.dart';
import 'package:cityvet_app/views/main_screens/animal/animal_view.dart';
import 'package:cityvet_app/views/main_screens/community/community_view.dart';
import 'package:cityvet_app/views/main_screens/home/home_view.dart';
import 'package:cityvet_app/views/main_screens/notification/notification_view.dart';
import 'package:cityvet_app/views/profile/profile_view.dart';
import 'package:cityvet_app/views/vaccination_history_view.dart';
import 'package:cityvet_app/views/main_screens/activity/schedule_activity_view.dart';
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
        _fetchUnreadNotifications(); 
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

  List<Widget> _getPages(bool canUseQrScanner) {
    return [
      const HomeView(),
      const CommunityView(),
      if (canUseQrScanner) const QrScannerPage(),
      const AnimalView(),
      const NotificationView(),
    ];
  }

  List<NavigationItem> _getNavigationItems(bool canUseQrScanner) {
    return [
      NavigationItem(icon: FontAwesomeIcons.house, pageIndex: 0),
      NavigationItem(icon: FontAwesomeIcons.users, pageIndex: 1),
      if (canUseQrScanner) NavigationItem(icon: FontAwesomeIcons.qrcode, pageIndex: 2),
      NavigationItem(icon: FontAwesomeIcons.paw, pageIndex: canUseQrScanner ? 3 : 2),
      NavigationItem(
        icon: FontAwesomeIcons.bell,
        pageIndex: canUseQrScanner ? 4 : 3,
        showBadge: _unreadNotifications > 0,
      ),
    ];
  }

  void _onTabSelected(int index) {
    final userViewModel = Provider.of<UserViewModel>(context, listen: false);
    final isVet = userViewModel.user?.role == Role.veterinarian;
    final isStaff = userViewModel.user?.role == Role.staff;
    final canUseQrScanner = isVet || isStaff;
    final navItems = _getNavigationItems(canUseQrScanner);
    
    if (index >= navItems.length || navItems[index].pageIndex == null) {
      return; 
    }

    if (_currentIndex != index) {
      setState(() {
        _previousIndex = _currentIndex;
        _currentIndex = index;
      });
    }
  }

  // Added confirmation dialog before logout
  Future<void> _showLogoutConfirmation() async {
    final bool? shouldLogout = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          title: Row(
            children: [
              Icon(
                Icons.logout,
                color: Config.primaryColor,
                size: 24,
              ),
              const SizedBox(width: 12),
              Text(
                'Confirm Logout',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                  color: Config.primaryColor,
                ),
              ),
            ],
          ),
          content: Text(
            'Are you sure you want to logout? You will need to sign in again to access your account.',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: 16,
              color: Colors.grey[700],
              height: 1.4,
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              style: TextButton.styleFrom(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              child: Text(
                'Cancel',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: 16,
                  fontWeight: FontWeight.w500,
                  color: Colors.grey[600],
                ),
              ),
            ),
            ElevatedButton(
              onPressed: () => Navigator.of(context).pop(true),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red[600],
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                elevation: 0,
              ),
              child: Text(
                'Logout',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        );
      },
    );

    // If user confirmed logout, proceed with logout
    if (shouldLogout == true) {
      await _handleLogout();
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
        '${ApiConstant.baseUrl}/user/logout',
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
  final userViewModel = Provider.of<UserViewModel>(context, listen: false);
  final animals = animalViewModel.animals;
  
  final user = userViewModel.user;
  final userRole = user?.role ?? '';
  final canAccessVaccination = userRole != 'owner';

  final pages = [
    {'label': 'Profile', 'builder': (_) => const ProfileView(), 'icon': Icons.person_outline},
    {'label': 'Notifications', 'builder': (_) => const NotificationView(), 'icon': Icons.notifications_outlined},
    if (canAccessVaccination)
      {'label': 'Vaccination History', 'builder': (_) => const VaccinationHistoryView(), 'icon': Icons.vaccines},
  ];
  
  String query = '';
  final TextEditingController searchController = TextEditingController();
  final FocusNode searchFocusNode = FocusNode();
  
  await showDialog(
    context: context,
    barrierDismissible: true,
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
                'label': animal.name,
                'subtitle': '${animal.type} • ${animal.breed ?? 'Mixed'}',
                'builder': (_) => AnimalPreview(animalModel: animal),
                'icon': Icons.pets,
                'isAnimal': true,
              })
              .toList();
          } else {
            filteredAnimals = [];
          }

          final results = [...filteredPages, ...filteredAnimals];

          return Dialog(
            backgroundColor: Colors.transparent,
            insetPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 100),
            child: Container(
              width: double.infinity,
              constraints: const BoxConstraints(maxWidth: 400, maxHeight: 500),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Header
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: Config.primaryColor.withOpacity(0.05),
                      borderRadius: const BorderRadius.only(
                        topLeft: Radius.circular(16),
                        topRight: Radius.circular(16),
                      ),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.search,
                          color: Config.primaryColor,
                          size: 24,
                        ),
                        const SizedBox(width: 12),
                        Text(
                          'Quick Search',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: 18,
                            fontWeight: FontWeight.w600,
                            color: Config.primaryColor,
                          ),
                        ),
                        const Spacer(),
                        GestureDetector(
                          onTap: () => Navigator.of(context).pop(),
                          child: Container(
                            padding: const EdgeInsets.all(4),
                            decoration: BoxDecoration(
                              color: Colors.grey.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: const Icon(
                              Icons.close,
                              size: 20,
                              color: Colors.grey,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  
                  // Search Field
                  Container(
                    margin: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: Colors.grey.withOpacity(0.05),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: Colors.grey.withOpacity(0.2),
                        width: 1,
                      ),
                    ),
                    child: TextField(
                      controller: searchController,
                      focusNode: searchFocusNode,
                      autofocus: true,
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: 16,
                        color: Colors.grey[800],
                      ),
                      decoration: InputDecoration(
                        hintText: 'Search pages, animals, or features...',
                        hintStyle: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: 16,
                          color: Colors.grey[500],
                        ),
                        prefixIcon: Container(
                          padding: const EdgeInsets.all(12),
                          child: Icon(
                            Icons.search,
                            color: Colors.grey[400],
                            size: 20,
                          ),
                        ),
                        suffixIcon: query.isNotEmpty
                            ? GestureDetector(
                                onTap: () {
                                  searchController.clear();
                                  setState(() {
                                    query = '';
                                  });
                                },
                                child: Container(
                                  padding: const EdgeInsets.all(12),
                                  child: Icon(
                                    Icons.clear,
                                    color: Colors.grey[400],
                                    size: 20,
                                  ),
                                ),
                              )
                            : null,
                        border: InputBorder.none,
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 16,
                        ),
                      ),
                      onChanged: (value) {
                        setState(() {
                          query = value;
                        });
                      },
                    ),
                  ),
                  
                  // Results
                  Flexible(
                    child: results.isEmpty && query.isNotEmpty
                        ? Container(
                            padding: const EdgeInsets.all(40),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  Icons.search_off,
                                  size: 48,
                                  color: Colors.grey[300],
                                ),
                                const SizedBox(height: 16),
                                Text(
                                  'No results found',
                                  style: TextStyle(
                                    fontFamily: Config.primaryFont,
                                    fontSize: 16,
                                    color: Colors.grey[600],
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  'Try searching for pages or animal names',
                                  style: TextStyle(
                                    fontFamily: Config.primaryFont,
                                    fontSize: 14,
                                    color: Colors.grey[500],
                                  ),
                                  textAlign: TextAlign.center,
                                ),
                              ],
                            ),
                          )
                        : query.isEmpty
                            ? Container(
                                padding: const EdgeInsets.all(20),
                                child: Column(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      Icons.lightbulb_outline,
                                      size: 32,
                                      color: Colors.grey[400],
                                    ),
                                    const SizedBox(height: 12),
                                    Text(
                                      'Quick Tips',
                                      style: TextStyle(
                                        fontFamily: Config.primaryFont,
                                        fontSize: 16,
                                        fontWeight: FontWeight.w600,
                                        color: Colors.grey[700],
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    Text(
                                      'Type to search for:\n• Pages (Profile, Notifications${canAccessVaccination ? ', Vaccination History' : ''})\n• Animals by name, breed, or type\n• App features',
                                      style: TextStyle(
                                        fontFamily: Config.primaryFont,
                                        fontSize: 14,
                                        color: Colors.grey[600],
                                        height: 1.4,
                                      ),
                                      textAlign: TextAlign.center,
                                    ),
                                  ],
                                ),
                              )
                            : ListView.builder(
                                shrinkWrap: true,
                                padding: const EdgeInsets.symmetric(horizontal: 8),
                                itemCount: results.length,
                                itemBuilder: (context, index) {
                                  final item = results[index];
                                  final isAnimal = item['isAnimal'] == true;
                                  
                                  return Container(
                                    margin: const EdgeInsets.symmetric(
                                      horizontal: 12,
                                      vertical: 4,
                                    ),
                                    decoration: BoxDecoration(
                                      borderRadius: BorderRadius.circular(8),
                                      color: Colors.transparent,
                                    ),
                                    child: ListTile(
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      leading: Container(
                                        width: 40,
                                        height: 40,
                                        decoration: BoxDecoration(
                                          color: isAnimal 
                                              ? Colors.orange.withOpacity(0.1)
                                              : Config.primaryColor.withOpacity(0.1),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Icon(
                                          item['icon'],
                                          color: isAnimal 
                                              ? Colors.orange[600]
                                              : Config.primaryColor,
                                          size: 20,
                                        ),
                                      ),
                                      title: Text(
                                        item['label'],
                                        style: TextStyle(
                                          fontFamily: Config.primaryFont,
                                          fontSize: 16,
                                          fontWeight: FontWeight.w500,
                                          color: Colors.grey[800],
                                        ),
                                      ),
                                      subtitle: item['subtitle'] != null
                                          ? Text(
                                              item['subtitle'],
                                              style: TextStyle(
                                                fontFamily: Config.primaryFont,
                                                fontSize: 14,
                                                color: Colors.grey[600],
                                              ),
                                            )
                                          : null,
                                      trailing: Icon(
                                        Icons.arrow_forward_ios,
                                        size: 16,
                                        color: Colors.grey[400],
                                      ),
                                      onTap: () {
                                        Navigator.of(context).pop();
                                        Navigator.push(
                                          context,
                                          MaterialPageRoute(builder: item['builder']),
                                        );
                                      },
                                    ),
                                  );
                                },
                              ),
                  ),
                ],
              ),
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
        final isStaff = userViewModel.user?.role == Role.staff;
        final canUseQrScanner = isVet || isStaff;
        final pages = _getPages(canUseQrScanner);
        final navItems = _getNavigationItems(canUseQrScanner);
        final selectedPageIndex = navItems[_currentIndex].pageIndex ?? 0;

        return Scaffold(
          appBar: _buildAppBar(userViewModel),
          backgroundColor: const Color(0xFFEEEEEE),
          drawer: _buildDrawer(userViewModel),
          body: _buildBody(pages, selectedPageIndex),
          floatingActionButton: _buildFloatingActionButton(canUseQrScanner),
          floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
          bottomNavigationBar: _buildBottomNavigationBar(navItems, canUseQrScanner),
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
          tooltip: 'Quick Search',
          onPressed: _showCommandPalette,
        ),
      ],
    );
  }

Drawer _buildDrawer(UserViewModel userViewModel) {
  final user = userViewModel.user;
  final userRole = user?.role ?? '';
  final canAccessVaccination = userRole != 'pet_owner' && userRole != 'livestock_owner' && userRole != 'poultry_owner';
  
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
        if (canAccessVaccination)
          _buildDrawerItem(
            'Animals',
            () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const AnimalManagementView()),
              );
            },
          ),
        if (canAccessVaccination)
          _buildDrawerItem(
            'Vaccination History',
            () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const VaccinationHistoryView()),
            ),
          ),
        if (userRole == 'aew')
          _buildDrawerItem(
            'Schedule Activity',
            () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ScheduleActivityView()),
            ),
          ),
        const Divider(thickness: 0.5, color: Color(0xFFDDDDDD)),
        _buildDrawerItem(
          'Logout',
          _showLogoutConfirmation,
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

  ListTile _buildDrawerItem(
  String title, 
  VoidCallback onTap, {
  bool isLoading = false,
  IconData? icon,
}) {
  return ListTile(
    leading: icon != null 
        ? Icon(
            icon,
            color: Config.tertiaryColor,
            size: 24,
          )
        : null,
    title: Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Expanded(
          child: Text(
            title,
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontMedium,
              color: Config.tertiaryColor,
            ),
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
            size: 16,
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

  Widget? _buildFloatingActionButton(bool canUseQrScanner) {
    if (!canUseQrScanner) return null;

    return FloatingActionButton(
      onPressed: () => _onTabSelected(2),
      backgroundColor: Colors.white,
      splashColor: Config.primaryColor,
      shape: const CircleBorder(),
      child: const FaIcon(FontAwesomeIcons.qrcode, color: Colors.grey),
    );
  }

BottomAppBar _buildBottomNavigationBar(List<NavigationItem> navItems, bool canUseQrScanner) {
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
        
        if (canUseQrScanner) 
          const SizedBox(width: 60), 
        
        // Remaining items (Animals, Notifications)
        for (int i = canUseQrScanner ? 3 : 2; i < navItems.length; i++)
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