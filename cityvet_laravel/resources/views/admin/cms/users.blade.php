@extends('layouts.layout')

@section('content')
<style>
  /* Custom slider styles */
  input[type="range"] {
    -webkit-appearance: none;
    appearance: none;
    background: transparent;
    cursor: pointer;
    width: 100%;
  }

  /* Slider track */
  input[type="range"]::-webkit-slider-runnable-track {
    background: linear-gradient(to right, 
      #3b82f6 0%, 
      #3b82f6 var(--value-percent), 
      #e5e7eb var(--value-percent), 
      #e5e7eb 100%);
    height: 0.75rem;
    border-radius: 0.5rem;
  }

  input[type="range"]::-moz-range-track {
    background: #e5e7eb;
    height: 0.75rem;
    border-radius: 0.5rem;
  }

  input[type="range"]::-moz-range-progress {
    background-color: #3b82f6;
    height: 0.75rem;
    border-radius: 0.5rem;
  }

  /* Dark mode track */
  .dark input[type="range"]::-webkit-slider-runnable-track {
    background: linear-gradient(to right, 
      #3b82f6 0%, 
      #3b82f6 var(--value-percent), 
      #374151 var(--value-percent), 
      #374151 100%);
  }

  .dark input[type="range"]::-moz-range-track {
    background: #374151;
  }

  /* Slider thumb */
  input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    margin-top: -0.375rem;
    background-color: #ffffff;
    border: 2px solid #3b82f6;
    height: 1.5rem;
    width: 1.5rem;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  input[type="range"]::-moz-range-thumb {
    border: none;
    background-color: #ffffff;
    border: 2px solid #3b82f6;
    height: 1.5rem;
    width: 1.5rem;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  /* Hover effects */
  input[type="range"]::-webkit-slider-thumb:hover {
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
  }

  input[type="range"]::-moz-range-thumb:hover {
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
  }

  /* Focus effects */
  input[type="range"]:focus {
    outline: none;
  }

  input[type="range"]:focus::-webkit-slider-thumb {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
  }

  input[type="range"]:focus::-moz-range-thumb {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
  }
</style>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumb -->
    <nav class="mb-6 flex items-center gap-2 text-sm">
      <a href="{{ route('admin.cms') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">CMS</a>
      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
      </svg>
      <span class="text-gray-900 dark:text-white font-medium">User Configuration</span>
    </nav>

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">User Inactivity Tracking</h1>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Configure the threshold for tracking inactive users</p>
    </div>

    <div x-data="userConfigManager()">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Current Threshold Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Current Threshold</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">
            <span x-text="threshold"></span>
            <span x-text="threshold === 1 ? 'Month' : 'Months'" class="text-lg text-gray-600 dark:text-gray-400"></span>
          </p>
        </div>

        <!-- Tracking Status Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Tracking Status</h3>
          </div>
          <p class="text-2xl font-bold text-green-600 dark:text-green-400">Active</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Auto-tracking enabled</p>
        </div>

        <!-- Range Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
              </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Allowed Range</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">1-24</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Months</p>
        </div>
      </div>

      <!-- Configuration Panel -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Inactivity Threshold</h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Set the number of months after which a user is considered inactive based on their last login
          </p>
        </div>

        <div class="p-8">
          <!-- Large Threshold Display -->
          <div class="text-center mb-8">
            <div class="inline-flex items-baseline gap-2">
              <span x-text="threshold" class="text-6xl font-bold text-blue-600 dark:text-blue-400"></span>
              <span x-text="threshold === 1 ? 'month' : 'months'" class="text-2xl text-gray-600 dark:text-gray-400"></span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Users inactive for this period will be marked as inactive</p>
          </div>

          <!-- Slider -->
          <div class="mb-8">
            <input 
              type="range" 
              x-model.number="threshold"
              min="1" 
              max="24" 
              step="1"
              class="w-full"
              x-on:input="updateSliderProgress($event.target)"
            >
            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-2">
              <span>1 month</span>
              <span>6 months</span>
              <span>12 months</span>
              <span>18 months</span>
              <span>24 months</span>
            </div>
          </div>

          <!-- Quick Select Buttons -->
          <div class="mb-8">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Quick Select</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <button 
                type="button"
                x-on:click="threshold = 3"
                :class="threshold === 3 ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-500'"
                class="px-4 py-2 rounded-lg border-2 font-medium transition-colors"
              >
                3 Months
              </button>
              <button 
                type="button"
                x-on:click="threshold = 6"
                :class="threshold === 6 ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-500'"
                class="px-4 py-2 rounded-lg border-2 font-medium transition-colors"
              >
                6 Months
              </button>
              <button 
                type="button"
                x-on:click="threshold = 12"
                :class="threshold === 12 ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-500'"
                class="px-4 py-2 rounded-lg border-2 font-medium transition-colors"
              >
                12 Months
              </button>
              <button 
                type="button"
                x-on:click="threshold = 24"
                :class="threshold === 24 ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-500'"
                class="px-4 py-2 rounded-lg border-2 font-medium transition-colors"
              >
                24 Months
              </button>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-4">
            <button 
              type="button"
              x-on:click="saveThreshold"
              :disabled="saving"
              class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
            >
              <svg x-show="!saving" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <svg x-show="saving" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span x-text="saving ? 'Saving...' : 'Save Changes'"></span>
            </button>
            
            <button 
              type="button"
              x-on:click="resetThreshold"
              :disabled="saving"
              class="px-6 py-3 rounded-lg border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500 font-medium transition-colors disabled:opacity-50"
            >
              Reset
            </button>
          </div>

          <!-- Success Message -->
          <div 
            x-show="showSuccess" 
            x-transition
            class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg flex items-center gap-3"
          >
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm text-green-800 dark:text-green-300">Threshold updated successfully!</p>
          </div>

          <!-- Error Message -->
          <div 
            x-show="errorMessage" 
            x-transition
            class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg flex items-center gap-3"
          >
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm text-red-800 dark:text-red-300" x-text="errorMessage"></p>
          </div>
        </div>
      </div>

      <!-- Information Box -->
      <div class="mt-8 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
        <div class="flex gap-4">
          <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-2">How It Works</h3>
            <ul class="space-y-2 text-sm text-blue-800 dark:text-blue-400">
              <li class="flex items-start gap-2">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                The system automatically tracks when users log in to the application
              </li>
              <li class="flex items-start gap-2">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                Users who haven't logged in for the specified period will be marked as inactive
              </li>
              <li class="flex items-start gap-2">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                Inactive users will still have access to their accounts when they log in
              </li>
              <li class="flex items-start gap-2">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                This threshold helps identify users who may need re-engagement or account cleanup
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function userConfigManager() {
  return {
    threshold: {{ $inactivityThreshold }},
    originalThreshold: {{ $inactivityThreshold }},
    saving: false,
    showSuccess: false,
    errorMessage: '',

    init() {
      // Initialize slider progress
      this.$nextTick(() => {
        const slider = this.$el.querySelector('input[type="range"]');
        if (slider) {
          this.updateSliderProgress(slider);
        }
      });
    },

    updateSliderProgress(slider) {
      const value = slider.value;
      const min = slider.min || 1;
      const max = slider.max || 24;
      const percentage = ((value - min) / (max - min)) * 100;
      slider.style.setProperty('--value-percent', percentage + '%');
    },

    resetThreshold() {
      this.threshold = this.originalThreshold;
      this.showSuccess = false;
      this.errorMessage = '';
      
      // Update slider progress
      const slider = this.$el.querySelector('input[type="range"]');
      if (slider) {
        this.updateSliderProgress(slider);
      }
    },

    async saveThreshold() {
      this.saving = true;
      this.showSuccess = false;
      this.errorMessage = '';

      try {
        const response = await fetch('{{ route('admin.cms.users.threshold.update') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            months: this.threshold
          })
        });

        const data = await response.json();

        if (data.success) {
          this.originalThreshold = this.threshold;
          this.showSuccess = true;
          
          setTimeout(() => {
            this.showSuccess = false;
          }, 3000);
        } else {
          this.errorMessage = data.message || 'Failed to update threshold';
        }
      } catch (error) {
        console.error('Error:', error);
        this.errorMessage = 'An error occurred while saving. Please try again.';
      } finally {
        this.saving = false;
      }
    }
  }
}
</script>
@endsection
