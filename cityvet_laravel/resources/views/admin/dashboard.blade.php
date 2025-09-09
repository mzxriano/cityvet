@extends('layouts.layout')

@section('content')
  <h1 class="title-style mb-8 lg:mb-[50px] text-2xl lg:text-3xl">Dashboard</h1>

  <section class="mb-6 lg:mb-[2rem]">
    <!-- Stats Cards - Stack on mobile, flex on desktop -->
    <div class="flex flex-col lg:flex-row justify-between gap-4 lg:gap-[5rem] mb-6 lg:mb-[2rem]">
      <div 
        class="bg-white flex flex-col flex-1 p-4 lg:p-[2rem] rounded-lg lg:rounded-[1rem] shadow-md cursor-pointer hover:shadow-lg transition-shadow" 
        data-modal-target="userRoleModal" 
        data-modal-toggle="userRoleModal"
      >
        <div class="mb-4 lg:mb-[2rem] text-gray-500 text-sm lg:text-base">
          Total Users
        </div>
        <div class="text-xl lg:text-[2rem] font-semibold text-[#0E0E0E]">
          {{ $totalUsers }}
        </div>
      </div>
      <div class="bg-white flex flex-col flex-1 p-4 lg:p-[2rem] rounded-lg lg:rounded-[1rem] shadow-md">
        <div class="mb-4 lg:mb-[2rem] text-gray-500 text-sm lg:text-base">
          Total Registered Animals
        </div>
        <div class="text-xl lg:text-[2rem] font-semibold text-[#0E0E0E]">
          {{ $totalAnimals }}
        </div>
      </div>
      <div class="bg-white flex flex-col flex-1 p-4 lg:p-[2rem] rounded-lg lg:rounded-[1rem] shadow-md">
        <div class="mb-4 lg:mb-[2rem] text-gray-500 text-sm lg:text-base">
          Total Vaccinated Animals
        </div>
        <div class="text-xl lg:text-[2rem] font-semibold text-[#0E0E0E]">
          {{ $totalVaccinatedAnimals }}
        </div>
      </div>
    </div>
  </section>

  <!-- Modal -->
  <div id="userRoleModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50">
    <div class="flex items-center justify-center min-h-screen px-4">
      <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">User Role Breakdown</h2>
        <ul class="space-y-2 text-sm text-gray-600">
            <li>Owner: <span class="font-semibold">{{ $userTypeCounts['owner'] ?? 0 }}</span></li>
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

  <!-- Case Report Section -->
  <section class="mb-6 lg:mb-[2rem]">
    <div class="bg-white shadow-md rounded-lg lg:rounded-[1rem] p-4 lg:p-[2rem]">
      <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 lg:gap-0"> 
        <div class="text-lg lg:text-[25px] text-gray-700 font-medium">
          Bite Case Report
        </div>

        <!-- Filter Section -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 lg:gap-4">
          <!-- Filter by Barangay -->
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <label class="text-sm text-gray-600 lg:hidden">Barangay:</label>
            <select id="barangay" class="w-full sm:w-auto px-3 py-2 border-2 bg-transparent rounded-full text-gray-600 text-sm lg:text-base">
              <option value="all">All</option>
              <option value="barangay-1">Barangay 1</option>
              <option value="barangay-2">Barangay 2</option>
            </select>
          </div>

          <!-- Filter by Date Range (Daily or Monthly) -->
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <label class="text-sm text-gray-600 lg:hidden">Period:</label>
            <select id="date-range" class="w-full sm:w-auto px-3 py-2 border-2 bg-transparent rounded-full text-gray-600 text-sm lg:text-base">
              <option value="daily">Daily</option>
              <option value="monthly">Monthly</option>
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
      <div class="flex flex-col w-full lg:w-2/5 bg-white rounded-xl shadow-md p-4 lg:p-[2rem]">
        <div class="text-lg lg:text-[20px] text-gray-700 mb-4 lg:mb-5 font-medium">
          Vaccinated Animal Per Barangay
        </div>
        <div class="space-y-3">
          @forelse($barangays as $barangay)
          <div class="flex justify-between items-center py-2">
            <div class="text-gray-500 text-sm lg:text-base">
              {{ $barangay->name }}
            </div>
            <div class="text-gray-500 text-sm lg:text-base font-medium">
              {{ $barangay->vaccinated_animals_count ?? 0 }}
            </div>
          </div>
          <hr class="border-gray-200">
          @empty
            <div class="text-center py-8">
              <p class="text-gray-500">No barangay found.</p>
            </div>
          @endforelse
        </div>
      </div>
      
      <!-- Animal per category -->
      <div class="flex flex-col flex-1 bg-white rounded-xl shadow-md p-4 lg:p-[2rem]">
        <div class="text-lg lg:text-[20px] text-gray-700 mb-4 font-medium">
          Animal per Category
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

      // Placeholder data
      const data = {
        daily: {
          labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'],
          dogBite: [5, 8, 7, 3, 6, 4, 5],
          catBite: [4, 2, 6, 7, 5, 3, 4]
        },
        monthly: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
          dogBite: [20, 15, 25, 30, 22],
          catBite: [18, 12, 20, 25, 15]
        },
      };

      let selectedData = data.daily;

      // Animal per Category Pie chart data
      const animalCategories = {
          labels: {!! json_encode($animalsPerCategory->pluck('type')) !!},
          data: {!! json_encode($animalsPerCategory->pluck('total')) !!}
        };

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
                  size: isMobile ? 10 : 12
                }
              }
            },
            x: {
              ticks: {
                font: {
                  size: isMobile ? 10 : 12
                },
                maxRotation: isMobile ? 45 : 0
              }
            }
          },
          plugins: {
            legend: {
              labels: {
                font: {
                  size: isMobile ? 10 : 12
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
      const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: selectedData.labels,
          datasets: [{
            label: 'Dog Bite',
            data: selectedData.dogBite,
            backgroundColor: '#D92A2A',
          }, {
            label: 'Cat Bite',
            data: selectedData.catBite,
            backgroundColor: '#FF8800',
          }]
        },
        options: getResponsiveOptions()
      });

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
                  size: isMobile ? 10 : 12
                },
                padding: isMobile ? 10 : 20,
                usePointStyle: true
              }
            }
          }
        };
      };

      // Create the pie chart for Animal Categories
      const animalPieChart = new Chart(pieCtx, {
        type: 'pie',
        data: {
          labels: animalCategories.labels,
          datasets: [{
            label: 'Animal Categories',
            data: animalCategories.data,
            backgroundColor: ['#D92A2A', '#FF8800', '#00A859', '#F2C35C', '#FFEB3B', '#03A9F4'],
          }]
        },
        options: getPieChartOptions()
      });

      // Update charts on window resize
      let resizeTimeout;
      window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
          myChart.options = getResponsiveOptions();
          animalPieChart.options = getPieChartOptions();
          myChart.update();
          animalPieChart.update();
        }, 250);
      });

      // Handle the filter changes
      document.getElementById('date-range').addEventListener('change', function () {
        const selectedDateRange = this.value;
        selectedData = selectedDateRange === 'daily' ? data.daily : data.monthly;

        myChart.data.labels = selectedData.labels;
        myChart.data.datasets[0].data = selectedData.dogBite;
        myChart.data.datasets[1].data = selectedData.catBite;
        myChart.update();
      });

      document.getElementById('barangay').addEventListener('change', function () {
        const selectedBarangay = this.value;
        console.log('Selected Barangay:', selectedBarangay);
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