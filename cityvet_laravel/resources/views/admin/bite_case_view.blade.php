@extends('layouts.layout')

@section('title', 'Animal B                        @endforeach
                    @else
                        <div class="p-6 text-center">
                            <p class="text-gray-500">No incidents found</p>
                        </div>
                    @endif
                </div>
            </div> Management')

@section('content')
<div class="container mx-auto p-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="text-2xl font-bold mb-4 text-primary">Animal Bite Case Management</h2>
        <div class="flex gap-3 mb-4">
            <span class="bg-yellow-200 px-3 py-1 rounded">
                Pending ({{ $pendingIncidents->count() }})
            </span>
            <span class="bg-blue-200 px-3 py-1 rounded">
                Under Review ({{ $underReviewIncidents->count() }})
            </span>
            <span class="bg-red-200 px-3 py-1 rounded">
                Confirmed ({{ $confirmedIncidents->count() }})
            </span>
            <span class="bg-purple-200 px-3 py-1 rounded">
                Disputed ({{ $disputedIncidents->count() }})
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Map Section -->
        <div class="lg:col-span-2">
            <div class="bg-white border rounded-lg">
                <div class="bg-blue-500 text-white px-4 py-2 rounded-t-lg">
                    <h3 class="text-lg font-semibold">Incident Map</h3>
                </div>
                <div style="height: 600px; width: 100%;">
                    <div id="incidentMap" style="height: 600px; width: 100%;"></div>
                </div>
            </div>
        </div>

        <!-- Incident List Section -->
        <div class="lg:col-span-1">
            <div class="bg-white border rounded-lg">
                <div class="bg-yellow-500 text-black px-4 py-2 rounded-t-lg">
                    <h3 class="text-lg font-semibold">All Incidents</h3>
                </div>
                <div style="max-height: 600px; overflow-y: auto;">
                    @if($allIncidents->count() > 0)
                        @foreach($allIncidents as $incident)
                            <div class="incident-item border-b p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700" 
                                 data-incident-id="{{ $incident->id }}"
                                 data-lat="{{ $incident->latitude }}" 
                                 data-lng="{{ $incident->longitude }}"
                                 onclick="viewIncidentDetails({{ $incident->id }})">
                                <h4 class="font-bold text-primary">{{ $incident->victim_name }}</h4>
                                <p class="text-sm text-secondary">{{ $incident->incident_time->format('M d, Y') }}</p>
                                <p class="text-sm text-secondary">{{ Str::limit($incident->location_address, 30) }}</p>
                                <p class="text-sm text-secondary mb-3">{{ $incident->species }} ({{ $incident->age }}y)</p>
                                @if($incident->status === 'pending')
                                    <span class="bg-yellow-200 px-2 py-1 text-xs rounded">Pending</span>
                                @elseif($incident->status === 'under_review')
                                    <span class="bg-blue-200 px-2 py-1 text-xs rounded">Under Review</span>
                                @elseif($incident->status === 'confirmed')
                                    <span class="bg-green-200 px-2 py-1 text-xs rounded">Confirmed</span>
                                @elseif($incident->status === 'disputed')
                                    <span class="bg-red-200 px-2 py-1 text-xs rounded">Disputed</span>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="p-4 text-center">
                            <p>No incidents found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incident Details Modal -->
<div id="incidentModal" class="fixed inset-0 z-[9999] hidden bg-black bg-opacity-50 items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Incident Details</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="modalBody" class="p-6 overflow-y-auto max-h-[60vh]">
            <!-- Dynamic content will be loaded here -->
        </div>
        <div class="flex justify-end items-center gap-3 p-6 border-t border-gray-200 dark:border-gray-700">
            <button onclick="closeModal()" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Close
            </button>
            <div id="modalActions">
                <!-- Action buttons will be added dynamically -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
console.log('Script loaded');

// Simple test to create map
window.onload = function() {
    console.log('Window loaded, creating map...');
    
    try {
        // Create map
        var map = L.map('incidentMap').setView([15.9750, 120.5710], 13);
        console.log('Map created');
        
        // Add tiles
        L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png', {
            attribution: '© CartoDB'
        }).addTo(map);
        console.log('Tiles added');
        
        // Test marker
        L.marker([15.9750, 120.5710]).addTo(map)
            .bindPopup('Urdaneta City')
            .openPopup();
        console.log('Test marker added');
        
        // Add incident data
        var incidents = @json($allIncidents);
        console.log('Incidents:', incidents);
        
        incidents.forEach(function(incident) {
            var color = incident.status === 'confirmed' ? 'red' : 'orange';
            
            var circle = L.circle([incident.latitude, incident.longitude], {
                color: color,
                fillColor: color,
                fillOpacity: 0.8,
                radius: 30
            }).addTo(map);
            
            // Add click event to show full details modal
            circle.on('click', function(e) {
                L.DomEvent.stopPropagation(e);
                viewIncidentDetails(incident.id);
            });
            
            // Optional: Add simple tooltip on hover
            circle.bindTooltip(`${incident.victim_name} - ${incident.species}`, {
                permanent: false,
                direction: 'top'
            });
        });
        
    } catch (error) {
        console.error('Map error:', error);
        alert('Map failed to load: ' + error.message);
    }
};

// Function to show modal
function showModal() {
    const modal = document.getElementById('incidentModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Function to close modal
function closeModal() {
    const modal = document.getElementById('incidentModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Function to view incident details in modal
function viewIncidentDetails(incidentId) {
    // Make AJAX request to get incident details
    fetch(`/admin/incidents/${incidentId}`)
        .then(response => response.json())
        .then(data => {
            // Populate modal with incident data
            const modalBody = document.getElementById('modalBody');
            const modalActions = document.getElementById('modalActions');
            
            modalBody.innerHTML = `
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-lg mb-3 text-gray-900 dark:text-gray-100">Victim Information</h4>
                        <div class="space-y-2">
                            <p class="text-gray-700 dark:text-gray-300"><span class="font-medium">Name:</span> ${data.victim_name}</p>
                            <p class="text-gray-700 dark:text-gray-300"><span class="font-medium">Age:</span> ${data.age} years old</p>
                            <p class="text-gray-700 dark:text-gray-300"><span class="font-medium">Species:</span> ${data.species}</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-lg mb-3 text-gray-900 dark:text-gray-100">Incident Details</h4>
                        <div class="space-y-2">
                            <p class="text-gray-700 dark:text-gray-300"><span class="font-medium">Date:</span> ${new Date(data.incident_time).toLocaleDateString()}</p>
                            <p class="text-gray-700 dark:text-gray-300"><span class="font-medium">Bite Provocation:</span> ${data.bite_provocation}</p>
                            <p class="text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Status:</span> 
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ml-2 ${data.status === 'confirmed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'}">${data.status.replace('_', ' ')}</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <h4 class="font-semibold text-lg mb-3 text-gray-900 dark:text-gray-100">Location</h4>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">${data.location_address}</p>
                    ${data.remarks ? `
                        <h4 class="font-semibold text-lg mb-3 text-gray-900 dark:text-gray-100">Remarks</h4>
                        <p class="text-gray-700 dark:text-gray-300 mb-4">${data.remarks}</p>
                    ` : ''}
                    <div class="grid md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400">
                        <p><span class="font-medium">Reported by:</span> ${data.reported_by || 'Unknown'}</p>
                        <p><span class="font-medium">Reported at:</span> ${new Date(data.reported_at).toLocaleDateString()}</p>
                        ${data.confirmed_by ? `<p><span class="font-medium">Confirmed by:</span> ${data.confirmed_by}</p>` : ''}
                        ${data.confirmed_at ? `<p><span class="font-medium">Confirmed at:</span> ${new Date(data.confirmed_at).toLocaleDateString()}</p>` : ''}
                    </div>
                </div>
                ${data.photo_path ? `
                    <div class="mt-6">
                        <h4 class="font-semibold text-lg mb-3 text-gray-900 dark:text-gray-100">Photo</h4>
                        <img src="/storage/${data.photo_path}" alt="Incident Photo" class="max-w-full h-auto max-h-72 rounded-lg">
                    </div>
                ` : ''}
            `;
            
            // Admin users can only view incidents, not manage status
            // Status management is handled by barangay personnel in the mobile app
            modalActions.innerHTML = '';
            
            // Show modal
            showModal();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading incident details');
        });
}

// Admin users can only view incidents
// Status management (confirm/dispute) is handled by barangay personnel in the mobile app

// Close modal when clicking outside
document.getElementById('incidentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection
