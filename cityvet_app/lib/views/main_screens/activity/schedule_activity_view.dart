import 'package:cityvet_app/components/button.dart';
import 'package:cityvet_app/components/label_text.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/viewmodels/schedule_activity_view_model.dart';
import 'package:file_picker/file_picker.dart';
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

  Set<String> selectedBarangays = {};
  bool isSelectAllBarangays = false;

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

  // File memo fields
  List<PlatformFile> attachedFiles = [];

  Future<void> _pickFiles() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf'],
      allowMultiple: true,
    );
    if (result != null) {
      setState(() {
        attachedFiles.addAll(result.files);
      });
    }
  }

  void _removeFile(int index) {
    setState(() {
      attachedFiles.removeAt(index);
    });
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

  void _toggleSelectAllBarangays(ScheduleActivityViewModel viewModel, bool? value) {
    if (value == null) return;
    setState(() {
      isSelectAllBarangays = value;
      if (isSelectAllBarangays) {
        selectedBarangays = viewModel.barangays
            .map((b) => b['id'] as String)
            .toSet();
      } else {
        selectedBarangays.clear();
      }
    });
  }

  void _submitRequest(BuildContext context, ScheduleActivityViewModel viewModel) async {
    if (reasonController.text.trim().isEmpty ||
        selectedCategory == null ||
        selectedBarangays.isEmpty ||
        selectedDate == null ||
        selectedTime == null ||
        detailsController.text.trim().isEmpty) {

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please fill in all required fields.')),
      );
      return;
    }

    final selectedBarangaysString = selectedBarangays.join(',');

    final activityData = {
      'reason': reasonController.text.trim(),
      'category': selectedCategory!,
      'barangay_ids': selectedBarangaysString,
      'date': '${selectedDate!.year}-${selectedDate!.month.toString().padLeft(2, '0')}-${selectedDate!.day.toString().padLeft(2, '0')}',
      'time': '${selectedTime!.hour.toString().padLeft(2, '0')}:${selectedTime!.minute.toString().padLeft(2, '0')}',
      'details': detailsController.text.trim(),
      'status': 'pending',
      'memos': attachedFiles,
    };

    await viewModel.submitActivityRequest(activityData);

    if (viewModel.message != null && context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(viewModel.message!),
          backgroundColor: viewModel.message!.contains('success') ? Colors.green : Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    return Consumer<ScheduleActivityViewModel>(
      builder: (context, viewModel, child) {
        final allBarangayIds = viewModel.barangays.map((b) => b['id'] as String).toSet();
        final isAllSelected = selectedBarangays.length == allBarangayIds.length && allBarangayIds.isNotEmpty;
        final isSomeSelected = selectedBarangays.isNotEmpty && !isAllSelected;

        return Stack(
          children: [
            SingleChildScrollView(
              child: Padding( 
                padding: const EdgeInsets.symmetric(horizontal: 16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
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
                        enabledBorder: const OutlineInputBorder(
                          borderSide: BorderSide(color: Colors.transparent),
                          borderRadius: BorderRadius.all(Radius.circular(10)),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderSide: BorderSide(color: Config.primaryColor, width: 2),
                          borderRadius: const BorderRadius.all(Radius.circular(10)),
                        ),
                        hintText: 'e.g., Community Vaccination Drive',
                      ),
                    ),
                    const SizedBox(height: 16),

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
                        enabledBorder: const OutlineInputBorder(
                          borderSide: BorderSide(color: Colors.transparent),
                          borderRadius: BorderRadius.all(Radius.circular(10)),
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
                    LabelText(label: 'Target Barangay(s)', isRequired: true),
                    
                    Container(
                      decoration: BoxDecoration(
                        color: Config.secondaryColor,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: SizedBox(
                        height: 200.0, 
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if (viewModel.barangays.isNotEmpty)
                              Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 8.0),
                                child: CheckboxListTile(
                                  title: const Text(
                                    'Select All Barangays',
                                    style: TextStyle(fontWeight: FontWeight.bold),
                                  ),
                                  value: isAllSelected,
                                  onChanged: viewModel.isLoading ? null : (value) {
                                    _toggleSelectAllBarangays(viewModel, value);
                                  },
                                  tristate: isSomeSelected,
                                  controlAffinity: ListTileControlAffinity.leading,
                                  contentPadding: EdgeInsets.zero,
                                ),
                              ),
                            
                            const Divider(height: 1, thickness: 1, indent: 8, endIndent: 8),

                            Expanded(
                              child: ListView.builder(
                                itemCount: viewModel.barangays.length,
                                itemBuilder: (context, index) {
                                  final barangay = viewModel.barangays[index];
                                  final barangayId = barangay['id'] as String;
                                  final barangayName = barangay['name'] as String;
                                  final isChecked = selectedBarangays.contains(barangayId);

                                  return CheckboxListTile(
                                    title: Text(barangayName),
                                    value: isChecked,
                                    onChanged: viewModel.isLoading ? null : (bool? value) {
                                      setState(() {
                                        if (value == true) {
                                          selectedBarangays.add(barangayId);
                                        } else {
                                          selectedBarangays.remove(barangayId);
                                        }
                                      });
                                    },
                                    controlAffinity: ListTileControlAffinity.leading,
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 8.0),
                                  );
                                },
                              ),
                            ),
                            
                            if (viewModel.isLoading && viewModel.barangays.isEmpty)
                              const Center(child: Text('Loading barangays...')),
                            if (!viewModel.isLoading && viewModel.barangays.isEmpty)
                              const Center(child: Text('No barangays available.')),
                          ],
                        ),
                      ),
                    ),
                    
                    const SizedBox(height: 16),

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
                        enabledBorder: const OutlineInputBorder(
                          borderSide: BorderSide(color: Colors.transparent),
                          borderRadius: BorderRadius.all(Radius.circular(10)),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderSide: BorderSide(color: Config.primaryColor, width: 2),
                          borderRadius: const BorderRadius.all(Radius.circular(10)),
                        ),
                        hintText: 'Provide detailed description of the activity, expected participants, resources needed, etc.',
                      ),
                    ),
                    const SizedBox(height: 16),

                    LabelText(label: 'Attach Memos (PDF files, optional)', isRequired: false),
                    ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: attachedFiles.length,
                      itemBuilder: (context, index) {
                        final file = attachedFiles[index];
                        return ListTile(
                          leading: const Icon(Icons.picture_as_pdf, color: Colors.red),
                          title: Text(file.name),
                          subtitle: Text('${(file.size / 1024).toStringAsFixed(1)} KB'),
                          trailing: IconButton(
                            icon: const Icon(Icons.remove_circle, color: Colors.red),
                            onPressed: () => _removeFile(index),
                          ),
                        );
                      },
                    ),
                    Align(
                      alignment: Alignment.centerLeft,
                      child: TextButton.icon(
                        icon: const Icon(Icons.attach_file, color: Colors.green),
                        label: const Text('Attach PDF'),
                        onPressed: _pickFiles,
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
                    const SizedBox(height: 20),
                  ],
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