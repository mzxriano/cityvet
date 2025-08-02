import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';
import 'package:cityvet_app/services/community_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/views/main_screens/community/post_edit_view.dart';

class MyPostsView extends StatefulWidget {
  const MyPostsView({Key? key}) : super(key: key);

  @override
  State<MyPostsView> createState() => _MyPostsViewState();
}

class _MyPostsViewState extends State<MyPostsView> {
  final CommunityService _service = CommunityService();
  List<dynamic> _posts = [];
  bool _isLoading = true;
  String? _error;
  String? _token;

  @override
  void initState() {
    super.initState();
    _initTokenAndFetch();
  }

  Future<void> _initTokenAndFetch() async {
    _token = await AuthStorage().getToken();
    if (_token == null) {
      setState(() {
        _error = 'You must be logged in to view your posts.';
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
      final response = await _service.fetchUserPosts(_token!);
      if (!mounted) return;
      setState(() {
        _posts = response.data;
        _isLoading = false;
      });
    } catch (e) {
      print('Fetching user posts error $e');
      if (!mounted) return;
      setState(() {
        _error = 'Failed to load your posts.';
        _isLoading = false;
      });
    }
  }

  String _getStatusText(String status) {
    switch (status) {
      case 'pending':
        return 'Pending Review';
      case 'approved':
        return 'Approved';
      case 'rejected':
        return 'Rejected';
      default:
        return 'Unknown';
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'pending':
        return Colors.orange;
      case 'approved':
        return Colors.green;
      case 'rejected':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: const Text(
          'My Posts',
          style: TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 18,
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        foregroundColor: Colors.black87,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.error_outline, size: 64, color: Colors.grey[400]),
                      const SizedBox(height: 16),
                      Text(
                        _error!,
                        style: TextStyle(
                          fontSize: 16,
                          color: Colors.grey[600],
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                )
              : _posts.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.post_add, size: 64, color: Colors.grey[400]),
                          const SizedBox(height: 16),
                          Text(
                            'No posts yet',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w600,
                              color: Colors.grey[600],
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Create your first post in the community!',
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey[500],
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: _fetchPosts,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _posts.length,
                        itemBuilder: (context, index) {
                          final post = _posts[index];
                          // final user = post['user'] as Map<String, dynamic>?;
                          final images = post['images'] as List<dynamic>? ?? [];
                          final status = post['status'] as String? ?? 'pending';

                          return Container(
                            margin: const EdgeInsets.only(bottom: 16),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(12),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withOpacity(0.05),
                                  blurRadius: 10,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  // Status Badge
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                    decoration: BoxDecoration(
                                      color: _getStatusColor(status).withOpacity(0.1),
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(color: _getStatusColor(status)),
                                    ),
                                    child: Text(
                                      _getStatusText(status),
                                      style: TextStyle(
                                        color: _getStatusColor(status),
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(height: 12),

                                  // Content
                                  Text(
                                    post['content'] ?? '',
                                    style: TextStyle(
                                      fontFamily: Config.primaryFont,
                                      fontSize: Config.fontMedium,
                                    ),
                                  ),

                                  // Images
                                  if (images.isNotEmpty) ...[
                                    const SizedBox(height: 12),
                                    SizedBox(
                                      height: 100,
                                      child: ListView.builder(
                                        scrollDirection: Axis.horizontal,
                                        itemCount: images.length,
                                        itemBuilder: (imgIdx, imgIndex) {
                                          final img = images[imgIndex];
                                          return Container(
                                            width: 100,
                                            margin: const EdgeInsets.only(right: 8),
                                            child: ClipRRect(
                                              borderRadius: BorderRadius.circular(8),
                                              child: Image.network(
                                                img['image_url'],
                                                fit: BoxFit.cover,
                                                errorBuilder: (context, error, stackTrace) {
                                                  return Container(
                                                    color: Colors.grey[300],
                                                    child: const Icon(Icons.error),
                                                  );
                                                },
                                              ),
                                            ),
                                          );
                                        },
                                      ),
                                    ),
                                  ],

                                  const SizedBox(height: 12),

                                  // Post Info
                                  Row(
                                    children: [
                                      Icon(Icons.schedule, size: 16, color: Colors.grey[600]),
                                      const SizedBox(width: 4),
                                      Text(
                                        'Posted on ${DateTime.parse(post['created_at']).toString().substring(0, 10)}',
                                        style: TextStyle(
                                          fontSize: 12,
                                          color: Colors.grey[600],
                                        ),
                                      ),
                                      const Spacer(),
                                      if (status == 'pending') ...[
                                        Icon(Icons.info_outline, size: 16, color: Colors.orange),
                                        const SizedBox(width: 4),
                                        Text(
                                          'Waiting for admin review',
                                          style: TextStyle(
                                            fontSize: 12,
                                            color: Colors.orange,
                                          ),
                                        ),
                                      ],
                                    ],
                                  ),

                                  // Action Buttons (for all user's posts)
                                  ...[
                                    const SizedBox(height: 12),
                                    Row(
                                      children: [
                                        Expanded(
                                          child: OutlinedButton.icon(
                                            onPressed: () async {
                                              final result = await Navigator.of(context).push(
                                                MaterialPageRoute(
                                                  builder: (context) => EditPostView(
                                                    token: _token!,
                                                    post: post,
                                                  ),
                                                ),
                                              );
                                              if (result == true) {
                                                _fetchPosts();
                                              }
                                            },
                                            icon: const Icon(Icons.edit, size: 16),
                                            label: const Text('Edit'),
                                            style: OutlinedButton.styleFrom(
                                              foregroundColor: Colors.blue,
                                              side: const BorderSide(color: Colors.blue),
                                            ),
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: OutlinedButton.icon(
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
                                                  ScaffoldMessenger.of(context).showSnackBar(
                                                    const SnackBar(content: Text('Failed to delete post')),
                                                  );
                                                }
                                              }
                                            },
                                            icon: const Icon(Icons.delete, size: 16),
                                            label: const Text('Delete'),
                                            style: OutlinedButton.styleFrom(
                                              foregroundColor: Colors.red,
                                              side: const BorderSide(color: Colors.red),
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          );
                        },
                      ),
                    ),
    );
  }

  @override
  void dispose() {
    super.dispose();
  }
} 