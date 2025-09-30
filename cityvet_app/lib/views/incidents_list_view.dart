import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cityvet_app/models/incident_model.dart';
import 'package:cityvet_app/viewmodels/incident_viewmodel.dart';
import 'package:cityvet_app/utils/config.dart';

class IncidentsListView extends StatefulWidget {
  const IncidentsListView({super.key});

  @override
  State<IncidentsListView> createState() => _IncidentsListViewState();
}

class _IncidentsListViewState extends State<IncidentsListView> {
  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();
  String? _selectedSpecies;
  String? _selectedProvocation;
  DateTime? _fromDate;
  DateTime? _toDate;

  final List<String> _commonSpecies = [
    'Dog', 'Cat', 'Rat', 'Snake', 'Monkey', 'Other'
  ];

  final List<String> _biteProvocations = [
    'Unprovoked',
    'Provoked - Teasing',
    'Provoked - Disturbing while eating',
    'Provoked - Protecting territory',
    'Provoked - Protecting offspring',
    'Self-defense',
    'Other'
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<IncidentViewModel>(context, listen: false).loadIncidents(refresh: true);
    });

    _scrollController.addListener(() {
      if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
        // Load more incidents when near the bottom
        final incidentVM = Provider.of<IncidentViewModel>(context, listen: false);
        if (!incidentVM.isLoading && incidentVM.hasNextPage) {
          incidentVM.loadMoreIncidents();
        }
      }
    });
  }

  void _applyFilters() {
    final incidentVM = Provider.of<IncidentViewModel>(context, listen: false);
    incidentVM.loadIncidents(
      refresh: true,
      search: _searchController.text.trim().isEmpty ? null : _searchController.text.trim(),
      species: _selectedSpecies,
      fromDate: _fromDate,
      toDate: _toDate,
    );
  }

  void _clearFilters() {
    setState(() {
      _searchController.clear();
      _selectedSpecies = null;
      _selectedProvocation = null;
      _fromDate = null;
      _toDate = null;
    });
    _applyFilters();
  }

  Future<void> _selectDateRange() async {
    final DateTimeRange? picked = await showDateRangePicker(
      context: context,
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now(),
      initialDateRange: _fromDate != null && _toDate != null
          ? DateTimeRange(start: _fromDate!, end: _toDate!)
          : null,
    );

    if (picked != null) {
      setState(() {
        _fromDate = picked.start;
        _toDate = picked.end;
      });
      _applyFilters();
    }
  }

  @override
  Widget build(BuildContext context) {
    Config().init(context);

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Incident Reports',
          style: TextStyle(
            fontFamily: Config.primaryFont,
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.red[600],
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            onPressed: () => _showFilterBottomSheet(),
            icon: const Icon(Icons.filter_list),
            tooltip: 'Filter incidents',
          ),
        ],
      ),
      body: Column(
        children: [
          // Search Bar
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: TextField(
              controller: _searchController,
              style: TextStyle(fontFamily: Config.primaryFont),
              decoration: InputDecoration(
                hintText: 'Search by victim name, species, location...',
                hintStyle: TextStyle(
                  fontFamily: Config.primaryFont,
                  color: Colors.grey[500],
                ),
                prefixIcon: Icon(Icons.search, color: Colors.red[600]),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        onPressed: () {
                          _searchController.clear();
                          _applyFilters();
                        },
                        icon: const Icon(Icons.clear),
                      )
                    : null,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(color: Colors.grey[300]!),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(color: Colors.red[600]!, width: 2),
                ),
              ),
              onSubmitted: (_) => _applyFilters(),
            ),
          ),

          // Active Filters
          _buildActiveFilters(),

          // Incidents List
          Expanded(
            child: Consumer<IncidentViewModel>(
              builder: (context, incidentVM, _) {
                if (incidentVM.isLoading && incidentVM.incidents.isEmpty) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (incidentVM.error != null && incidentVM.incidents.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.error_outline, size: 64, color: Colors.grey[400]),
                        const SizedBox(height: 16),
                        Text(
                          incidentVM.error!,
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            color: Colors.grey[600],
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: () => incidentVM.refresh(),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.red[600],
                            foregroundColor: Colors.white,
                          ),
                          child: Text(
                            'Retry',
                            style: TextStyle(fontFamily: Config.primaryFont),
                          ),
                        ),
                      ],
                    ),
                  );
                }

                if (incidentVM.incidents.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.report_off, size: 64, color: Colors.grey[400]),
                        const SizedBox(height: 16),
                        Text(
                          'No incidents reported',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: 18,
                            fontWeight: FontWeight.w600,
                            color: Colors.grey[600],
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Incident reports will appear here when submitted',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            color: Colors.grey[500],
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  );
                }

                return RefreshIndicator(
                  onRefresh: () async => incidentVM.refresh(),
                  child: ListView.builder(
                    controller: _scrollController,
                    padding: const EdgeInsets.all(16),
                    itemCount: incidentVM.incidents.length + (incidentVM.hasNextPage ? 1 : 0),
                    itemBuilder: (context, index) {
                      if (index >= incidentVM.incidents.length) {
                        return const Padding(
                          padding: EdgeInsets.all(16),
                          child: Center(child: CircularProgressIndicator()),
                        );
                      }

                      final incident = incidentVM.incidents[index];
                      return _buildIncidentCard(incident);
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActiveFilters() {
    List<Widget> filters = [];

    if (_searchController.text.isNotEmpty) {
      filters.add(_buildFilterChip('Search: ${_searchController.text}', () {
        _searchController.clear();
        _applyFilters();
      }));
    }

    if (_selectedSpecies != null) {
      filters.add(_buildFilterChip('Species: $_selectedSpecies', () {
        setState(() => _selectedSpecies = null);
        _applyFilters();
      }));
    }

    if (_fromDate != null && _toDate != null) {
      filters.add(_buildFilterChip(
        'Date: ${_fromDate!.day}/${_fromDate!.month} - ${_toDate!.day}/${_toDate!.month}',
        () {
          setState(() {
            _fromDate = null;
            _toDate = null;
          });
          _applyFilters();
        }
      ));
    }

    if (filters.isEmpty) return const SizedBox();

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      color: Colors.grey[100],
      child: Wrap(
        spacing: 8,
        children: [
          ...filters,
          if (filters.isNotEmpty)
            ActionChip(
              label: Text(
                'Clear All',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  color: Colors.red[600],
                  fontSize: 12,
                ),
              ),
              onPressed: _clearFilters,
              backgroundColor: Colors.white,
              side: BorderSide(color: Colors.red[600]!),
            ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, VoidCallback onDeleted) {
    return Chip(
      label: Text(
        label,
        style: TextStyle(
          fontFamily: Config.primaryFont,
          fontSize: 12,
        ),
      ),
      onDeleted: onDeleted,
      backgroundColor: Colors.blue[100],
    );
  }

  Widget _buildIncidentCard(IncidentModel incident) {
    return Card(
      elevation: 2,
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.red[100],
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(Icons.report, color: Colors.red[600], size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        incident.victimName,
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      Text(
                        'Age: ${incident.age} • ${incident.species}',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          color: Colors.grey[600],
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
                ),
                Text(
                  _formatDate(incident.incidentTime),
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    color: Colors.grey[500],
                    fontSize: 12,
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: 12),

            // Details
            _buildDetailRow('Provocation', incident.biteProvocation),
            const SizedBox(height: 8),
            _buildDetailRow('Location', incident.locationAddress),
            if (incident.remarks != null && incident.remarks!.isNotEmpty) ...[
              const SizedBox(height: 8),
              _buildDetailRow('Remarks', incident.remarks!),
            ],

            const SizedBox(height: 12),

            // Footer
            Row(
              children: [
                Icon(Icons.access_time, size: 16, color: Colors.grey[500]),
                const SizedBox(width: 4),
                Text(
                  _formatDateTime(incident.incidentTime),
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    color: Colors.grey[500],
                    fontSize: 12,
                  ),
                ),
                const Spacer(),
                if (incident.photoPath != null)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.blue[100],
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.photo, size: 14, color: Colors.blue[700]),
                        const SizedBox(width: 4),
                        Text(
                          'Photo',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            color: Colors.blue[700],
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 80,
          child: Text(
            '$label:',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontWeight: FontWeight.w500,
              color: Colors.grey[700],
              fontSize: 14,
            ),
          ),
        ),
        Expanded(
          child: Text(
            value,
            style: TextStyle(
              fontFamily: Config.primaryFont,
              color: Colors.grey[800],
              fontSize: 14,
            ),
          ),
        ),
      ],
    );
  }

  void _showFilterBottomSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => Padding(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom,
          ),
          child: Container(
            padding: const EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Filter Incidents',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: 18,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 20),

                // Species Filter
                Text(
                  'Species',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 8),
                DropdownButtonFormField<String>(
                  value: _selectedSpecies,
                  decoration: InputDecoration(
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                    hintText: 'Select species',
                  ),
                  items: _commonSpecies.map((species) {
                    return DropdownMenuItem(value: species, child: Text(species));
                  }).toList(),
                  onChanged: (value) => setModalState(() => _selectedSpecies = value),
                ),
                
                const SizedBox(height: 16),

                // Date Range Filter
                Text(
                  'Date Range',
                  style: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 8),
                InkWell(
                  onTap: () async {
                    Navigator.pop(context);
                    await _selectDateRange();
                  },
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      border: Border.all(color: Colors.grey[300]!),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      children: [
                        Icon(Icons.date_range, color: Colors.grey[600]),
                        const SizedBox(width: 8),
                        Text(
                          _fromDate != null && _toDate != null
                              ? '${_fromDate!.day}/${_fromDate!.month}/${_fromDate!.year} - ${_toDate!.day}/${_toDate!.month}/${_toDate!.year}'
                              : 'Select date range',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            color: _fromDate != null ? Colors.black : Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),

                const SizedBox(height: 20),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () {
                          Navigator.pop(context);
                          _clearFilters();
                        },
                        child: Text(
                          'Clear',
                          style: TextStyle(fontFamily: Config.primaryFont),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: () {
                          Navigator.pop(context);
                          _applyFilters();
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.red[600],
                          foregroundColor: Colors.white,
                        ),
                        child: Text(
                          'Apply',
                          style: TextStyle(fontFamily: Config.primaryFont),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }

  String _formatDateTime(DateTime date) {
    return '${date.day}/${date.month}/${date.year} ${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}';
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }
}
