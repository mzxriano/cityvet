@extends('layouts.layout')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8" x-data="animalTypeManager()">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumb -->
    <nav class="mb-6 flex items-center gap-2 text-sm">
      <a href="{{ route('admin.cms') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">CMS</a>
      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
      </svg>
      <span class="text-gray-900 dark:text-white font-medium">Animals</span>
    </nav>

    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Animal Types & Breeds</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Manage animal species and their breeds</p>
      </div>
      <button @click="showAddTypeModal = true" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium shadow-sm transition-colors duration-200 flex items-center gap-2">
        Add Animal Type
      </button>
    </div>

    <!-- Success/Error Messages -->
    <div x-show="successMessage" x-transition class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" x-cloak>
      <span x-text="successMessage"></span>
      <button @click="successMessage = ''" class="absolute top-0 right-0 px-4 py-3">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <div x-show="errorMessage" x-transition class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" x-cloak>
      <span x-text="errorMessage"></span>
      <button @click="errorMessage = ''" class="absolute top-0 right-0 px-4 py-3">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <!-- Animal Types Grid -->
    <div class="mb-8">
      <div class="flex items-center gap-4 mb-4">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Active Animal Types</h2>
        <button @click="showArchived = !showArchived" class="text-sm px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
          <span x-text="showArchived ? 'Hide Archived' : 'Show Archived'"></span>
        </button>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <template x-for="type in activeTypes" :key="type.id">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700" :class="{
              'bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20': type.category === 'pet',
              'bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20': type.category === 'livestock',
              'bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20': type.category === 'poultry'
            }">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                    <span x-text="type.display_name"></span>
                  </h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400" x-text="type.category.charAt(0).toUpperCase() + type.category.slice(1)"></p>
                </div>
                <div class="flex gap-2">
                  <!-- <button @click="confirmDeleteType(type.id, type.display_name)" class="text-red-500 hover:text-red-700 p-2 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Archive animal type">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button> -->
                </div>
              </div>
              <div class="mt-4 space-y-2">
                <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                  <span class="font-medium w-24">Name:</span>
                  <code class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded" x-text="type.name"></code>
                </div>
                <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                  <span class="font-medium w-24">Sort Order:</span>
                  <span class="text-gray-700 dark:text-gray-300" x-text="type.sort_order"></span>
                </div>
              </div>
            </div>
            <!-- Breeds Section -->
            <div class="p-6">
              <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-gray-900 dark:text-white">Breeds (<span x-text="type.breeds.length"></span>)</h4>
                <button @click="openAddBreedModal(type.id, type.display_name)" class="text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 text-sm font-medium flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                  </svg>
                  Add Breed
                </button>
              </div>
              <template x-if="type.breeds.length > 0">
                <div class="space-y-2 max-h-64 overflow-y-auto">
                  <template x-for="breed in type.breeds" :key="breed.id">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-green-300 dark:hover:border-green-700 transition-colors group">
                      <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white text-sm" x-text="breed.name"></p>
                        <template x-if="breed.description">
                          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="breed.description"></p>
                        </template>
                      </div>
                      <button @click="confirmDeleteBreed(breed.id, breed.name)" class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 p-1.5 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-all" title="Delete breed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </div>
                  </template>
                </div>
              </template>
              <template x-if="type.breeds.length === 0">
                <div class="text-center py-8 text-gray-500 dark:text-gray-400 text-sm">
                  <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                  </svg>
                  No breeds added yet
                </div>
              </template>
            </div>
          </div>
        </template>
      </div>
    </div>
    <div x-show="showArchived" class="mb-8">
      <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Archived Animal Types</h2>
      <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <template x-for="type in archivedTypes" :key="type.id">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden opacity-50">
            <!-- Card Header -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700" :class="{
              'bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20': type.category === 'pet',
              'bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20': type.category === 'livestock',
              'bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20': type.category === 'poultry'
            }">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                    <span x-text="type.display_name"></span>
                    <span class="ml-2 px-2 py-0.5 text-xs bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded">Archived</span>
                  </h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400" x-text="type.category.charAt(0).toUpperCase() + type.category.slice(1)"></p>
                </div>
                <div class="flex gap-2">
                  <button @click="restoreType(type.id, type.display_name)" class="text-green-600 hover:text-green-800 p-2 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors" title="Restore animal type">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10v6a1 1 0 001 1h6m10-11v6a1 1 0 01-1 1h-6m-7 7l7-7 7 7"></path>
                    </svg>
                  </button>
                </div>
              </div>
              <div class="mt-4 space-y-2">
                <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                  <span class="font-medium w-24">Name:</span>
                  <code class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded" x-text="type.name"></code>
                </div>
                <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                  <span class="font-medium w-24">Sort Order:</span>
                  <span class="text-gray-700 dark:text-gray-300" x-text="type.sort_order"></span>
                </div>
              </div>
            </div>
            <!-- Breeds Section -->
            <div class="p-6">
              <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-gray-900 dark:text-white">Breeds (<span x-text="type.breeds.length"></span>)</h4>
                <button @click="openAddBreedModal(type.id, type.display_name)" class="text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 text-sm font-medium flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                  </svg>
                  Add Breed
                </button>
              </div>
              <template x-if="type.breeds.length > 0">
                <div class="space-y-2 max-h-64 overflow-y-auto">
                  <template x-for="breed in type.breeds" :key="breed.id">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-green-300 dark:hover:border-green-700 transition-colors group">
                      <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white text-sm" x-text="breed.name"></p>
                        <template x-if="breed.description">
                          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="breed.description"></p>
                        </template>
                      </div>
                      <button @click="confirmDeleteBreed(breed.id, breed.name)" class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 p-1.5 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-all" title="Delete breed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </div>
                  </template>
                </div>
              </template>
              <template x-if="type.breeds.length === 0">
                <div class="text-center py-8 text-gray-500 dark:text-gray-400 text-sm">
                  <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                  </svg>
                  No breeds added yet
                </div>
              </template>
            </div>
          </div>
        </template>
      </div>
    </div>

    @if($animalTypes->count() === 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
      <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Animal Types Yet</h3>
      <p class="text-gray-600 dark:text-gray-400 mb-6">Get started by adding your first animal type</p>
      <button @click="showAddTypeModal = true" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium">
        Add Animal Type
      </button>
    </div>
    @endif

  </div>

  <!-- Add Animal Type Modal -->
  <div x-show="showAddTypeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showAddTypeModal = false">
    <div class="flex items-center justify-center min-h-screen px-4">
      <div class="fixed inset-0 bg-black opacity-50" @click="showAddTypeModal = false"></div>
      
      <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add New Animal Type</h3>
          <button @click="showAddTypeModal = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="addAnimalType" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Display Name *</label>
            <input type="text" x-model="newType.display_name" @input="newType.name = newType.display_name.toLowerCase().replace(/\s+/g, '_')" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., Dog, Cat, Pig">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type Name *</label>
            <input type="text" x-model="newType.name" readonly class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed" placeholder="Auto-generated">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category *</label>
            <select x-model="newType.category" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
              <option value="">Select Category</option>
              <option value="pet">Pet</option>
              <option value="livestock">Livestock</option>
              <option value="poultry">Poultry</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
            <textarea x-model="newType.description" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white" placeholder="Optional description"></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <button type="button" @click="showAddTypeModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
              Cancel
            </button>
            <button type="submit" :disabled="isSubmitting" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium disabled:opacity-50">
              <span x-show="!isSubmitting">Add Type</span>
              <span x-show="isSubmitting">Adding...</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Breed Modal -->
  <div x-show="showAddBreedModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showAddBreedModal = false">
    <div class="flex items-center justify-center min-h-screen px-4">
      <div class="fixed inset-0 bg-black opacity-50" @click="showAddBreedModal = false"></div>
      
      <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add Breed to <span x-text="selectedTypeName"></span></h3>
          <button @click="showAddBreedModal = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="addBreed" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Breed Name *</label>
            <input type="text" x-model="newBreed.name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., Labrador, Persian">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
            <textarea x-model="newBreed.description" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white" placeholder="Optional description"></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <button type="button" @click="showAddBreedModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
              Cancel
            </button>
            <button type="submit" :disabled="isSubmitting" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium disabled:opacity-50">
              <span x-show="!isSubmitting">Add Breed</span>
              <span x-show="isSubmitting">Adding...</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showDeleteModal = false">
    <div class="flex items-center justify-center min-h-screen px-4">
      <div class="fixed inset-0 bg-black opacity-50" @click="showDeleteModal = false"></div>
      
      <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-center gap-4 mb-4">
          <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Confirm Archiving</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1" x-text="deleteMessage"></p>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
          <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
            Cancel
          </button>
          <button @click="executeDelete" :disabled="isSubmitting" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium disabled:opacity-50">
            <span x-show="!isSubmitting">Archive</span>
            <span x-show="isSubmitting">Archiving...</span>
          </button>
        </div>
      </div>
    </div>
  </div>
zx
  <!-- Delete Breed Confirmation Modal -->
  <div x-show="showDeleteBreedModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showDeleteBreedModal = false">
    <div class="flex items-center justify-center min-h-screen px-4">
      <div class="fixed inset-0 bg-black opacity-50" @click="showDeleteBreedModal = false"></div>
      <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-center gap-4 mb-4">
          <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Confirm Deletion</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1" x-text="deleteBreedMessage"></p>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-4">
          <button @click="showDeleteBreedModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
            Cancel
          </button>
          <button @click="executeDeleteBreed" :disabled="isSubmitting" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium disabled:opacity-50">
            <span x-show="!isSubmitting">Delete</span>
            <span x-show="isSubmitting">Deleting...</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function animalTypeManager() {
  return {
    showAddTypeModal: false,
    showAddBreedModal: false,
    showDeleteModal: false,
    showDeleteBreedModal: false,
    isSubmitting: false,
    successMessage: '',
    errorMessage: '',
    selectedTypeId: null,
    selectedTypeName: '',
    deleteType: null, // 'type' or 'breed'
    deleteId: null,
    deleteMessage: '',
    deleteBreedMessage: '',
    showArchived: false,
    
    newType: {
      name: '',
      display_name: '',
      category: '',
      icon: 'pets',
      description: ''
    },
    
    newBreed: {
      animal_type_id: null,
      name: '',
      description: ''
    },

    openAddBreedModal(typeId, typeName) {
      this.selectedTypeId = typeId;
      this.selectedTypeName = typeName;
      this.newBreed = {
        animal_type_id: typeId,
        name: '',
        description: ''
      };
      this.showAddBreedModal = true;
    },

    async addAnimalType() {
      this.isSubmitting = true;
      this.errorMessage = '';

      try {
        const response = await fetch('{{ route("admin.cms.animals.types.store") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify(this.newType)
        });

        const data = await response.json();

        if (data.success) {
          this.successMessage = data.message;
          this.showAddTypeModal = false;
          this.newType = { name: '', display_name: '', category: '', icon: 'pets', description: '' };
          setTimeout(() => window.location.reload(), 1000);
        } else {
          this.errorMessage = data.message || 'Failed to add animal type';
        }
      } catch (error) {
        this.errorMessage = 'An error occurred while adding the animal type';
      } finally {
        this.isSubmitting = false;
      }
    },

    async addBreed() {
      this.isSubmitting = true;
      this.errorMessage = '';

      try {
        const response = await fetch('{{ route("admin.cms.animals.breeds.store") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify(this.newBreed)
        });

        const data = await response.json();

        if (data.success) {
          this.successMessage = data.message;
          this.showAddBreedModal = false;
          this.newBreed = { animal_type_id: null, name: '', description: '' };
          setTimeout(() => window.location.reload(), 1000);
        } else {
          this.errorMessage = data.message || 'Failed to add breed';
        }
      } catch (error) {
        this.errorMessage = 'An error occurred while adding the breed';
      } finally {
        this.isSubmitting = false;
      }
    },

    confirmDeleteType(id, name) {
      this.deleteType = 'type';
      this.deleteId = id;
      this.deleteMessage = `Are you sure you want to archive \"${name}\"? This will hide it from active types but can be restored.`;
      this.showDeleteModal = true;
    },

    confirmDeleteBreed(id, name) {
      this.deleteType = 'breed';
      this.deleteId = id;
      this.deleteBreedMessage = `Are you sure you want to delete "${name}" breed? This action cannot be undone.`;
      this.showDeleteBreedModal = true;
    },

    async executeDelete() {
      this.isSubmitting = true;
      this.errorMessage = '';

      if (this.deleteType === 'type') {
        // Always archive instead of delete
        try {
          const response = await fetch(`/admin/cms/animals/types/${this.deleteId}/archive`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
          });
          const data = await response.json();
          if (data.success) {
            this.successMessage = data.message;
            this.showDeleteModal = false;
            setTimeout(() => window.location.reload(), 1000);
          } else {
            this.errorMessage = data.message || 'Failed to archive animal type';
            this.showDeleteModal = false;
          }
        } catch (error) {
          this.errorMessage = 'An error occurred while archiving';
          this.showDeleteModal = false;
        } finally {
          this.isSubmitting = false;
        }
        return;
      }
      // ...existing code for breed deletion...
    },

    async executeDeleteBreed() {
      this.isSubmitting = true;
      this.errorMessage = '';
      try {
        const response = await fetch(`/admin/cms/animals/breeds/${this.deleteId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          }
        });
        const data = await response.json();
        if (data.success) {
          this.successMessage = data.message;
          this.showDeleteBreedModal = false;
          setTimeout(() => window.location.reload(), 1000);
        } else {
          this.errorMessage = data.message || 'Failed to delete breed';
          this.showDeleteBreedModal = false;
        }
      } catch (error) {
        this.errorMessage = 'An error occurred while deleting the breed';
        this.showDeleteBreedModal = false;
      } finally {
        this.isSubmitting = false;
      }
    },

    async restoreType(id, name) {
      this.isSubmitting = true;
      this.errorMessage = '';
      try {
        const response = await fetch(`/admin/cms/animals/types/${id}/restore`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          }
        });
        const data = await response.json();
        if (data.success) {
          this.successMessage = data.message;
          setTimeout(() => window.location.reload(), 1000);
        } else {
          this.errorMessage = data.message || 'Failed to restore animal type';
        }
      } catch (error) {
        this.errorMessage = 'An error occurred while restoring the animal type';
      } finally {
        this.isSubmitting = false;
      }
    },

    get activeTypes() {
      return this.$store.animalTypes.filter(t => t.is_active);
    },
    get archivedTypes() {
      return this.$store.animalTypes.filter(t => !t.is_active);
    },
    init() {
      // Convert PHP animalTypes to Alpine store for filtering
      this.$store.animalTypes = @json($animalTypes);
    },
  }
}
</script>
@endsection
