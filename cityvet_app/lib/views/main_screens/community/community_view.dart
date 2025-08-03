import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';
import 'package:cityvet_app/services/community_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/views/main_screens/community/post_create_view.dart';
import 'package:cityvet_app/views/main_screens/community/post_details_view.dart';
import 'package:cityvet_app/views/main_screens/community/post_edit_view.dart';
import 'package:cityvet_app/views/main_screens/community/my_posts_view.dart';
import 'package:intl/intl.dart'; 

enum PostFilter { newest, oldest }

class CommunityView extends StatefulWidget {
  const CommunityView({Key? key}) : super(key: key);

  @override
  State<CommunityView> createState() => _CommunityViewState();
}

class _CommunityViewState extends State<CommunityView> {
  final CommunityService _service = CommunityService();
  List<dynamic> _posts = [];
  List<dynamic> _filteredPosts = [];
  bool _isLoading = true;
  String? _error;
  String? _token;
  String? _userId;
  bool _tokenLoaded = false;
  PostFilter _currentFilter = PostFilter.newest;

  @override
  void initState() {
    super.initState();
    _initTokenAndUserIdAndFetch();
  }

  Future<void> _initTokenAndUserIdAndFetch() async {
    _token = await AuthStorage().getToken();
    _userId = await AuthStorage().getUserId();
    _tokenLoaded = true;
    if (_token == null) {
      setState(() {
        _error = 'You must be logged in to view the community.';
        _isLoading = false;
      });
      return;
    }
    _fetchPosts();
  }

  Future<void> _fetchPosts() async {
    if (!mounted) return;
    setState(() { _isLoading = true; _error = null; });
    try {
      final response = await _service.fetchPosts(_token!);
      if (!mounted) return;
      setState(() {
        _posts = response.data;
        _applyFilter(); // Apply current filter after fetching
        _isLoading = false;
      });
    } catch (e) {
      print('Fetching posts error $e');
      if (!mounted) return;
      setState(() {
        _error = 'Failed to load posts.';
        _isLoading = false;
      });
    }
  }

  void _applyFilter() {
    List<dynamic> sortedPosts = List.from(_posts);
    
    sortedPosts.sort((a, b) {
      DateTime? dateA = _parseDate(a['created_at']);
      DateTime? dateB = _parseDate(b['created_at']);
      
      if (dateA == null && dateB == null) return 0;
      if (dateA == null) return 1;
      if (dateB == null) return -1;
      
      if (_currentFilter == PostFilter.newest) {
        return dateB.compareTo(dateA); // Newest first
      } else {
        return dateA.compareTo(dateB); // Oldest first
      }
    });
    
    _filteredPosts = sortedPosts;
  }

  DateTime? _parseDate(String? dateString) {
    if (dateString == null) return null;
    try {
      return DateTime.parse(dateString);
    } catch (e) {
      return null;
    }
  }

  void _onFilterChanged(PostFilter? filter) {
    if (filter != null && filter != _currentFilter) {
      setState(() {
        _currentFilter = filter;
        _applyFilter();
      });
    }
  }

  // Pull-to-refresh handler
  Future<void> _onRefresh() async {
    if (_token == null) return;
    try {
      final response = await _service.fetchPosts(_token!);
      if (!mounted) return;
      setState(() {
        _posts = response.data;
        _applyFilter(); // Apply current filter after refresh
        _error = null;
      });
    } catch (e) {
      print('Refresh error: $e');
      if (!mounted) return;
      // Optionally show a snackbar for refresh errors
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Failed to refresh posts'),
          duration: Duration(seconds: 2),
        ),
      );
    }
  }

  Future<void> _likePost(int postId) async {
    if (_token == null || !mounted) return;
    try {
      await _service.likePost(postId, _token!);
      if (mounted) {
        _fetchPosts();
      }
    } catch (e) {
      //  show error
    }
  }

  // Helper method to format time
  String _formatTime(String? timeString) {
    if (timeString == null) return 'Just now';
    
    try {
      DateTime dateTime = DateTime.parse(timeString);
      DateTime now = DateTime.now();
      Duration difference = now.difference(dateTime);
      
      if (difference.inMinutes < 1) {
        return 'Just now';
      } else if (difference.inMinutes < 60) {
        return '${difference.inMinutes}m ago';
      } else if (difference.inHours < 24) {
        return '${difference.inHours}h ago';
      } else if (difference.inDays < 7) {
        return '${difference.inDays}d ago';
      } else {
        return DateFormat('MMM d, yyyy').format(dateTime);
      }
    } catch (e) {
      return timeString; 
    }
  }

  void _openCreatePost() async {
    if (_token == null) return;
    final result = await Navigator.of(context).push(MaterialPageRoute(
      builder: (context) => CreatePostView(token: _token!),
    ));
    if (result == true) {
      _fetchPosts();
    }
  }

  void _openPostDetails(int postId) async {
    if (_token == null || !mounted) return;
    
    try {
      await Navigator.of(context).push(MaterialPageRoute(
        builder: (context) => PostDetailsView(postId: postId, token: _token!),
      ));
      if (mounted) {
        _fetchPosts();
      }
    } catch (e) {
      print('Error opening post details: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to open comments')),
        );
      }
    }
  }

  Widget _buildFilterChips() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        children: [
          Text(
            'Sort by:',
            style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: 14,
              fontWeight: FontWeight.w500,
              color: Colors.grey[700],
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Row(
              children: [
                FilterChip(
                  label: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.schedule,
                        size: 16,
                        color: _currentFilter == PostFilter.newest 
                            ? Colors.white 
                            : Config.primaryColor,
                      ),
                      const SizedBox(width: 4),
                      const Text('Newest'),
                    ],
                  ),
                  selected: _currentFilter == PostFilter.newest,
                  onSelected: (selected) {
                    if (selected) _onFilterChanged(PostFilter.newest);
                  },
                  selectedColor: Config.primaryColor,
                  checkmarkColor: Colors.white,
                  labelStyle: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: 13,
                    color: _currentFilter == PostFilter.newest 
                        ? Colors.white 
                        : Config.primaryColor,
                    fontWeight: FontWeight.w500,
                  ),
                  side: BorderSide(
                    color: Config.primaryColor,
                    width: 1,
                  ),
                  backgroundColor: Colors.white,
                ),
                const SizedBox(width: 8),
                FilterChip(
                  label: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.history,
                        size: 16,
                        color: _currentFilter == PostFilter.oldest 
                            ? Colors.white 
                            : Config.primaryColor,
                      ),
                      const SizedBox(width: 4),
                      const Text('Oldest'),
                    ],
                  ),
                  selected: _currentFilter == PostFilter.oldest,
                  onSelected: (selected) {
                    if (selected) _onFilterChanged(PostFilter.oldest);
                  },
                  selectedColor: Config.primaryColor,
                  checkmarkColor: Colors.white,
                  labelStyle: TextStyle(
                    fontFamily: Config.primaryFont,
                    fontSize: 13,
                    color: _currentFilter == PostFilter.oldest 
                        ? Colors.white 
                        : Config.primaryColor,
                    fontWeight: FontWeight.w500,
                  ),
                  side: BorderSide(
                    color: Config.primaryColor,
                    width: 1,
                  ),
                  backgroundColor: Colors.white,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (!_tokenLoaded) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                _error!,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 16),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: _fetchPosts,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );
    }

    return Column(
      children: [
        // Header with proper responsive design
        Container(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              // Title
              Row(
                children: [
                  Expanded(
                    child: Text(
                      'Community',
                      style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontMedium,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              // Buttons row with proper wrapping
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () {
                        Navigator.of(context).push(MaterialPageRoute(
                          builder: (context) => const MyPostsView(),
                        ));
                      },
                      style: OutlinedButton.styleFrom(
                        foregroundColor: Config.primaryColor,
                        side: BorderSide(color: Config.primaryColor),
                      ),
                      child: const Text('My Posts'),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: _openCreatePost,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Config.primaryColor,
                        foregroundColor: Colors.white,
                      ),
                      child: const Text('Create Post'),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),

        // Filter Chips
        if (_posts.isNotEmpty) _buildFilterChips(),

        // Posts List with RefreshIndicator
        Expanded(
          child: RefreshIndicator(
            onRefresh: _onRefresh,
            color: Config.primaryColor,
            backgroundColor: Colors.white,
            child: _filteredPosts.isEmpty 
              ? ListView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  children: const [
                    SizedBox(height: 100),
                    Center(
                      child: Text(
                        'No posts available',
                        style: TextStyle(fontSize: 16, color: Colors.grey),
                      ),
                    ),
                  ],
                )
              : ListView.builder(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  itemCount: _filteredPosts.length,
                  itemBuilder: (context, index) {
                    final post = _filteredPosts[index];
                    final user = post['user'] ?? {};
                    final images = post['images'] as List<dynamic>? ?? [];
                    final isOwner = _userId != null && user['id']?.toString() == _userId;
                    
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // User Info Row with proper overflow handling
                            Row(
                              children: [
                                CircleAvatar(
                                  radius: 22,
                                  backgroundImage: user['image_url'] != null ? 
                                    NetworkImage(user['image_url']) : null,
                                  child: user['image_url'] == null 
                                    ? const Icon(Icons.person, size: 22) 
                                    : null,
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        '${user['first_name'] ?? ''} ${user['last_name'] ?? ''}'.trim(),
                                        style: TextStyle(
                                          fontFamily: Config.primaryFont,
                                          fontSize: 16, 
                                          fontWeight: FontWeight.w600,
                                        ),
                                        overflow: TextOverflow.ellipsis,
                                        maxLines: 1,
                                      ),
                                      const SizedBox(height: 2), 
                                      Text(
                                        _formatTime(post['created_at']), 
                                        style: TextStyle(
                                          fontFamily: Config.primaryFont,
                                          fontSize: 13, 
                                          color: Colors.grey[600],
                                          fontWeight: FontWeight.w400,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                // Action buttons with proper spacing
                                if (isOwner) ...[
                                  const SizedBox(width: 8),
                                  Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      IconButton(
                                        icon: const Icon(Icons.edit, color: Colors.blue, size: 20),
                                        onPressed: () async {
                                          final result = await Navigator.of(context).push(MaterialPageRoute(
                                            builder: (context) => EditPostView(
                                              token: _token!,
                                              post: post,
                                            ),
                                          ));
                                          if (result == true) {
                                            _fetchPosts();
                                          }
                                        },
                                        padding: const EdgeInsets.all(4),
                                        constraints: const BoxConstraints(
                                          minWidth: 32,
                                          minHeight: 32,
                                        ),
                                      ),
                                      IconButton(
                                        icon: const Icon(Icons.delete, color: Colors.red, size: 20),
                                        onPressed: () async {
                                          final confirm = await showDialog<bool>(
                                            context: context,
                                            builder: (context) => AlertDialog(
                                              title: const Text('Delete Post'),
                                              content: const Text('Are you sure you want to delete this post?'),
                                              actions: [
                                                TextButton(
                                                  onPressed: () => Navigator.of(context).pop(false),
                                                  child: const Text('Cancel'),
                                                ),
                                                TextButton(
                                                  onPressed: () => Navigator.of(context).pop(true),
                                                  child: const Text('Delete'),
                                                ),
                                              ],
                                            ),
                                          );
                                          if (confirm == true) {
                                            try {
                                              await _service.deletePost(post['id'], _token!);
                                              _fetchPosts();
                                            } catch (e) {
                                              if (mounted) {
                                                ScaffoldMessenger.of(context).showSnackBar(
                                                  const SnackBar(content: Text('Failed to delete post')),
                                                );
                                              }
                                            }
                                          }
                                        },
                                        padding: const EdgeInsets.all(4),
                                        constraints: const BoxConstraints(
                                          minWidth: 32,
                                          minHeight: 32,
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ],
                            ),

                            const SizedBox(height: 14), 

                            // Content with proper text wrapping
                            if (post['content'] != null && post['content'].toString().isNotEmpty)
                              Container(
                                width: double.infinity,
                                child: Text(
                                  post['content'].toString(),
                                  style: TextStyle(
                                    fontFamily: Config.primaryFont,
                                    fontSize: 15,
                                    height: 1.4,
                                    color: Colors.grey[800], 
                                  ),
                                  softWrap: true,
                                ),
                              ),

                            // Images with proper sizing and error handling
                            if (images.isNotEmpty) ...[
                              const SizedBox(height: 14),
                              SizedBox(
                                height: 130,
                                child: ListView.builder(
                                  scrollDirection: Axis.horizontal,
                                  itemCount: images.length,
                                  itemBuilder: (context, imgIdx) {
                                    final img = images[imgIdx];
                                    return Container(
                                      width: 130,
                                      height: 130,
                                      margin: const EdgeInsets.only(right: 10), 
                                      child: ClipRRect(
                                        borderRadius: BorderRadius.circular(10), 
                                        child: Image.network(
                                          img['image_url'] ?? '',
                                          fit: BoxFit.cover,
                                          loadingBuilder: (context, child, loadingProgress) {
                                            if (loadingProgress == null) return child;
                                            return Container(
                                              color: Colors.grey[200],
                                              child: const Center(
                                                child: CircularProgressIndicator(strokeWidth: 2),
                                              ),
                                            );
                                          },
                                          errorBuilder: (context, error, stackTrace) {
                                            return Container(
                                              color: Colors.grey[300],
                                              child: const Icon(
                                                Icons.broken_image,
                                                color: Colors.grey,
                                                size: 32,
                                              ),
                                            );
                                          },
                                        ),
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ],

                            const SizedBox(height: 14),

                            // Actions row with proper spacing
                            Row(
                              children: [
                                Flexible(
                                  child: TextButton.icon(
                                    onPressed: () => _likePost(post['id']),
                                    icon: const Icon(Icons.thumb_up_outlined, size: 18),
                                    label: Text(
                                      '${post['likes_count'] ?? 0}',
                                      style: const TextStyle(fontSize: 14), 
                                    ),
                                    style: TextButton.styleFrom(
                                      foregroundColor: Config.primaryColor,
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6), 
                                      minimumSize: Size.zero,
                                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 20), 
                                Flexible(
                                  child: TextButton.icon(
                                    onPressed: () {
                                      final postId = post['id'];
                                      if (postId != null) {
                                        int? id;
                                        if (postId is int) {
                                          id = postId;
                                        } else if (postId is String) {
                                          id = int.tryParse(postId);
                                        }
                                        if (id != null) {
                                          _openPostDetails(id);
                                        }
                                      }
                                    },
                                    icon: const Icon(Icons.comment_outlined, size: 18), 
                                    label: Text(
                                      '${post['comments_count'] ?? 0}',
                                      style: const TextStyle(fontSize: 14), 
                                    ),
                                    style: TextButton.styleFrom(
                                      foregroundColor: Config.color524F4F,
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6), 
                                      minimumSize: Size.zero,
                                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                    ),
                                  ),
                                ),
                                const Spacer(),
                              ],
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
          ),
        ),
      ],
    );
  }

  @override
  void dispose() {
    super.dispose();
  }
}