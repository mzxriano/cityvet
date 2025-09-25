import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/components/label_text.dart';
import 'package:cityvet_app/modals/confirmation_modal.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/schedule_activity_view_model.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class ScheduleActivityView extends StatefulWidget {
  const ScheduleActivityView({super.key});

  @override
  State<ScheduleActivityView> createState() => _ScheduleActivityViewState();
}

class _ScheduleActivityViewState extends State<ScheduleActivityView> {
  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (context) {
        final viewModel = ScheduleActivityViewModel();
        viewModel.loadBarangays();
        return viewModel;
      },
      child: const _ScheduleActivityContent(),
    );
  }
}

class _ScheduleActivityContent extends StatefulWidget {
  const _ScheduleActivityContent();

  @override
  State<_ScheduleActivityContent> createState() => _ScheduleActivityContentState();
}

class _ScheduleActivityContentState extends State<_ScheduleActivityContent> {
  final TextEditingController reasonController = TextEditingController();
  final TextEditingController detailsController = TextEditingController();
  final FocusNode reasonNode = FocusNode();
  final FocusNode detailsNode = FocusNode();

  String? selectedBarangay;
  String? selectedCategory;
  String? selectedStatus = 'pending';
  DateTime? selectedDate;
  TimeOfDay? selectedTime;

  final List<String> categories = [
    'Vaccination',
    'Deworming', 
    'Vitamin',
    'Other'
  ];



  Future<bool> _onWillPop() async {
    final isFormDirty = reasonController.text.isNotEmpty ||
                        detailsController.text.isNotEmpty ||
                        selectedBarangay != null ||
                        selectedCategory != null ||
                        selectedDate != null ||
                        selectedTime != null;

    if (!isFormDirty) return true;

    final shouldLeave = await showConfirmationModal(context);
    return shouldLeave ?? false;
  }

  Future<void> _selectDate() async {
    final date = await showDatePicker(
      context: context,
      initialDate: DateTime.now().add(const Duration(days: 1)), // Minimum tomorrow
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (date != null) {
      setState(() {
        selectedDate = date;
      });
    }
  }

  Future<void> _selectTime() async {
    final time = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.now(),
    );
    if (time != null) {
      setState(() {
        selectedTime = time;
      });
    }
  }

  void _submitRequest(BuildContext context, ScheduleActivityViewModel viewModel) async {
    // Validation
    if (reasonController.text.trim().isEmpty ||
        selectedCategory == null ||
        selectedBarangay == null ||
        selectedDate == null ||
        selectedTime == null ||
        detailsController.text.trim().isEmpty) {
          
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please fill in all required fields.')),
      );
      return;
    }

    final activityData = {
      'reason': reasonController.text.trim(),
      'category': selectedCategory!,
      'barangay_id': selectedBarangay!,
      'date': '${selectedDate!.year}-${selectedDate!.month.toString().padLeft(2, '0')}-${selectedDate!.day.toString().padLeft(2, '0')}',
      'time': '${selectedTime!.hour.toString().padLeft(2, '0')}:${selectedTime!.minute.toString().padLeft(2, '0')}',
      'details': detailsController.text.trim(),
      'status': 'pending', // AEW requests start as pending
    };

    await viewModel.submitActivityRequest(activityData);

    if (viewModel.message != null && context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(viewModel.message!),
          backgroundColor: viewModel.message!.contains('success') ? Colors.green : Colors.red,
        ),
      );
      if (viewModel.message!.contains('success')) {
        Navigator.pop(context);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);
    
    return Consumer<ScheduleActivityViewModel>(
      builder: (context, viewModel, child) {
        return Stack(
          children: [
            Scaffold(
              appBar: AppBar(
                leading: IconButton(
                  onPressed: viewModel.isLoading ? null : () async {
                    final shouldPop = await _onWillPop();
                    if(shouldPop) {
                      Navigator.pop(context);
                    }
                  },
                  icon: Config.backButtonIcon,
                ),
                title: const Text('Schedule Activity Request'),
                backgroundColor: Config.primaryColor,
                foregroundColor: Colors.white,
              ),
              body: Padding(
                padding: Config.paddingScreen,
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header Info
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.blue.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.blue.withOpacity(0.3)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Icon(Icons.info_outline, color: Colors.blue, size: 20),
                                const SizedBox(width: 8),
                                Text(
                                  'Activity Request',
                                  style: TextStyle(
                                    color: Colors.blue,
                                    fontWeight: FontWeight.w600,
                                    fontSize: 16,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'Submit a request for scheduling an activity in your assigned area. This request will be reviewed and approved by the admin.',
                              style: TextStyle(fontSize: 14, color: Colors.grey[700]),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 20),

                      // Reason/Title
                      LabelText(label: 'Activity Title/Reason', isRequired: true),
                      TextField(
                        controller: reasonController,
                        focusNode: reasonNode,
                        keyboardType: TextInputType.text,
                        decoration: InputDecoration(
                          filled: true,
                          fillColor: Config.secondaryColor,
                          contentPadding: Config.paddingTextfield,
                          border: const OutlineInputBorder(
                            borderRadius: BorderRadius.all(Radius.circular(10)),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderSide: BorderSide(color: Colors.transparent),
                            borderRadius: const BorderRadius.all(Radius.circular(10)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderSide: BorderSide(color: Config.primaryColor, width: 2),
                            borderRadius: const BorderRadius.all(Radius.circular(10)),
                          ),
                          hintText: 'e.g., Community Vaccination Drive',
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Category
                      LabelText(label: 'Category', isRequired: true),
                      DropdownButtonFormField<String>(
                        value: selectedCategory,
                        decoration: InputDecoration(
                          filled: true,
                          fillColor: Config.secondaryColor,
                          contentPadding: Config.paddingTextfield,
                          border: const OutlineInputBorder(
                            borderRadius: BorderRadius.all(Radius.circular(10)),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderSide: BorderSide(color: Colors.transparent),
                            borderRadius: const BorderRadius.all(Radius.circular(10)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderSide: BorderSide(color: Config.primaryColor, width: 2),
                            borderRadius: const BorderRadius.all(Radius.circular(10)),
                          ),
                          hintText: 'Select activity category',
                        ),
                        items: categories.map((category) {
                          return DropdownMenuItem(value: category, child: Text(category));
                        }).toList(),
                        onChanged: viewModel.isLoading ? null : (value) {
                          setState(() {
                            selectedCategory = value;
                          });
                        },
                      ),
                      const SizedBox(height: 16),

                      // Barangay
                      LabelText(label: 'Target Barangay', isRequired: true),
                      DropdownButtonFormField<String>(
                        value: selectedBarangay,
                        decoration: InputDecoration(
                          filled: true,
                          fillColor: Config.secondaryColor,
                          contentPadding: Config.paddingTextfield,
                          border: const OutlineInputBorder(
                            borderRadius: BorderRadius.all(Radius.circular(10)),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderSide: BorderSide(color: Colors.transparent),
                            borderRadius: const BorderRadius.all(Radius.circular(10)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderSide: BorderSide(color: Config.primaryColor, width: 2),
                            borderRadius: const BorderRadius.all(Radius.circular(10)),
                          ),
                          hintText: 'Select target barangay',
                        ),
                        items: viewModel.barangays.map((barangay) {
                          return DropdownMenuItem<String>(
                            value: barangay['id'] as String, 
                            child: Text(barangay['name'] as String)
                          );
                        }).toList(),
                        onChanged: viewModel.isLoading ? null : (value) {
                          setState(() {
                            selectedBarangay = value;
                          });
                        },
                      ),
                      const SizedBox(height: 16),

                      // Date and Time Row
                      Row(
                        children: [
                          // Date
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                LabelText(label: 'Preferred Date', isRequired: true),
                                InkWell(
                                  onTap: viewModel.isLoading ? null : _selectDate,
                                  child: Container(
                                    padding: Config.paddingTextfield,
                                    decoration: BoxDecoration(
                                      color: Config.secondaryColor,
                                      borderRadius: BorderRadius.circular(10),
                                      border: Border.all(color: Colors.transparent),
                                    ),
                                    child: Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          selectedDate != null
                                              ? '${selectedDate!.month}/${selectedDate!.day}/${selectedDate!.year}'
                                              : 'Select Date',
                                          style: TextStyle(
                                            color: selectedDate != null ? Colors.black : Colors.grey[600],
                                          ),
                                        ),
                                        Icon(Icons.calendar_today, color: Colors.grey[600], size: 20),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 12),
                          // Time
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                LabelText(label: 'Preferred Time', isRequired: true),
                                InkWell(
                                  onTap: viewModel.isLoading ? null : _selectTime,
                                  child: Container(
                                    padding: Config.paddingTextfield,
                                    decoration: BoxDecoration(
                                      color: Config.secondaryColor,
                                      borderRadius: BorderRadius.circular(10),
                                      border: Border.all(color: Colors.transparent),
                                    ),
                                    child: Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          selectedTime != null
                                              ? selectedTime!.format(context)
                                              : 'Select Time',
                                          style: TextStyle(
                                            color: selectedTime != null ? Colors.black : Colors.grey[600],
                                          ),
                                        ),
                                        Icon(Icons.access_time, color: Colors.grey[600], size: 20),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),

                      // Details/Description
                      LabelText(label: 'Activity Details', isRequired: true),
                      TextField(
                        controller: detailsController,
                        focusNode: detailsNode,
                        keyboardType: TextInputType.multiline,
                        maxLines: 4,
                        decoration: InputDecoration(
                          filled: true,
                          fillColor: Config.secondaryColor,
                          contentPadding: Config.paddingTextfield,
                          border: const OutlineInputBorder(
                            borderRadius: BorderRadius.all(Radius.circular(10)),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderSide: BorderSide(color: Colors.transparent),
                            borderRadius: const BorderRadius.all(Radius.circular(10)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderSide: BorderSide(color: Config.primaryColor, width: 2),
                            borderRadius: const BorderRadius.all(Radius.circular(10)),
                          ),
                          hintText: 'Provide detailed description of the activity, expected participants, resources needed, etc.',
                        ),
                      ),
                      const SizedBox(height: 32),

                      // Submit Button
                      Button(
                        width: double.infinity, 
                        title: viewModel.isLoading ? 'Submitting Request...' : 'Submit Request',
                        onPressed: () {
                          if (!viewModel.isLoading) {
                            _submitRequest(context, viewModel);
                          }
                        },
                      ),
                    ],
                  ),
                ),
              ),
            ),
            // Loading overlay
            if (viewModel.isLoading)
              Container(
                color: Colors.black.withOpacity(0.5),
                child: const Center(
                  child: CircularProgressIndicator(
                    strokeWidth: 4.0,
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                  ),
                ),
              ),
          ],
        );
      },
    );
  }

  @override
  void dispose() {
    reasonController.dispose();
    detailsController.dispose();
    reasonNode.dispose();
    detailsNode.dispose();
    super.dispose();
  }
}
