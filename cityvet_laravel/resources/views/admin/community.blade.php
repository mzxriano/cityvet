@extends('layouts.layout')

@section('content')
<div class="min-h-screen">
  <main class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Community Posts</h1>
        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm transition-colors duration-200 flex items-center space-x-2" onclick="refreshPosts()">
          <i class="fas fa-sync-alt"></i>
          <span>Refresh</span>
        </button>
      </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-6">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" id="communityTabs" role="tablist">
          <button class="tab-button active py-2 px-1 border-b-2 border-amber-500 font-medium text-sm text-amber-600" 
                  id="pending-tab" 
                  data-bs-toggle="tab" 
                  data-bs-target="#pending" 
                  type="button" 
                  role="tab" 
                  aria-controls="pending" 
                  aria-selected="true">
            Pending Reviews
            <span class="ml-2 bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded-full font-medium" id="pending-count">0</span>
          </button>
          <button class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                  id="approved-tab" 
                  data-bs-toggle="tab" 
                  data-bs-target="#approved" 
                  type="button" 
                  role="tab" 
                  aria-controls="approved" 
                  aria-selected="false">
            Approved Posts
            <span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium" id="approved-count">0</span>
          </button>
        </nav>
      </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="communityTabsContent">
      <!-- Pending Posts Tab -->
      <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
        <div id="pending-posts-container">
          <div class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
        </div>
      </div>
      
      <!-- Approved Posts Tab -->
      <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
        <div id="approved-posts-container">
          <div class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<style>
.tab-button.active {
  color: #d97706;
  border-color: #d97706;
}

.tab-button.active[aria-controls="approved"] {
  color: #059669;
  border-color: #059669;
}

.tab-pane {
  display: none;
}

.tab-pane.show.active {
  display: block;
}
</style>

<script>
let pendingPosts = [];
let approvedPosts = [];

async function loadPendingPosts() {
  try {
    const response = await fetch('/admin/community/pending-posts', {
      headers: { 'Accept': 'application/json' }
    });
    if (response.ok) {
      pendingPosts = await response.json();
      displayPendingPosts();
    } else {
      document.getElementById('pending-posts-container').innerHTML = `
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Error</h3>
              <div class="mt-2 text-sm text-red-700">Failed to load pending posts</div>
            </div>
          </div>
        </div>
      `;
    }
  } catch (error) {
    document.getElementById('pending-posts-container').innerHTML = `
      <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-400"></i>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error</h3>
            <div class="mt-2 text-sm text-red-700">Error loading pending posts</div>
          </div>
        </div>
      </div>
    `;
  }
}

async function loadApprovedPosts() {
  try {
    const response = await fetch('/admin/community/approved-posts', {
      headers: { 'Accept': 'application/json' }
    });
    if (response.ok) {
      approvedPosts = await response.json();
      displayApprovedPosts();
    } else {
      document.getElementById('approved-posts-container').innerHTML = `
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Error</h3>
              <div class="mt-2 text-sm text-red-700">Failed to load approved posts</div>
            </div>
          </div>
        </div>
      `;
    }
  } catch (error) {
    document.getElementById('approved-posts-container').innerHTML = `
      <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-400"></i>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error</h3>
            <div class="mt-2 text-sm text-red-700">Error loading approved posts</div>
          </div>
        </div>
      </div>
    `;
  }
}

function displayPendingPosts() {
  const container = document.getElementById('pending-posts-container');
  document.getElementById('pending-count').textContent = pendingPosts.length;
  
  if (pendingPosts.length === 0) {
    container.innerHTML = `
      <div class="text-center py-12">
        <div class="mx-auto h-16 w-16 text-green-400 mb-4">
          <svg fill="currentColor" viewBox="0 0 20 20" class="w-full h-full">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">All caught up!</h3>
        <p class="text-gray-500">No pending posts need review at this time.</p>
      </div>
    `;
    return;
  }
  
  container.innerHTML = `
    <div class="space-y-4">
      ${pendingPosts.map(postCardWithReviewButtons).join('')}
    </div>
  `;
}

function displayApprovedPosts() {
  const container = document.getElementById('approved-posts-container');
  document.getElementById('approved-count').textContent = approvedPosts.length;
  
  if (approvedPosts.length === 0) {
    container.innerHTML = `
      <div class="text-center py-12">
        <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
          <svg fill="currentColor" viewBox="0 0 20 20" class="w-full h-full">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No approved posts yet</h3>
        <p class="text-gray-500">Approved community posts will appear here.</p>
      </div>
    `;
    return;
  }
  
  container.innerHTML = `
    <div class="space-y-4">
      ${approvedPosts.map(postCardApproved).join('')}
    </div>
  `;
}

function postCardWithReviewButtons(post) {
  const userName = post.user ? `${post.user.first_name} ${post.user.last_name}` : 'Unknown User';
  const postDate = new Date(post.created_at).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
  
  const imagesHtml = post.images && post.images.length > 0 ? `
    <div class="mt-4">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        ${post.images.map(image => `
          <div class="relative group">
            <img src="${image.image_url}" 
                 alt="Post image" 
                 class="w-full h-24 object-cover rounded-lg border border-gray-200 group-hover:opacity-90 transition-opacity">
          </div>
        `).join('')}
      </div>
    </div>
  ` : '';

  return `
    <div class="bg-white rounded-lg shadow-lg border border-amber-200 hover:shadow-xl transition-shadow duration-200">
      <!-- Header -->
      <div class="bg-amber-50 px-6 py-4 border-b border-amber-100 rounded-t-lg">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
              <i class="fas fa-user text-amber-600"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">${userName}</h3>
              <div class="flex items-center space-x-2 text-sm text-gray-500">
                <i class="fas fa-clock"></i>
                <span>${postDate}</span>
                <span class="bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded-full font-medium">Pending Review</span>
              </div>
            </div>
          </div>
          <div class="flex space-x-2">
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-sm transition-colors duration-200 flex items-center space-x-2" 
                    onclick="reviewPost(${post.id}, 'approved')">
              <i class="fas fa-check"></i>
              <span>Approve</span>
            </button>
            <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow-sm transition-colors duration-200 flex items-center space-x-2" 
                    onclick="reviewPost(${post.id}, 'rejected')">
              <i class="fas fa-times"></i>
              <span>Reject</span>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Content -->
      <div class="p-6">
        <div class="text-gray-700 leading-relaxed">${post.content}</div>
        ${imagesHtml}
      </div>
    </div>
  `;
}

function postCardApproved(post) {
  const userName = post.user ? `${post.user.first_name} ${post.user.last_name}` : 'Unknown User';
  const postDate = new Date(post.created_at).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
  
  const imagesHtml = post.images && post.images.length > 0 ? `
    <div class="mt-4">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        ${post.images.map(image => `
          <div class="relative group">
            <img src="${image.image_url}" 
                 alt="Post image" 
                 class="w-full h-24 object-cover rounded-lg border border-gray-200 group-hover:opacity-90 transition-opacity">
          </div>
        `).join('')}
      </div>
    </div>
  ` : '';

  return `
    <div class="bg-white rounded-lg shadow-lg border border-green-200 hover:shadow-xl transition-shadow duration-200">
      <!-- Header -->
      <div class="bg-green-50 px-6 py-4 border-b border-green-100 rounded-t-lg">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
              <i class="fas fa-user text-green-600"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">${userName}</h3>
              <div class="flex items-center space-x-2 text-sm text-gray-500">
                <i class="fas fa-clock"></i>
                <span>${postDate}</span>
                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">Approved</span>
              </div>
            </div>
          </div>
          <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
            <i class="fas fa-check text-green-600"></i>
          </div>
        </div>
      </div>
      
      <!-- Content -->
      <div class="p-6">
        <div class="text-gray-700 leading-relaxed">${post.content}</div>
        ${imagesHtml}
      </div>
    </div>
  `;
}

async function reviewPost(postId, status) {
  try {
    const response = await fetch(`/admin/community/${postId}/review`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': window.csrfToken
      },
      body: JSON.stringify({ status: status })
    });
    
    if (response.ok) {
      // Remove the post from pending list
      pendingPosts = pendingPosts.filter(post => post.id !== postId);
      displayPendingPosts();
      // Reload approved posts
      loadApprovedPosts();
      // Show success message
      const message = status === 'approved' ? 'Post approved successfully!' : 'Post rejected successfully!';
      showAlert('success', message);
    } else {
      const error = await response.json();
      showAlert('error', error.message || 'Failed to review post');
    }
  } catch (error) {
    showAlert('error', 'An error occurred while reviewing the post');
  }
}

function refreshPosts() {
  // Show loading state
  document.getElementById('pending-posts-container').innerHTML = `
    <div class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>
  `;
  document.getElementById('approved-posts-container').innerHTML = `
    <div class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>
  `;
  
  loadPendingPosts();
  loadApprovedPosts();
}

function showAlert(type, message) {
  const alertColors = {
    success: 'bg-green-50 border-green-200 text-green-800',
    error: 'bg-red-50 border-red-200 text-red-800',
    warning: 'bg-yellow-50 border-yellow-200 text-yellow-800'
  };
  
  const iconMap = {
    success: 'fas fa-check-circle text-green-400',
    error: 'fas fa-exclamation-circle text-red-400',
    warning: 'fas fa-exclamation-triangle text-yellow-400'
  };
  
  const alertDiv = document.createElement('div');
  alertDiv.className = `fixed top-4 right-4 max-w-sm w-full ${alertColors[type]} border rounded-lg p-4 shadow-lg z-50 transform transition-all duration-300 translate-x-full`;
  alertDiv.innerHTML = `
    <div class="flex">
      <div class="flex-shrink-0">
        <i class="${iconMap[type]}"></i>
      </div>
      <div class="ml-3 flex-1">
        <p class="text-sm font-medium">${message}</p>
      </div>
      <div class="ml-4 flex-shrink-0">
        <button class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none" onclick="this.parentElement.parentElement.parentElement.remove()">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  `;
  
  document.body.appendChild(alertDiv);
  
  // Animate in
  setTimeout(() => {
    alertDiv.classList.remove('translate-x-full');
  }, 100);
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    alertDiv.classList.add('translate-x-full');
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.remove();
      }
    }, 300);
  }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
  loadPendingPosts();
  loadApprovedPosts();
  
  // Tab functionality
  document.querySelectorAll('#communityTabs button').forEach(tab => {
    tab.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Remove active class from all tabs and content
      document.querySelectorAll('#communityTabs button').forEach(t => {
        t.classList.remove('active');
        t.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        t.classList.remove('text-amber-600', 'border-amber-500', 'text-green-600', 'border-green-500');
        t.setAttribute('aria-selected', 'false');
      });
      
      document.querySelectorAll('.tab-pane').forEach(content => {
        content.classList.remove('show', 'active');
      });
      
      // Add active class to clicked tab
      this.classList.add('active');
      this.classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
      this.setAttribute('aria-selected', 'true');
      
      // Color based on tab type
      if (this.getAttribute('aria-controls') === 'approved') {
        this.classList.add('text-green-600', 'border-green-500');
      } else {
        this.classList.add('text-amber-600', 'border-amber-500');
      }
      
      // Show corresponding content
      const targetId = this.getAttribute('data-bs-target');
      const targetContent = document.querySelector(targetId);
      if (targetContent) {
        targetContent.classList.add('show', 'active');
      }
    });
  });
});
</script>
@endsection