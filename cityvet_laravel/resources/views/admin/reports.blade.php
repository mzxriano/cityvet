@extends('layouts.layout')

@section('content')
<!-- Success/Error Messages -->
@if(session('success'))
<div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
  {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
  {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
  <ul class="list-disc list-inside">
    @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<div x-data="reportsData()">
    <h1 class="title-style mb-[2rem]">Reports</h1>

    <!-- Tabs Navigation -->
    <div class="mb-6">
        <div class="border-b border-gray-200 whitespace-nowrap">
            <!-- 
                ADDED: 'overflow-x-auto' to enable horizontal scrolling 
                ADDED: 'whitespace-nowrap' to ensure tabs stay on one line
                NOTE: 'scrollbar-hide' is an assumed utility class if you're using Tailwind plugins, otherwise standard scrollbars will appear.
            -->
            <nav class="-mb-px flex space-x-6 sm:space-x-8" role="tablist">
                {{-- Existing Tabs --}}
                <button @click="activeTab = 'vaccination'" 
                        :class="activeTab === 'vaccination' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm transition-colors whitespace-nowrap">
                    Vaccination Reports
                    <span :class="activeTab === 'vaccination' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                          class="ml-2 text-xs px-2 py-1 rounded-full font-medium">
                        {{ $vaccinationReports->total() ?? 0 }}
                    </span>
                </button>
                <button @click="activeTab = 'bite-case'" 
                        :class="activeTab === 'bite-case' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm transition-colors whitespace-nowrap">
                    Bite Case Reports
                    <span :class="activeTab === 'bite-case' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'"
                          class="ml-2 text-xs px-2 py-1 rounded-full font-medium">
                        {{ count($biteCaseReports) ?? 0 }}
                    </span>
                </button>

                {{-- NEW TABS --}}
                <button @click="activeTab = 'register-animals'" 
                        :class="activeTab === 'register-animals' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm transition-colors whitespace-nowrap">
                    Registered Animals
                    <span :class="activeTab === 'register-animals' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'"
                          class="ml-2 text-xs px-2 py-1 rounded-full font-medium">
                        0
                    </span>
                </button>
                <button @click="activeTab = 'animals-disease'" 
                        :class="activeTab === 'animals-disease' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm transition-colors whitespace-nowrap">
                    Animals with Disease
                    <span :class="activeTab === 'animals-disease' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600'"
                          class="ml-2 text-xs px-2 py-1 rounded-full font-medium">
                        0
                    </span>
                </button>
                <button @click="activeTab = 'damaged-vaccines'" 
                        :class="activeTab === 'damaged-vaccines' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm transition-colors whitespace-nowrap">
                    Damaged Vaccines
                    <span :class="activeTab === 'damaged-vaccines' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-600'"
                          class="ml-2 text-xs px-2 py-1 rounded-full font-medium">
                        0
                    </span>
                </button>
                <button @click="activeTab = 'activities'" 
                        :class="activeTab === 'activities' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm transition-colors whitespace-nowrap">
                    User Activities
                    <span :class="activeTab === 'activities' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-600'"
                          class="ml-2 text-xs px-2 py-1 rounded-full font-medium">
                        0
                    </span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Content Includes -->
    <div x-show="activeTab === 'vaccination'">
        @include('admin.reports.vaccination', ['vaccinationReports' => $vaccinationReports, 'animalTypes' => $animalTypes, 'barangays' => $barangays, 'ownerRoles' => $ownerRoles ?? []])
    </div>
    
    <div x-show="activeTab === 'bite-case'">
        @include('admin.reports.bite_case', ['biteCaseReports' => $biteCaseReports, 'biteSpecies' => $biteSpecies ?? [], 'biteProvocations' => $biteProvocations ?? []])
    </div>
    
    <div x-show="activeTab === 'register-animals'">
        @include('admin.reports.registered_animals', ['barangays' => $barangays, 'animalTypes' => $animalTypes])
    </div>

    <div x-show="activeTab === 'animals-disease'">
        @include('admin.reports.animal_with_disease', ['barangays' => $barangays])
    </div>

    <div x-show="activeTab === 'damaged-vaccines'">
        @include('admin.reports.damaged_vaccines')
    </div>

    <div x-show="activeTab === 'activities'">
        @include('admin.reports.activities')
    </div>
</div>

<script>
    function reportsData() {
        return {
            activeTab: '{{ request("tab", "vaccination") }}',
            vaccinationSearch: '',
            biteCaseSearch: '',
            registerAnimalsSearch: '',
            diseaseAnimalsSearch: '',
            damagedVaccinesSearch: '',
            activitiesSearch: '',
        }
    }
</script>
@endsection
