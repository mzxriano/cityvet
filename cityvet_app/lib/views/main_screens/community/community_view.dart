import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';
import 'package:cityvet_app/services/community_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/views/main_screens/community/post_create_view.dart';
import 'package:cityvet_app/views/main_screens/community/post_details_view.dart';
import 'package:cityvet_app/views/main_screens/community/post_edit_view.dart';
import 'package:cityvet_app/views/main_screens/community/my_posts_view.dart';

class CommunityView extends StatefulWidget {
  const CommunityView({Key? key}) : super(key: key);

  @override
  State<CommunityView> createState() => _CommunityViewState();
}

class _CommunityViewState extends State<CommunityView> {
  final CommunityService _service = CommunityService();
  List<dynamic> _posts = [];
  bool _isLoading = true;
  String? _error;
  String? _token;
  String? _userId;
  bool _tokenLoaded = false;

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
    setState(() { _isLoading = true; _error = null; });
    try {
      final response = await _service.fetchPosts(_token!);
      setState(() {
        _posts = response.data;
        _isLoading = false;
      });
    } catch (e) {
      print('Fetching posts error $e');
      setState(() {
        _error = 'Failed to load posts.';
        _isLoading = false;
      });
    }
  }

  Future<void> _likePost(int postId) async {
    if (_token == null) return;
    try {
      await _service.likePost(postId, _token!);
      _fetchPosts();
    } catch (e) {
      // Optionally show error
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

        // Posts List with proper sizing
        Expanded(
          child: _posts.isEmpty 
            ? const Center(
                child: Text(
                  'No posts available',
                  style: TextStyle(fontSize: 16, color: Colors.grey),
                ),
              )
            : ListView.builder(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                itemCount: _posts.length,
                itemBuilder: (context, index) {
                  final post = _posts[index];
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
                                radius: 20,
                                backgroundImage: user['image_url'] != null ? 
                                  NetworkImage(user['image_url']) : null,
                                child: user['image_url'] == null 
                                  ? const Icon(Icons.person, size: 20) 
                                  : null,
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Text(
                                  '${user['first_name'] ?? ''} ${user['last_name'] ?? ''}'.trim(),
                                  style: TextStyle(
                                    fontFamily: Config.primaryFont,
                                    fontSize: Config.fontMedium,
                                    fontWeight: FontWeight.w600,
                                  ),
                                  overflow: TextOverflow.ellipsis,
                                  maxLines: 1,
                                ),
                              ),
                              // Action buttons with proper spacing
                              if (isOwner && post['status'] == 'pending') ...[
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

                          const SizedBox(height: 12),

                          // Content with proper text wrapping
                          if (post['content'] != null && post['content'].toString().isNotEmpty)
                            Container(
                              width: double.infinity,
                              child: Text(
                                post['content'].toString(),
                                style: TextStyle(
                                  fontFamily: Config.primaryFont,
                                  fontSize: Config.fontMedium,
                                  height: 1.4,
                                ),
                                softWrap: true,
                              ),
                            ),

                          // Images with proper sizing and error handling
                          if (images.isNotEmpty) ...[
                            const SizedBox(height: 12),
                            SizedBox(
                              height: 120,
                              child: ListView.builder(
                                scrollDirection: Axis.horizontal,
                                itemCount: images.length,
                                itemBuilder: (context, imgIdx) {
                                  final img = images[imgIdx];
                                  return Container(
                                    width: 120,
                                    height: 120,
                                    margin: const EdgeInsets.only(right: 8),
                                    child: ClipRRect(
                                      borderRadius: BorderRadius.circular(8),
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

                          const SizedBox(height: 12),

                          // Actions row with proper spacing
                          Row(
                            children: [
                              Flexible(
                                child: TextButton.icon(
                                  onPressed: () => _likePost(post['id']),
                                  icon: const Icon(Icons.thumb_up_outlined, size: 16),
                                  label: Text('${post['likes_count'] ?? 0}'),
                                  style: TextButton.styleFrom(
                                    foregroundColor: Config.primaryColor,
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                    minimumSize: Size.zero,
                                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 16),
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
                                  icon: const Icon(Icons.comment_outlined, size: 16),
                                  label: Text('${post['comments_count'] ?? 0}'),
                                  style: TextButton.styleFrom(
                                    foregroundColor: Config.color524F4F,
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                    minimumSize: Size.zero,
                                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                  ),
                                ),
                              ),
                              // Add flexible spacer to push content left
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
      ],
    );
  }
}