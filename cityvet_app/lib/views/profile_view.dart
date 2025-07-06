import 'package:cityvet_app/components/card.dart';
import 'package:cityvet_app/components/role.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/animal_view_model.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class ProfileView extends StatefulWidget {
  const ProfileView({super.key});

  @override
  State<ProfileView> createState() => _ProfileView();
}

class _ProfileView extends State<ProfileView> {
  int selectedTab = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<AnimalViewModel>(context, listen: false).fetchAnimals();
    });
  }
  @override
  Widget build(BuildContext context) {
    Config().init(context);
    final animalViewModel = Provider.of<AnimalViewModel>(context);
    final userRef = Provider.of<UserViewModel>(context);
    final animals = animalViewModel.animals;
    final role = RoleWidget();

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(onPressed: () => Navigator.pop(context), icon: Config.backButtonIcon),
        title: Text('Profile', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium,),),
        actions: [
          IconButton(onPressed: (){}, icon: Icon(Icons.edit))
        ],
      ),
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) {
            return Column(
              children: [
                // Scrollable header content
                Expanded(
                  child: SingleChildScrollView(
                    padding: Config.paddingScreen,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Align(
                          alignment: Alignment.topRight,
                          child: role['Owner'],
                        ),
                        Config.heightSmall,
                        CustomCard(
                          width: double.infinity,
                          color: Colors.white,
                          widget: Row(
                            crossAxisAlignment: CrossAxisAlignment.center,
                            mainAxisAlignment: MainAxisAlignment.start,
                            children: [
                              CircleAvatar(radius: 40),
                              const SizedBox(width: 15.0),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Text(userRef.user?.firstName ?? 'User', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium, color: Config.color524F4F)),
                                  const SizedBox(height: 3),
                                  Text('Male', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontSmall, color: Config.color524F4F)),
                                  const SizedBox(height: 3),
                                  Text('24 years old', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium, color: Config.color524F4F)),
                                ],
                              )
                            ],
                          ),
                        ),
                        Config.heightMedium,
                        CustomCard(
                          width: double.infinity,
                          color: Colors.white,
                          widget: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text('Address', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium, color: Config.tertiaryColor)),
                              Config.heightSmall,
                              Text('2428 Bugnay, San Vicente West, Urdaneta', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium, color: Config.color524F4F)),
                            ],
                          ),
                        ),
                        Config.heightMedium,
                        CustomCard(
                          width: double.infinity,
                          color: Colors.white,
                          widget: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text('Contact Info', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium, color: Config.tertiaryColor)),
                              Config.heightSmall,
                              Text(userRef.user?.phoneNumber ?? '09xxxxxx', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium, color: Config.color524F4F)),
                              const SizedBox(height: 3),
                              Text(userRef.user?.email ?? 'user@gmail.com', style: TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium, color: Config.color524F4F)),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),

                // Tabs that take remaining space
                DefaultTabController(
                  length: 2,
                  child: Container(
                    height: constraints.maxHeight * 0.4,
                    padding: EdgeInsets.symmetric(vertical: 10.0, horizontal: 20.0),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.only(
                        topLeft: Radius.circular(30),
                        topRight: Radius.circular(30),
                      ),
                      boxShadow: [
                        BoxShadow(
                          offset: Offset(0, 0),
                          color: Color.fromRGBO(0, 0, 0, 0.25),
                          blurRadius: 5,
                          spreadRadius: 0,
                        ),
                      ],
                    ),
                    child: Column(
                      children: [
                        Container(
                          height: 40,
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(20),
                            border: null
                          ),
                          child: TabBar(
                            indicator: BoxDecoration(
                              color: Config.primaryColor,
                              borderRadius: BorderRadius.circular(20),
                            ),
                            labelColor: Colors.white,
                            unselectedLabelColor: Config.tertiaryColor,
                            indicatorSize: TabBarIndicatorSize.tab,
                            overlayColor: WidgetStateProperty.all(Colors.transparent),
                            dividerColor: Colors.transparent,
                            tabs: [
                              Tab(child: Text("Owned Animals", style: TextStyle(fontWeight: FontWeight.bold))),
                              Tab(child: Text("Pictures", style: TextStyle(fontWeight: FontWeight.bold))),
                            ],
                          ),
                        ),
                        SizedBox(height: 10),
                        Expanded(
                          child: TabBarView(
                            children: [
                              animals.isEmpty ? Center(
                                child: Text("No Animals yet.", style: TextStyle(fontFamily: Config.primaryFont, color: Config.secondaryColor),),
                              ) : ListView.builder(
                                itemCount: animals.length,
                                itemBuilder: (context, index) {
                                  final animal = animals[index];
                                  return ListTile(
                                    title: Text(animal.name),
                                    subtitle: Text(animal.breed!),
                                  );
                                }
                              ),

                              Center(child: Text("No Pictures yet.", style: TextStyle(fontFamily: Config.primaryFont, color: Config.secondaryColor),)),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            );
          },
        ),
      ),
    );
  }
}