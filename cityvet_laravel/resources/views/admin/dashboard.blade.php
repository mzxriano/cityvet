@extends('layouts.layout')

@section('content')
  <h1 class="title-style mb-8 lg:mb-[50px] text-2xl lg:text-3xl dark:text-white">Dashboard</h1>

  <section class="mb-6 lg:mb-[2rem]">
    <div class="flex flex-col lg:flex-row justify-between gap-4 lg:gap-[5rem] mb-6 lg:mb-[2rem]">
      
      <!-- Total Users -->
      <div 
        class="bg-white dark:bg-gray-800 flex flex-col flex-1 p-4 lg:p-[2rem] rounded-lg lg:rounded-[1rem] shadow-md cursor-pointer hover:shadow-lg transition-shadow" 
        data-modal-target="userRoleModal" 
        data-modal-toggle="userRoleModal"
      >
        <div class="mb-4 lg:mb-[2rem] text-gray-500 dark:text-gray-400 text-sm lg:text-base">
          Total Users
          <div class="text-xs text-secondary mt-1 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md w-fit">
            Overall
          </div>
        </div>
        <div class="text-xl lg:text-[2rem] font-semibold text-[#0E0E0E] dark:text-white">
          {{ $totalUsers }}
        </div>
      </div>

      <!-- Total Registered Animals -->
      <div class="bg-white dark:bg-gray-800 flex flex-col flex-1 p-4 lg:p-[2rem] rounded-lg lg:rounded-[1rem] shadow-md hover:shadow-lg transition-shadow">
        <div class="mb-4 lg:mb-[2rem] text-gray-500 dark:text-gray-400 text-sm lg:text-base">
          Total Registered Animals
          <div class="text-xs text-secondary mt-1 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md w-fit">
            Overall
          </div>
        </div>
        <div class="text-xl lg:text-[2rem] font-semibold text-[#0E0E0E] dark:text-white">
          {{ $totalAnimals }}
        </div>
      </div>

      <!-- Total Vaccinated Animals -->
      <div class="bg-white dark:bg-gray-800 flex flex-col flex-1 p-4 lg:p-[2rem] rounded-lg lg:rounded-[1rem] shadow-md hover:shadow-lg transition-shadow">
        <div class="mb-4 lg:mb-[2rem] text-gray-500 dark:text-gray-400 text-sm lg:text-base">
          Total Vaccinated Animals
          <div class="text-xs text-secondary mt-1 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md w-fit">
            Overall
          </div>
        </div>
        <div class="text-xl lg:text-[2rem] font-semibold text-[#0E0E0E] dark:text-white">
          {{ $totalVaccinatedAnimals }}
        </div>
      </div>

    </div>
  </section>


  <!-- Modal -->
  <div id="userRoleModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50">
    <div class="flex items-center justify-center min-h-screen px-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-md w-full p-6 relative">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">User Role Breakdown</h2>
        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
            <li>Pet Owner: <span class="font-semibold">{{ $userTypeCounts['pet_owner'] ?? 0 }}</span></li>
            <li>Livestock Owner: <span class="font-semibold">{{ $userTypeCounts['livestock_owner'] ?? 0 }}</span></li>
            <li>Poultry Owner: <span class="font-semibold">{{ $userTypeCounts['poultry_owner'] ?? 0 }}</span></li>
            <li>Staff: <span class="font-semibold">{{ $userTypeCounts['staff'] ?? 0 }}</span></li>
            <li>Veterinarian: <span class="font-semibold">{{ $userTypeCounts['veterinarian'] ?? 0 }}</span></li>
            <li>Aew: <span class="font-semibold">{{ $userTypeCounts['aew'] ?? 0 }}</span></li>
            <li>Sub Admin: <span class="font-semibold">{{ $userTypeCounts['sub_admin'] ?? 0 }}</span></li>
            <li>Barangay Personel: <span class="font-semibold">{{ $userTypeCounts['barangay_personel'] ?? 0 }}</span></li>
        </ul>
        <button 
          class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-xl" 
          onclick="document.getElementById('userRoleModal').classList.add('hidden')"
        >&times;</button>
      </div>
    </div>
  </div>

  <!-- Weekly Bite Case Summary -->
  <section class="mb-6 lg:mb-[2rem]">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg lg:rounded-[1rem] p-4 lg:p-[2rem]">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg lg:text-xl font-semibold text-gray-700 dark:text-gray-300">
          This Week's Bite Case Report
        </h3>
        <span class="text-sm text-gray-500 dark:text-gray-400">
          {{ $weeklyBiteStats['weekPeriod'] }}
        </span>
      </div>
      
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Confirmed Cases -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 p-4 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-red-600 dark:text-red-400 font-medium">Total Confirmed</p>
              <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $weeklyBiteStats['thisWeek'] }}</p>
            </div>
            <div class="text-red-500">
              <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          @if($weeklyBiteStats['percentageChange'] != 0)
            <div class="mt-2 flex items-center text-xs">
              @if($weeklyBiteStats['percentageChange'] > 0)
                <span class="text-red-600 dark:text-red-400">↑ {{ abs($weeklyBiteStats['percentageChange']) }}%</span>
              @else
                <span class="text-green-600 dark:text-green-400">↓ {{ abs($weeklyBiteStats['percentageChange']) }}%</span>
              @endif
              <span class="text-gray-500 dark:text-gray-400 ml-1">from last week</span>
            </div>
          @else
            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">No change from last week</div>
          @endif
        </div>

        <!-- Dog Bites -->
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 p-4 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-orange-600 dark:text-orange-400 font-medium">Dog Bites</p>
              <p class="text-2xl font-bold text-orange-700 dark:text-orange-300">{{ $weeklyBiteStats['dogBites'] }}</p>
            </div>
            <div class="text-orange-500">
              <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
              </svg>
            </div>
          </div>
          <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            {{ $weeklyBiteStats['thisWeek'] > 0 ? round(($weeklyBiteStats['dogBites'] / $weeklyBiteStats['thisWeek']) * 100, 1) : 0 }}% of total cases
          </div>
        </div>

        <!-- Cat Bites -->
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-4 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-purple-600 dark:text-purple-400 font-medium">Cat Bites</p>
              <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ $weeklyBiteStats['catBites'] }}</p>
            </div>
            <div class="text-purple-500">
              <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            {{ $weeklyBiteStats['thisWeek'] > 0 ? round(($weeklyBiteStats['catBites'] / $weeklyBiteStats['thisWeek']) * 100, 1) : 0 }}% of total cases
          </div>
        </div>

        <!-- Other Animal Bites -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Other Animals</p>
              <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $weeklyBiteStats['otherBites'] }}</p>
            </div>
            <div class="text-blue-500">
              <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            {{ $weeklyBiteStats['thisWeek'] > 0 ? round(($weeklyBiteStats['otherBites'] / $weeklyBiteStats['thisWeek']) * 100, 1) : 0 }}% of total cases
          </div>
        </div>
      </div>

      @if($weeklyBiteStats['thisWeek'] === 0)
        <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
          <div class="flex items-center">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-sm text-green-700 dark:text-green-300 font-medium">No confirmed bite cases reported this week.</span>
          </div>
        </div>
      @elseif($weeklyBiteStats['thisWeek'] > 5)
        <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
          <div class="flex items-center">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-sm text-red-700 dark:text-red-300 font-medium">High bite case activity this week. Consider increased awareness campaigns.</span>
          </div>
        </div>
      @endif
    </div>
  </section>

  <!-- Case Report Section -->
  <section class="mb-6 lg:mb-[2rem]">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg lg:rounded-[1rem] p-4 lg:p-[2rem]">
      <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 lg:gap-0"> 
        <div class="text-lg lg:text-[25px] text-gray-700 dark:text-gray-300 font-medium">
          Confirmed Bite Case Report (Chart Analysis)
        </div>

        <!-- Filter Section -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 lg:gap-4">
          <!-- Filter by Barangay -->
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <label class="text-sm text-gray-600 dark:text-gray-400 lg:hidden">Barangay:</label>
            <select id="barangay" class="w-full sm:w-auto px-3 py-2 border-2 bg-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-full text-gray-600 text-sm lg:text-base">
              <option value="all">All Barangays</option>
              @foreach($barangays as $barangay)
                <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
              @endforeach
            </select>
          </div>

          <!-- Filter by Date Range (Daily or Monthly) -->
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <label class="text-sm text-gray-600 lg:hidden">Period:</label>
            <select id="date-range" class="w-full sm:w-auto px-3 py-2 border-2 bg-transparent rounded-full text-gray-600 text-sm lg:text-base">
              <option value="daily">Daily</option>
              <option value="monthly">Monthly</option>
              <option value="yearly">Yearly</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Graph Section -->
      <div class="mt-6 lg:mt-[2rem]">
        <div class="bg-gray-100 h-[250px] sm:h-[300px] lg:h-[350px] rounded-lg p-2">
          <!-- Canvas where the graph will be rendered -->
          <canvas id="myChart"></canvas>
        </div>
      </div>
    </div>
  </section>

  <section>
    <!-- Bottom Cards - Stack on mobile/tablet, flex on desktop -->
    <div class="flex flex-col lg:flex-row gap-4 lg:gap-[2rem]">
    <!-- Vaccinated per barangay -->
    <div class="flex flex-col w-full lg:w-2/5 bg-white dark:bg-gray-800 rounded-xl shadow-md p-4 lg:p-[2rem]">
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 lg:mb-5">
        <div class="text-lg lg:text-[20px] text-gray-700 dark:text-gray-300 font-medium">
          Vaccinated Animal Per Barangay
        </div>
        <!-- Year Filter -->
        <div class="flex items-center gap-2">
          <label class="text-sm text-gray-600 dark:text-gray-400">Year:</label>
          <select id="vaccination-year" class="px-3 py-2 border-2 bg-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-full text-gray-600 text-sm">
            <option value="all">All Years</option>
            @foreach($availableYears as $year)
              <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
          </select>
        </div>
      </div>
      
      <!-- Horizontal Bar Chart -->
      <div class="flex-1 min-h-[400px] max-h-[600px] overflow-y-auto">
        <div style="height: {{ count($barangays) * 25 + 100 }}px; min-height: 400px;">
          <canvas id="vaccinatedBarangayChart"></canvas>
        </div>
      </div>
    </div>

      
      <!-- Animal per category -->
      <div class="flex flex-col flex-1 bg-white dark:bg-gray-800 rounded-xl shadow-md p-4 lg:p-[2rem]">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4">
          <div class="text-lg lg:text-[20px] text-gray-700 dark:text-gray-300 font-medium">
            Animal per Category
          </div>
          <!-- Barangay Filter -->
          <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600 dark:text-gray-400">Barangay:</label>
            <select id="category-barangay" class="px-3 py-2 border-2 bg-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-full text-gray-600 text-sm">
              <option value="all">All Barangays</option>
              @foreach($barangays as $barangay)
                <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <!-- Pie chart -->
        <div class="flex-1 min-h-[250px] sm:min-h-[300px]">
          <canvas id="animalPieChart"></canvas>
        </div>
      </div>
    </div>
  </section>

  <!-- Chart.js Script -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
  document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('myChart').getContext('2d');
    const pieCtx = document.getElementById('animalPieChart').getContext('2d');
    const barCtx = document.getElementById('vaccinatedBarangayChart').getContext('2d');

    // Dynamic bite case data from backend
    const biteCaseData = {!! json_encode($biteCases) !!};
    


    // Helper to get filtered data
    function getFilteredData(period, barangay) {
      let data = biteCaseData[period];
      
      if (barangay && barangay !== 'all') {
        // Filter by specific barangay
        return {
          labels: data.labels,
          dogBite: data.dogBitesByBarangay[barangay] || Array(data.labels.length).fill(0),
          catBite: data.catBitesByBarangay[barangay] || Array(data.labels.length).fill(0),
          otherBite: data.otherBitesByBarangay ? (data.otherBitesByBarangay[barangay] || Array(data.labels.length).fill(0)) : Array(data.labels.length).fill(0)
        };
      } else {
        // Show all barangays combined
        return {
          labels: data.labels,
          dogBite: data.dogBite,
          catBite: data.catBite,
          otherBite: data.otherBite || Array(data.labels.length).fill(0)
        };
      }
    }

    // Initial chart data
    let selectedPeriod = 'daily';
    let selectedBarangay = 'all';
    let selectedData = getFilteredData(selectedPeriod, selectedBarangay);
    


    // Animal per Category Pie chart data
    const animalCategories = {
        labels: {!! json_encode($animalsPerCategory->pluck('type')) !!},
        data: {!! json_encode($animalsPerCategory->pluck('total')) !!}
      };

    // Animal per Category by Barangay data
    const animalCategoriesByBarangay = {!! json_encode($animalsPerCategoryByBarangay) !!};

    // Vaccinated Animals per Barangay chart data
    const vaccinatedBarangayData = {
        labels: {!! json_encode($barangays->pluck('name')) !!},
        data: {!! json_encode($barangays->pluck('vaccinated_animals_count')) !!}
      };
    
    // Vaccination data by year
    const vaccinationDataByYear = {!! json_encode($vaccinationDataByYear) !!};
    
    // Helper function to get animal category data based on barangay filter
    function getAnimalCategoryData(barangayId) {
      if (barangayId && barangayId !== 'all') {
        const barangayData = animalCategoriesByBarangay[barangayId];
        if (barangayData) {
          return {
            labels: barangayData.labels,
            data: barangayData.data
          };
        }
        return { labels: [], data: [] };
      } else {
        return {
          labels: animalCategories.labels,
          data: animalCategories.data
        };
      }
    }
    
    // Helper function to get vaccination data based on year filter
    function getVaccinationData(year) {
      if (year && year !== 'all') {
        const yearData = vaccinationDataByYear[year];
        if (yearData) {
          return {
            labels: yearData.labels,
            data: yearData.data
          };
        }
        return { labels: [], data: [] };
      } else {
        // Calculate total for all years
        const allYears = Object.keys(vaccinationDataByYear);
        if (allYears.length === 0) {
          return {
            labels: vaccinatedBarangayData.labels,
            data: vaccinatedBarangayData.data
          };
        }
        
        const barangayNames = vaccinationDataByYear[allYears[0]].labels;
        const totals = new Array(barangayNames.length).fill(0);
        
        allYears.forEach(y => {
          vaccinationDataByYear[y].data.forEach((count, index) => {
            totals[index] += parseInt(count) || 0;
          });
        });
        
        return {
          labels: barangayNames,
          data: totals
        };
      }
    }
    


    // Responsive chart options
    const getResponsiveOptions = () => {
      const isMobile = window.innerWidth < 640;
      const isTablet = window.innerWidth < 1024;
      
      return {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              font: {
                size: isMobile ? 14 : 16
              },
              stepSize: 1,
              callback: function(value) {
                if (Number.isInteger(value)) {
                  return value;
                }
              }
            }
          },
          x: {
            ticks: {
              font: {
                size: isMobile ? 14 : 16
              },
              maxRotation: isMobile ? 45 : 0
            }
          }
        },
        plugins: {
          legend: {
            labels: {
              font: {
                size: isMobile ? 16 : 18
              }
            }
          }
        },
        barThickness: isMobile ? 12 : isTablet ? 15 : 18,
        categoryPercentage: 0.8,
        barPercentage: isMobile ? 0.6 : 0.5,
      };
    };

    // Create the bar chart
    let myChart = null;
    try {
      if (selectedData && selectedData.labels && selectedData.dogBite && selectedData.catBite && selectedData.otherBite) {
        myChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: selectedData.labels,
            datasets: [{
              label: 'Dog Bite',
              data: selectedData.dogBite,
              backgroundColor: '#F59E0B',
              borderColor: '#D97706',
              borderWidth: 1
            }, {
              label: 'Cat Bite',
              data: selectedData.catBite,
              backgroundColor: '#8B5CF6',
              borderColor: '#7C3AED',
              borderWidth: 1
            }, {
              label: 'Others',
              data: selectedData.otherBite,
              backgroundColor: '#3B82F6',
              borderColor: '#2563EB',
              borderWidth: 1
            }]
          },
          options: getResponsiveOptions()
        });
      } else {
        console.error('Invalid bite case data:', selectedData);
      }
    } catch (error) {
      console.error('Error creating bite case chart:', error);
    }

    // Responsive pie chart options
    const getPieChartOptions = () => {
      const isMobile = window.innerWidth < 640;
      
      return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: isMobile ? 'bottom' : 'right',
            labels: {
              font: {
                size: isMobile ? 14 : 16
              },
              padding: isMobile ? 12 : 20,
              usePointStyle: true
            }
          },
          tooltip: {
            titleFont: {
              size: isMobile ? 14 : 16
            },
            bodyFont: {
              size: isMobile ? 12 : 14
            }
          }
        }
      };
    };

    // Create the pie chart for Animal Categories
    let animalPieChart = null;
    let selectedCategoryBarangay = 'all';
    let currentCategoryData = getAnimalCategoryData(selectedCategoryBarangay);
    
    try {
      if (currentCategoryData && currentCategoryData.labels && currentCategoryData.data) {
        animalPieChart = new Chart(pieCtx, {
          type: 'pie',
          data: {
            labels: currentCategoryData.labels,
            datasets: [{
              label: 'Animal Categories',
              data: currentCategoryData.data,
              backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4'],
            }]
          },
          options: getPieChartOptions()
        });
      } else {
        console.error('Invalid animal categories data:', currentCategoryData);
      }
    } catch (error) {
      console.error('Error creating animal pie chart:', error);
    }

    // Responsive horizontal bar chart options
    const getHorizontalBarOptions = () => {
      const isMobile = window.innerWidth < 640;
      const barangayCount = vaccinatedBarangayData.labels.length;
      
      return {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        layout: {
          padding: {
            top: 10,
            bottom: 10,
            left: 10,
            right: 10
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            ticks: {
              font: {
                size: isMobile ? 14 : 16
              },
              stepSize: 1,
              callback: function(value) {
                if (Number.isInteger(value)) {
                  return value;
                }
              }
            },
            grid: {
              display: true,
              color: 'rgba(0,0,0,0.1)'
            }
          },
          y: {
            ticks: {
              font: {
                size: isMobile ? 12 : 14
              },
              maxRotation: 0,
              callback: function(value, index) {
                const label = this.getLabelForValue(value);
                // Truncate long barangay names for better display
                return label.length > 15 ? label.substring(0, 15) + '...' : label;
              }
            },
            grid: {
              display: false
            }
          }
        },
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            titleFont: {
              size: isMobile ? 14 : 16
            },
            bodyFont: {
              size: isMobile ? 12 : 14
            },
            callbacks: {
              title: function(context) {
                return context[0].label; // Show full barangay name in tooltip
              },
              label: function(context) {
                return `Vaccinated Animals: ${context.parsed.x}`;
              }
            }
          }
        },
        elements: {
          bar: {
            barThickness: Math.max(12, Math.min(25, 400 / barangayCount)) // Dynamic bar thickness
          }
        }
      };
    };

    // Create the horizontal bar chart for Vaccinated Animals per Barangay
    let vaccinatedBarangayChart = null;
    let selectedVaccinationYear = document.getElementById('vaccination-year') ? document.getElementById('vaccination-year').value : 'all';
    let currentVaccinationData = getVaccinationData(selectedVaccinationYear);
    
    try {
      if (currentVaccinationData && currentVaccinationData.labels && currentVaccinationData.data) {
        vaccinatedBarangayChart = new Chart(barCtx, {
          type: 'bar',
          data: {
            labels: currentVaccinationData.labels,
            datasets: [{
              label: 'Vaccinated Animals',
              data: currentVaccinationData.data,
              backgroundColor: '#8B5CF6',
              borderColor: '#7C3AED',
              borderWidth: 1
            }]
          },
          options: getHorizontalBarOptions()
        });
      } else {
        console.error('Invalid vaccinated barangay data:', currentVaccinationData);
      }
    } catch (error) {
      console.error('Error creating vaccinated barangay chart:', error);
    }

    // Update charts on window resize
    let resizeTimeout;
    window.addEventListener('resize', function() {
      clearTimeout(resizeTimeout);
      resizeTimeout = setTimeout(function() {
        if (myChart) {
          myChart.options = getResponsiveOptions();
          myChart.update();
        }
        if (animalPieChart) {
          animalPieChart.options = getPieChartOptions();
          animalPieChart.update();
        }
        if (vaccinatedBarangayChart) {
          vaccinatedBarangayChart.options = getHorizontalBarOptions();
          vaccinatedBarangayChart.update();
        }
      }, 250);
    });

    // Handle the filter changes
    document.getElementById('date-range').addEventListener('change', function () {
      selectedPeriod = this.value;
      selectedData = getFilteredData(selectedPeriod, selectedBarangay);
      
      if (myChart && selectedData) {
        myChart.data.labels = selectedData.labels;
        myChart.data.datasets[0].data = selectedData.dogBite;
        myChart.data.datasets[1].data = selectedData.catBite;
        myChart.data.datasets[2].data = selectedData.otherBite;
        myChart.update();
      }
    });

    document.getElementById('barangay').addEventListener('change', function () {
      selectedBarangay = this.value;
      selectedData = getFilteredData(selectedPeriod, selectedBarangay);
      
      if (myChart && selectedData) {
        myChart.data.labels = selectedData.labels;
        myChart.data.datasets[0].data = selectedData.dogBite;
        myChart.data.datasets[1].data = selectedData.catBite;
        myChart.data.datasets[2].data = selectedData.otherBite;
        myChart.update();
      }
    });

    // Handle category barangay filter change
    document.getElementById('category-barangay').addEventListener('change', function () {
      selectedCategoryBarangay = this.value;
      currentCategoryData = getAnimalCategoryData(selectedCategoryBarangay);
      
      if (animalPieChart && currentCategoryData) {
        animalPieChart.data.labels = currentCategoryData.labels;
        animalPieChart.data.datasets[0].data = currentCategoryData.data;
        animalPieChart.update();
      }
    });

    // Handle vaccination year filter change
    document.getElementById('vaccination-year').addEventListener('change', function () {
      selectedVaccinationYear = this.value;
      currentVaccinationData = getVaccinationData(selectedVaccinationYear);
      
      if (vaccinatedBarangayChart && currentVaccinationData) {
        vaccinatedBarangayChart.data.labels = currentVaccinationData.labels;
        vaccinatedBarangayChart.data.datasets[0].data = currentVaccinationData.data;
        vaccinatedBarangayChart.update();
      }
    });
  });

  // Modal for detailed user counts
  document.querySelectorAll('[data-modal-toggle]').forEach(btn => {
    btn.addEventListener('click', function () {
      const target = this.getAttribute('data-modal-target');
      const modal = document.getElementById(target);
      if (modal) modal.classList.remove('hidden');
    });
  });

  </script>

@endsection