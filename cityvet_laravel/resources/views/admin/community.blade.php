@extends('layouts.layout')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
  <main class="max-w-7xl mx-auto p-4">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Community Management</h1>
        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm transition-colors duration-200 flex items-center space-x-2" onclick="refreshAllData()">
          <i class="fas fa-sync-alt"></i>
          <span>Refresh</span>
        </button>
      </div>
    </div>

    <div class="flex gap-6">
      <!-- Main Content -->
      <div class="flex-1">
        <!-- Tabs Navigation -->
        <div class="mb-6">
          <div class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-t-lg">
            <nav class="-mb-px flex space-x-8 px-6" id="communityTabs" role="tablist">
              <button class="tab-button active py-4 px-1 border-b-2 border-amber-500 font-medium text-sm text-amber-600 dark:text-amber-400" 
                      id="pending-tab" 
                      data-target="pending">
                Pending Reviews
                <span class="ml-2 bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200 text-xs px-2 py-1 rounded-full font-medium" id="pending-count">0</span>
              </button>
              <button class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" 
                      id="approved-tab" 
                      data-target="approved">
                All Posts
                <span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium" id="approved-count">0</span>
              </button>
            </nav>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
          <!-- Pending Posts Tab -->
          <div class="tab-pane active" id="pending">
            <div id="pending-posts-container" class="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
              <div class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              </div>
            </div>
          </div>
          
          <!-- Approved Posts Tab -->
          <div class="tab-pane" id="approved">
            <div id="approved-posts-container" class="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
              <div class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar for Reported Content -->
      <div class="w-80">
        <div class="bg-white rounded-lg shadow-sm border border-color">
          <div class="p-4 border-b border-color">
            <h2 class="text-lg font-semibold text-primary flex items-center">
              <i class="fas fa-flag text-red-500 mr-2"></i>
              Reported Content
            </h2>
          </div>
          
          <!-- Reported Posts Section -->
          <div class="p-4 border-b border-color">
            <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center justify-between">
              Reported Posts
              <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full font-medium" id="reported-posts-count">0</span>
            </h3>
            <div id="reported-posts-container" class="space-y-2 max-h-64 overflow-y-auto">
              <div class="text-center py-4 text-sm text-gray-500">Loading...</div>
            </div>
          </div>

          <!-- Reported Comments Section -->
          <div class="p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center justify-between">
              Reported Comments
              <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full font-medium" id="reported-comments-count">0</span>
            </h3>
            <div id="reported-comments-container" class="space-y-2 max-h-64 overflow-y-auto">
              <div class="text-center py-4 text-sm text-gray-500">Loading...</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Comments Modal -->
<div id="commentsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4" style="display: none;">
  <div class="bg-white rounded-lg max-w-2xl w-full max-h-[80vh] overflow-hidden">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
      <h3 class="text-lg font-semibold text-secondary">Comments</h3>
      <button onclick="closeCommentsModal()" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="p-4 max-h-96 overflow-y-auto" id="commentsModalContent">
      <!-- Comments will be loaded here -->
    </div>
  </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 z-50 items-center justify-center p-4" style="display: none;">
  <div class="relative max-w-4xl max-h-full">
    <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 text-2xl z-10">
      <i class="fas fa-times"></i>
    </button>
    <img id="modalImage" src="" alt="Full size image" class="max-w-full max-h-full object-contain rounded-lg">
  </div>
</div>

<style>
.tab-button.active {
  color: #d97706;
  border-color: #d97706;
}

.tab-button.active[data-target="approved"] {
  color: #059669;
  border-color: #059669;
}

.tab-pane {
  display: none;
}

.tab-pane.active {
  display: block;
}

.post-card {
  transition: all 0.2s ease;
}

.post-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.like-btn:hover {
  background-color: #fee2e2;
  color: #dc2626;
}

.like-btn.liked {
  background-color: #dc2626;
  color: white;
}

.comment-btn:hover {
  background-color: #dbeafe;
  color: #2563eb;
}

/* Custom scrollbar styles */
#pending-posts-container::-webkit-scrollbar,
#approved-posts-container::-webkit-scrollbar {
  width: 6px;
}

#pending-posts-container::-webkit-scrollbar-track,
#approved-posts-container::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

#pending-posts-container::-webkit-scrollbar-thumb,
#approved-posts-container::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

#pending-posts-container::-webkit-scrollbar-thumb:hover,
#approved-posts-container::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Dark mode scrollbar */
.dark #pending-posts-container::-webkit-scrollbar-track,
.dark #approved-posts-container::-webkit-scrollbar-track {
  background: #374151;
}

.dark #pending-posts-container::-webkit-scrollbar-thumb,
.dark #approved-posts-container::-webkit-scrollbar-thumb {
  background: #6b7280;
}

.dark #pending-posts-container::-webkit-scrollbar-thumb:hover,
.dark #approved-posts-container::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>

<script>
let pendingPosts = [];
let approvedPosts = [];
let reportedPosts = [];
let reportedComments = [];

// Load all data functions
async function loadPendingPosts() {
  try {
    const response = await fetch('/admin/community/pending-posts', {
      headers: { 'Accept': 'application/json' }
    });
    if (response.ok) {
      pendingPosts = await response.json();
      displayPendingPosts();
    } else {
      showError('pending-posts-container', 'Failed to load pending posts');
    }
  } catch (error) {
    showError('pending-posts-container', 'Error loading pending posts');
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
      showError('approved-posts-container', 'Failed to load approved posts');
    }
  } catch (error) {
    showError('approved-posts-container', 'Error loading approved posts');
  }
}

async function loadReportedPosts() {
  try {
    const response = await fetch('/admin/community/reported-posts', {
      headers: { 'Accept': 'application/json' }
    });
    if (response.ok) {
      reportedPosts = await response.json();
      displayReportedPosts();
    } else {
      document.getElementById('reported-posts-container').innerHTML = '<div class="text-center py-4 text-sm text-gray-500">No reported posts</div>';
    }
  } catch (error) {
    document.getElementById('reported-posts-container').innerHTML = '<div class="text-center py-4 text-sm text-red-500">Error loading</div>';
  }
}

async function loadReportedComments() {
  try {
    const response = await fetch('/admin/community/reported-comments', {
      headers: { 'Accept': 'application/json' }
    });
    if (response.ok) {
      reportedComments = await response.json();
      displayReportedComments();
    } else {
      document.getElementById('reported-comments-container').innerHTML = '<div class="text-center py-4 text-sm text-gray-500">No reported comments</div>';
    }
  } catch (error) {
    document.getElementById('reported-comments-container').innerHTML = '<div class="text-center py-4 text-sm text-red-500">Error loading</div>';
  }
}

// Display functions
function displayPendingPosts() {
  const container = document.getElementById('pending-posts-container');
  document.getElementById('pending-count').textContent = pendingPosts.length;
  
  if (pendingPosts.length === 0) {
    container.innerHTML = `
      <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg">
        <div class="mx-auto h-16 w-16 text-green-400 mb-4">
          <svg fill="currentColor" viewBox="0 0 20 20" class="w-full h-full">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-primary mb-2">All caught up!</h3>
        <p class="text-gray-500">No pending posts need review at this time.</p>
      </div>
    `;
    return;
  }
  
  container.innerHTML = pendingPosts.map(post => createPostCard(post, true)).join('');
}

function displayApprovedPosts() {
  const container = document.getElementById('approved-posts-container');
  document.getElementById('approved-count').textContent = approvedPosts.length;
  
  if (approvedPosts.length === 0) {
    container.innerHTML = `
      <div class="text-center py-12 bg-white rounded-lg">
        <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
          <svg fill="currentColor" viewBox="0 0 20 20" class="w-full h-full">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-primary mb-2">No posts yet</h3>
        <p class="text-gray-500">Community posts will appear here once approved.</p>
      </div>
    `;
    return;
  }
  
  container.innerHTML = approvedPosts.map(post => createPostCard(post, false)).join('');
}

function displayReportedPosts() {
  const container = document.getElementById('reported-posts-container');
  document.getElementById('reported-posts-count').textContent = reportedPosts.length;
  
  if (reportedPosts.length === 0) {
    container.innerHTML = '<div class="text-center py-4 text-sm text-gray-500">No reported posts</div>';
    return;
  }
  
  container.innerHTML = reportedPosts.map(post => `
    <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm">
      <div class="font-medium text-red-800">${post.user?.first_name} ${post.user?.last_name}</div>
      <div class="text-red-600 truncate">${post.content}</div>
      <div class="text-red-500 text-xs mt-1">${new Date(post.created_at).toLocaleDateString()}</div>
    </div>
  `).join('');
}

function displayReportedComments() {
  const container = document.getElementById('reported-comments-container');
  document.getElementById('reported-comments-count').textContent = reportedComments.length;
  
  if (reportedComments.length === 0) {
    container.innerHTML = '<div class="text-center py-4 text-sm text-gray-500">No reported comments</div>';
    return;
  }
  
  container.innerHTML = reportedComments.map(comment => `
    <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm">
      <div class="font-medium text-red-800">${comment.user?.first_name} ${comment.user?.last_name}</div>
      <div class="text-red-600 truncate">${comment.content}</div>
      <div class="text-red-500 text-xs mt-1">${new Date(comment.created_at).toLocaleDateString()}</div>
    </div>
  `).join('');
}

// Create Facebook-like post card
function createPostCard(post, isPending = false) {
  const userName = post.user ? `${post.user.first_name} ${post.user.last_name}` : 'Unknown User';
  const postDate = new Date(post.created_at).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
  
  const imagesHtml = post.images && post.images.length > 0 ? `
    <div class="mt-3">
      <div class="grid ${post.images.length === 1 ? 'grid-cols-1' : 'grid-cols-2'} gap-2">
        ${post.images.map(image => `
          <img src="${image.image_url}" 
               alt="Post image" 
               class="w-full h-64 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
               onclick="openImageModal('${image.image_url}')">
        `).join('')}
      </div>
    </div>
  ` : '';

  const statusBadge = isPending 
    ? '<span class="bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded-full font-medium">Pending Review</span>'
    : '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">Approved</span>';

  const actionButtons = isPending ? `
    <div class="flex space-x-2 mt-4">
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
  ` : '';

  return `
    <div class="card-bg rounded-lg shadow-sm border border-color post-card">
      <!-- Post Header -->
      <div class="p-4 border-b border-color">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
              <span class="text-white font-medium text-sm">${userName.charAt(0)}</span>
            </div>
            <div>
              <h3 class="font-semibold text-primary">${userName}</h3>
              <div class="flex items-center space-x-2 text-sm text-gray-500">
                <span>${postDate}</span>
                <span>•</span>
                ${statusBadge}
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Post Content -->
      <div class="p-4">
        <div class="text-gray-800 leading-relaxed mb-3">${post.content}</div>
        ${imagesHtml}
      </div>
      
      <!-- Post Stats -->
      <div class="px-4 py-2 border-t border-color">
        <div class="flex items-center justify-between text-sm text-gray-500">
          <span>${post.likes_count || 0} likes</span>
          <span>${post.comments_count || 0} comments</span>
        </div>
      </div>
      
      <!-- Action Buttons -->
      <div class="px-4 py-2 border-t border-color">
        <div class="flex items-center space-x-1">
          <button class="comment-btn flex-1 flex items-center justify-center space-x-2 py-2 px-3 rounded-lg hover:bg-gray-50 transition-colors"
                  onclick="openCommentsModal(${post.id})">
            <i class="far fa-comment"></i>
            <span>Comment</span>
          </button>
        </div>
        ${actionButtons}
      </div>
    </div>
  `;
}

// Modal functions
function openCommentsModal(postId) {
  const post = [...pendingPosts, ...approvedPosts].find(p => p.id === postId);
  if (!post) return;
  
  const modal = document.getElementById('commentsModal');
  const content = document.getElementById('commentsModalContent');
  
  content.innerHTML = `
    <div class="space-y-4">
      ${post.comments && post.comments.length > 0 
        ? post.comments.map(comment => `
            <div class="flex space-x-3">
              <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-blue-600 rounded-full flex items-center justify-center">
                <span class="text-white font-medium text-xs">${comment.user?.first_name?.charAt(0) || 'U'}</span>
              </div>
              <div class="flex-1">
                <div class="bg-gray-200  dark:bg-gray-700 rounded-lg p-3">
                  <div class="font-medium text-sm text-primary">${comment.user?.first_name} ${comment.user?.last_name}</div>
                  <div class="text-secondary">${comment.content}</div>
                </div>
                <div class="text-xs text-primary mt-1">${new Date(comment.created_at).toLocaleDateString()}</div>
              </div>
            </div>
          `).join('')
        : '<div class="text-center py-8 text-gray-500">No comments yet</div>'
      }
    </div>
  `;
  
  modal.style.display = 'flex';
}

function closeCommentsModal() {
  document.getElementById('commentsModal').style.display = 'none';
}

function openImageModal(imageUrl) {
  const modal = document.getElementById('imageModal');
  const modalImage = document.getElementById('modalImage');
  
  modalImage.src = imageUrl;
  modal.style.display = 'flex';
}

function closeImageModal() {
  document.getElementById('imageModal').style.display = 'none';
}

// Review post function
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
      pendingPosts = pendingPosts.filter(post => post.id !== postId);
      displayPendingPosts();
      loadApprovedPosts();
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

// Utility functions
function showError(containerId, message) {
  document.getElementById(containerId).innerHTML = `
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <i class="fas fa-exclamation-circle text-red-400"></i>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error</h3>
          <div class="mt-2 text-sm text-red-700">${message}</div>
        </div>
      </div>
    </div>
  `;
}

function refreshAllData() {
  // Show loading states
  document.getElementById('pending-posts-container').innerHTML = '<div class="flex justify-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
  document.getElementById('approved-posts-container').innerHTML = '<div class="flex justify-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
  
  loadPendingPosts();
  loadApprovedPosts();
  loadReportedPosts();
  loadReportedComments();
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
  
  setTimeout(() => alertDiv.classList.remove('translate-x-full'), 100);
  setTimeout(() => {
    alertDiv.classList.add('translate-x-full');
    setTimeout(() => alertDiv.remove(), 300);
  }, 5000);
}

// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
  refreshAllData();
  
  document.querySelectorAll('#communityTabs button').forEach(tab => {
    tab.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Remove active class from all tabs and content
      document.querySelectorAll('#communityTabs button').forEach(t => {
        t.classList.remove('active');
        t.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        t.classList.remove('text-amber-600', 'border-amber-500', 'text-green-600', 'border-green-500');
      });
      
      document.querySelectorAll('.tab-pane').forEach(content => {
        content.classList.remove('active');
      });
      
      // Add active class to clicked tab
      this.classList.add('active');
      this.classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
      
      // Color based on tab type
      const target = this.getAttribute('data-target');
      if (target === 'approved') {
        this.classList.add('text-green-600', 'border-green-500');
      } else {
        this.classList.add('text-amber-600', 'border-amber-500');
      }
      
      // Show corresponding content
      document.getElementById(target).classList.add('active');
    });
  });
  
  // Close modal when clicking outside
  document.getElementById('commentsModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeCommentsModal();
    }
  });
  
  // Close image modal when clicking outside
  document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeImageModal();
    }
  });
  
  // Close image modal with Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      if (document.getElementById('imageModal').style.display === 'flex') {
        closeImageModal();
      }
    }
  });
});
</script>
@endsection