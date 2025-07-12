@extends('layouts.layout')

@section('content')
  <h1 class="title-style mb-[50px]">Dashboard</h1>

  <section class="mb-[2rem]">
    <div class="flex justify-between gap-[5rem] mb-[2rem]">
      <div class="bg-white flex flex-col flex-1 p-[2rem] rounded-[1rem] shadow-md">
        <div class="mb-[2rem] text-[#858585]">
          Total Users
        </div>
        <div class="text-[2rem]">
          {{ $totalUsers }}
        </div>
      </div>
      <div class="bg-white flex flex-col flex-1 p-[2rem] rounded-[1rem] shadow-md">
        <div class="mb-[2rem] text-[#858585]">
          Total Registered Animals
        </div>
        <div class="text-[2rem]">
          {{ $totalAnimals }}
        </div>
      </div>
      <div class="bg-white flex flex-col flex-1 p-[2rem] rounded-[1rem] shadow-md">
        <div class="mb-[2rem] text-[#858585]">
          Total Vaccinated Animals
        </div>
        <div class="text-[2rem]">
          0
        </div>
      </div>
    </div>
  </section>

  <!-- Case Report Section -->
  <section class="mb-[2rem]">
    <div class="bg-white shadow-md rounded-[1rem] p-[2rem]">
      <div class="flex justify-between items-center"> 
        <div class="text-[25px] text-[#524F4F]">
          Bite Case Report
        </div>

        <!-- Filter Section -->
        <div class="flex items-center gap-4">
          <!-- Filter by Barangay -->
          <div class="flex items-center gap-2">
            <select id="barangay" class="px-2 py-1 border-2 bg-transparent rounded-full text-[#4D4D4D]">
              <option value="all">All</option>
              <option value="barangay-1">Barangay 1</option>
              <option value="barangay-2">Barangay 2</option>
            </select>
          </div>

          <!-- Filter by Date Range (Daily or Monthly) -->
          <div class="flex items-center gap-2">
            <select id="date-range" class="px-2 py-1 border-2 bg-transparent rounded-full text-[#4D4D4D]">
              <option value="daily">Daily</option>
              <option value="monthly">Monthly</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Graph Section -->
      <div class="mt-[2rem]">
        <div class="bg-gray-100 h-[300px] rounded-lg">
          <!-- Canvas where the graph will be rendered -->
          <canvas id="myChart"></canvas>
        </div>
      </div>
    </div>
  </section>


  <section>
    <div class="flex gap-[2rem]">
      <!-- Vaccinated per barangay -->
      <div class="flex flex-col w-2/5 bg-white rounded-xl shadow-md p-[2rem]">
        <div class="text-[20px] text-[#524F4F] mb-5">
          Vaccinated Animal Per Barangay
        </div>
        <div class="flex justify-between">
          <div class="text-[#858585]">
            Anonas
          </div>
          <div class="text-[#858585]">
            10
          </div>
        </div>
        <hr>
        <div class="flex justify-between">
          <div class="text-[#858585]">
            Camantiles
          </div>
          <div class="text-[#858585]">
            3
          </div>
        </div>
        <hr>
      </div>
      <!-- Animal per category -->
      <div class="flex flex-col flex-1 bg-white rounded-xl shadow-md p-[2rem]">
        <div class="text-[20px] text-[#524F4F]">
          Animal per Category
        </div>
        <!-- Pie chart -->
        <div>
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
          labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'],  // Last 7 days
          dogBite: [5, 8, 7, 3, 6, 4, 5],  // Number of dog bite cases per day
          catBite: [4, 2, 6, 7, 5, 3, 4]   // Number of cat bite cases per day
        },
        monthly: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],  // Last 5 months
          dogBite: [20, 15, 25, 30, 22],  // Dog bite cases per month
          catBite: [18, 12, 20, 25, 15]  // Cat bite cases per month
        },
        // Animal per Category Pie chart data
        animalCategories: {
          labels: ['Dog', 'Cat', 'Cow', 'Goat', 'Chicken', 'Duck'],
          data: [50, 40, 30, 20, 3, 0]  // Number of animals per category
        }
      };

      let selectedData = data.daily;  // Default to daily data

      // Create the bar chart
      const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: selectedData.labels,
          datasets: [{
            label: 'Dog Bite',
            data: selectedData.dogBite,
            backgroundColor: '#D92A2A',  // Red for Dog Bite
          }, {
            label: 'Cat Bite',
            data: selectedData.catBite,
            backgroundColor: '#FF8800',  // Orange for Cat Bite
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          },
          barThickness: 18,  // Reduce bar thickness
          categoryPercentage: 0.8,  // Make the category width smaller
          barPercentage: 0.5,
        }
      });

      // Create the pie chart for Animal Categories
      const animalPieChart = new Chart(pieCtx, {
        type: 'pie',
        data: {
          labels: data.animalCategories.labels,
          datasets: [{
            label: 'Animal Categories',
            data: data.animalCategories.data,
            backgroundColor: ['#D92A2A', '#FF8800', '#00A859', '#F2C35C', '#FFEB3B', '#03A9F4'],  // Colors for each category
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
        }
      });

      // Handle the filter changes (Barangay & Date Range)
      document.getElementById('date-range').addEventListener('change', function () {
        const selectedDateRange = this.value;
        
        // Switch between daily and monthly data
        selectedData = selectedDateRange === 'daily' ? data.daily : data.monthly;

        // Update chart labels and data dynamically
        myChart.data.labels = selectedData.labels;
        myChart.data.datasets[0].data = selectedData.dogBite;
        myChart.data.datasets[1].data = selectedData.catBite;

        // Re-render the chart
        myChart.update();
      });

      document.getElementById('barangay').addEventListener('change', function () {
        const selectedBarangay = this.value;
        
        console.log('Selected Barangay:', selectedBarangay);
      });
    });
  </script>

@endsection
