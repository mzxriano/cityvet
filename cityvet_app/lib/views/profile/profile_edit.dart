import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/components/label_text.dart';
import 'package:cityvet_app/components/text_field.dart';
import 'package:cityvet_app/models/barangay_model.dart';
import 'package:cityvet_app/models/user_model.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/profile_edit_view_model.dart';
import 'package:cityvet_app/viewmodels/user_view_model.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class ProfileEdit extends StatefulWidget {
  const ProfileEdit({super.key});

  @override
  State<ProfileEdit> createState() => _ProfileEditState();
}

class _ProfileEditState extends State<ProfileEdit> {
  final TextEditingController _firstNameController = TextEditingController();
  final TextEditingController _lastNameController = TextEditingController();
  final TextEditingController _phoneNumberController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _streetController = TextEditingController();

  final FocusNode _firstNameNode = FocusNode();
  final FocusNode _lastNameNode = FocusNode();
  final FocusNode _phoneNumberNode = FocusNode();
  final FocusNode _emailNode = FocusNode();
  final FocusNode _streetNode = FocusNode();

  bool _isFirstNameFocused = false;
  bool _isLastNameFocused = false;
  bool _isPhoneNumberFocused = false;
  bool _isEmailFocused = false;
  bool _isStreetFocused = false;

  @override
  void initState() {
    super.initState();

    final user = Provider.of<UserViewModel>(context, listen: false).user!;
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final profileViewModel = Provider.of<ProfileEditViewModel>(context, listen: false);
      profileViewModel.initializeUserData(user);
    });

    _firstNameNode.addListener(() {
      setState(() {
        _isFirstNameFocused = _firstNameNode.hasFocus;
      });
    });
    _lastNameNode.addListener(() {
      setState(() {
        _isLastNameFocused = _lastNameNode.hasFocus;
      });
    });
    _phoneNumberNode.addListener(() {
      setState(() {
        _isPhoneNumberFocused = _phoneNumberNode.hasFocus;
      });
    });
    _emailNode.addListener(() {
      setState(() {
        _isEmailFocused = _emailNode.hasFocus;
      });
    });
    _streetNode.addListener(() {
      setState(() {
        _isStreetFocused = _streetNode.hasFocus;
      });
    });
  }

  @override
  void dispose() {
    _firstNameController.dispose();
    _lastNameController.dispose();
    _phoneNumberController.dispose();
    _emailController.dispose();
    _streetController.dispose();

    _firstNameNode.dispose();
    _lastNameNode.dispose();
    _phoneNumberNode.dispose();
    _emailNode.dispose();
    _streetNode.dispose();

    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);
    final userViewModel = Provider.of<UserViewModel>(context, listen: false);
    final profileViewModel = context.watch<ProfileEditViewModel>();
    _initializeControllers(userViewModel.user!);

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          onPressed: () => Navigator.pop(context),
          icon: Config.backButtonIcon,
        ),
        title: Text(
          'Edit Profile',
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontSize: Config.fontMedium,
          ),
        ),
      ),
      body: Stack(
        children: [
          SafeArea(
            child: SingleChildScrollView(
              padding: Config.paddingScreen,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Align(
                    alignment: Alignment.center,
                    child: CircleAvatar(
                      backgroundImage: profileViewModel.profile != null ?
                      FileImage(profileViewModel.profile!)
                      : null,
                      radius: 70, 
                      child: IconButton(
                        onPressed: (){
                        profileViewModel.pickImageFromGallery();
                      }, icon: Icon(Icons.camera_alt_rounded,size: 50, color: Colors.grey,)
                      ),
                    ),
                  ),
                  Config.heightMedium,

                  // First Name
                  Text(
                    'First Name',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontMedium,
                    ),
                  ),
                  CustomTextField(
                    controller: _firstNameController,
                    node: _firstNameNode,
                    textInputType: TextInputType.name,
                    isObscured: false,
                    isFocused: _isFirstNameFocused,
                  ),
                  Config.heightMedium,

                  // Last Name
                  Text(
                    'Last Name',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontMedium,
                    ),
                  ),
                  CustomTextField(
                    controller: _lastNameController,
                    node: _lastNameNode,
                    textInputType: TextInputType.name,
                    isObscured: false,
                    isFocused: _isLastNameFocused,
                  ),
                  Config.heightMedium,

                  // Birth Date
                  Text(
                    'Birthdate',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontMedium,
                    ),
                  ),
                  InkWell(
                    onTap: () async {
                      final date = await showDatePicker(
                        context: context,
                        initialDate: profileViewModel.selectedDate ?? DateTime.now(),
                        firstDate: DateTime(1950),
                        lastDate: DateTime.now(),
                      );
                      if (date != null) {
                        profileViewModel.setBirthDate(date);
                      }
                    },
                    child: InputDecorator(
                      decoration: InputDecoration(
                        labelText: '',
                        filled: true,
                        fillColor: Config.secondaryColor,
                        contentPadding: Config.paddingTextfield,
                        border: const OutlineInputBorder(
                          borderRadius: BorderRadius.all(Radius.circular(10)),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderSide: BorderSide(
                            color: Colors.transparent,
                          ),
                          borderRadius: const BorderRadius.all(Radius.circular(10)),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderSide: BorderSide(
                            color: Config.primaryColor,
                            width: 2,
                          ),
                          borderRadius: const BorderRadius.all(Radius.circular(10)),
                        ),
                      ),
                      child: Text(
                        profileViewModel.selectedDate != null
                            ? '${profileViewModel.selectedDate!.month}/${profileViewModel.selectedDate!.day}/${profileViewModel.selectedDate!.year}'
                            : 'Select Date',
                        style: TextStyle(
                          color: profileViewModel.selectedDate != null ? Colors.black : Colors.grey,
                        ),
                      ),
                    ),
                  ),
                  Config.heightMedium,

                  // Phone Number
                  Text(
                    'Phone Number',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontMedium,
                    ),
                  ),
                  CustomTextField(
                    controller: _phoneNumberController,
                    node: _phoneNumberNode,
                    textInputType: TextInputType.phone,
                    isObscured: false,
                    isFocused: _isPhoneNumberFocused,
                  ),
                  Config.heightMedium,

                  // Email
                  Text(
                    'Email',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontMedium,
                    ),
                  ),
                  CustomTextField(
                    controller: _emailController,
                    node: _emailNode,
                    textInputType: TextInputType.emailAddress,
                    isObscured: false,
                    isFocused: _isEmailFocused,
                  ),
                  Config.heightMedium,

                  // Barangay Dropdown
                  Text(
                    'Barangay',
                    style: TextStyle(
                      fontFamily: Config.primaryFont,
                      fontSize: Config.fontMedium,
                    ),
                  ),
                  DropdownButtonHideUnderline(
                    child: DropdownButtonFormField<BarangayModel>(
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: Config.secondaryColor,
                        contentPadding: Config.paddingTextfield,
                        border: const OutlineInputBorder(
                          borderRadius: BorderRadius.all(Radius.circular(10)),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderSide: BorderSide(
                            color: Colors.transparent,
                          ),
                          borderRadius: const BorderRadius.all(Radius.circular(10)),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderSide: BorderSide(
                            color: Config.primaryColor,
                            width: 2,
                          ),
                          borderRadius: const BorderRadius.all(Radius.circular(10)),
                        ),
                      ),
                      value: profileViewModel.selectedBarangay,
                        hint: Text(
                        profileViewModel.isLoading ? 'Loading barangays...' : 'Select Barangay',
                        style: TextStyle(color: Colors.grey),
                      ),
                      items: profileViewModel.barangays?.map((barangay) {
                        return DropdownMenuItem<BarangayModel>(
                          value: barangay,
                          child: Text(barangay.name),
                        );
                      }).toList(),
                      onChanged: profileViewModel.isLoading ? null : (value) {
                        profileViewModel.setBarangay(value);
                      },
                    ),
                  ),
                  Config.heightMedium,

                  LabelText(label: 'Street ', isRequired: false),
                  CustomTextField(
                    controller: _streetController,
                    node: _streetNode,
                    textInputType: TextInputType.streetAddress,
                    isObscured: false,
                    isFocused: _isStreetFocused,
                  ),
                  Config.heightMedium,

                  // Save Button
                  Button(
                    width: double.infinity,
                    title: 'Save',
                    onPressed: () async {
                      await profileViewModel.editProfile(
                        _firstNameController.text,
                        _lastNameController.text,
                        _emailController.text,
                        _phoneNumberController.text,
                        profileViewModel.formattedBDate,
                        profileViewModel.selectedBarangay,
                        _streetController.text,
                      );

                      if (profileViewModel.isSuccessful) {
                        Provider.of<UserViewModel>(context, listen: false).setUser(profileViewModel.user!);
                        
                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Profile successfully updated.')));
                        Navigator.pop(context);
                      }else {
                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(profileViewModel.error.toString())));
                        Navigator.pop(context);
                      }
                    },
                  ),
                ],
              ),
            ),
          ),

          // Show loading overlay when initializing
          if (profileViewModel.isLoading)
            Container(
              color: Colors.black.withValues(alpha: 0.5, red: 0, green: 0, blue: 0),
              child: const Center(
                child: CircularProgressIndicator(color: Color(0xFFDDDDDD)),
              ),
            ),
        ],
      ),
            );
  }

  void _initializeControllers(UserModel user) {
    if (_firstNameController.text.isEmpty) {
      _firstNameController.text = user.firstName ?? '';
      _lastNameController.text = user.lastName ?? '';
      _phoneNumberController.text = user.phoneNumber ?? '';
      _emailController.text = user.email ?? '';
      _streetController.text = user.street ?? '';
    }
  }
}