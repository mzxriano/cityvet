@extends('layouts.layout')

@section('content')
  <h1 class="title-style mb-8 lg:mb-[50px] text-2xl lg:text-3xl dark:text-white">Dashboard</h1>

  <!-- Dashboard Stats Carousel -->
  @include('components.dashboard-carousel', [
    'weeklyBiteStats' => $weeklyBiteStats
  ])


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
            <select id="category-barangay" class="px-3 py-2 border-2 bg-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-full text-gray-600 text-sm>
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
    
    const ANIMAL_COLORS = {
      'dog': { bg: '#F59E0B', border: '#D97706', name: 'Dog' },
      'cat': { bg: '#8B5CF6', border: '#7C3AED', name: 'Cat' },
      'chicken': { bg: '#3B82F6', border: '#2563EB', name: 'Chicken' },
      'goat': { bg: '#10B981', border: '#059669', name: 'Goat' },
      'pig': { bg: '#EF4444', border: '#DC2626', name: 'Pig' },
      'cattle': { bg: '#14B8A6', border: '#0D9488', name: 'Cattle' },
      'carabao': { bg: '#F97316', border: '#EA580C', name: 'Carabao' },
      'horse': { bg: '#EC4899', border: '#DB2777', name: 'Horse' },
      'default': { bg: '#6B7280', border: '#4B5563', name: 'Other' }
    };

    // Helper function to get color for an animal type
    function getAnimalColor(type) {
      const normalizedType = type.toLowerCase();
      return ANIMAL_COLORS[normalizedType] || ANIMAL_COLORS['default'];
    }

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
          datasets: yearData.datasets // Return datasets object instead of data array
        };
      }
      return { labels: [], datasets: {} };
    } else {
      // Calculate total for all years for each animal type
      const allYears = Object.keys(vaccinationDataByYear);
      if (allYears.length === 0) {
        return {
          labels: vaccinatedBarangayData.labels,
          datasets: {} // Return empty datasets object
        };
      }
      
      const barangayNames = vaccinationDataByYear[allYears[0]].labels;
      const animalTypes = Object.keys(vaccinationDataByYear[allYears[0]].datasets);
      const totals = {};
      
      // Initialize totals for each animal type
      animalTypes.forEach(type => {
        totals[type] = new Array(barangayNames.length).fill(0);
      });
      
      // Sum up counts for all years
      allYears.forEach(y => {
        animalTypes.forEach(type => {
          vaccinationDataByYear[y].datasets[type].forEach((count, index) => {
            totals[type][index] += parseInt(count) || 0;
          });
        });
      });
      
      return {
        labels: barangayNames,
        datasets: totals // Return datasets object with totals
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

        const pieColors = currentCategoryData.labels.map(label => {
          return getAnimalColor(label).bg;
        });

        animalPieChart = new Chart(pieCtx, {
          type: 'pie',
          data: {
            labels: currentCategoryData.labels.map(label => {
              return getAnimalColor(label).name;
            }),
            datasets: [{
              label: 'Animal Categories',
              data: currentCategoryData.data,
              backgroundColor: pieColors,
              borderColor: currentCategoryData.labels.map(label => getAnimalColor(label).border),
              borderWidth: 2
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
                return context[0].label;
              },
              label: function(context) {
                return `Vaccinated Animals: ${context.parsed.x}`;
              }
            }
          }
        },
        elements: {
          bar: {
            barThickness: Math.max(12, Math.min(25, 400 / barangayCount))
          }
        }
      };
    };

    let vaccinatedBarangayChart = null;
    let selectedVaccinationYear = document.getElementById('vaccination-year') ? document.getElementById('vaccination-year').value : 'all';
    let currentVaccinationData = getVaccinationData(selectedVaccinationYear);

    try {
      if (currentVaccinationData && currentVaccinationData.labels && currentVaccinationData.datasets) {
        
        // Prepare datasets for Chart.js with stacked bars
        const chartDatasets = Object.keys(currentVaccinationData.datasets).map(type => {
          const color = getAnimalColor(type);
          return {
            label: color.name,
            data: currentVaccinationData.datasets[type],
            backgroundColor: color.bg,
            borderColor: color.border,
            borderWidth: 1
          };
        });
        
        vaccinatedBarangayChart = new Chart(barCtx, {
          type: 'bar',
          data: {
            labels: currentVaccinationData.labels,
            datasets: chartDatasets
          },
          options: {
            ...getHorizontalBarOptions(),
            indexAxis: 'y',
            scales: {
              x: {
                stacked: true, // Enable stacking
                beginAtZero: true,
                ticks: {
                  font: { size: window.innerWidth < 640 ? 14 : 16 },
                  stepSize: 1,
                  callback: function(value) {
                    if (Number.isInteger(value)) return value;
                  }
                }
              },
              y: {
                stacked: true, // Enable stacking
                ticks: {
                  font: { size: window.innerWidth < 640 ? 12 : 14 },
                  maxRotation: 0,
                  callback: function(value, index) {
                    const label = this.getLabelForValue(value);
                    return label.length > 15 ? label.substring(0, 15) + '...' : label;
                  }
                }
              }
            },
            plugins: {
              legend: {
                display: true,
                position: 'bottom',
                labels: {
                  font: { size: window.innerWidth < 640 ? 12 : 14 },
                  padding: 10,
                  usePointStyle: true
                }
              },
              tooltip: {
                titleFont: { size: window.innerWidth < 640 ? 14 : 16 },
                bodyFont: { size: window.innerWidth < 640 ? 12 : 14 },
                callbacks: {
                  title: function(context) {
                    return context[0].label; // Barangay name
                  },
                  label: function(context) {
                    const animalType = context.dataset.label;
                    const count = context.parsed.x;
                    return `${animalType}: ${count}`;
                  },
                  afterLabel: function(context) {
                    // Calculate total for this barangay
                    const dataIndex = context.dataIndex;
                    let total = 0;
                    context.chart.data.datasets.forEach(dataset => {
                      total += dataset.data[dataIndex] || 0;
                    });
                    return `Total: ${total}`;
                  }
                }
              }
            }
          }
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
        animalPieChart.data.labels = currentCategoryData.labels.map(label => {
          return getAnimalColor(label).name;
        });
        animalPieChart.data.datasets[0].data = currentCategoryData.data;
        animalPieChart.data.datasets[0].backgroundColor = currentCategoryData.labels.map(label => {
          return getAnimalColor(label).bg;
        });
        animalPieChart.data.datasets[0].borderColor = currentCategoryData.labels.map(label => {
          return getAnimalColor(label).border;
        });
        animalPieChart.update();
      }
    });

    // Handle vaccination year filter change
    document.getElementById('vaccination-year').addEventListener('change', function () {
      selectedVaccinationYear = this.value;
      currentVaccinationData = getVaccinationData(selectedVaccinationYear);
      
      if (vaccinatedBarangayChart && currentVaccinationData) {
        vaccinatedBarangayChart.data.labels = currentVaccinationData.labels;
        
        vaccinatedBarangayChart.data.datasets = Object.keys(currentVaccinationData.datasets).map(type => {
          const color = getAnimalColor(type);
          return {
            label: color.name,
            data: currentVaccinationData.datasets[type],
            backgroundColor: color.bg,
            borderColor: color.border,
            borderWidth: 1
          };
        });
        
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