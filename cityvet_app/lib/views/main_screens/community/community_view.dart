import 'package:cityvet_app/utils/config.dart';
import 'package:flutter/material.dart';
import 'package:cityvet_app/services/community_service.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:cityvet_app/views/main_screens/community/post_create_view.dart';
import 'package:cityvet_app/views/main_screens/community/post_details_view.dart';

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
      return Center(child: CircularProgressIndicator());
    }
    if (_isLoading) {
      return Center(child: CircularProgressIndicator());
    }
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(_error!),
            ElevatedButton(
              onPressed: _fetchPosts,
              child: Text('Retry'),
            ),
          ],
        ),
      );
    }

    return Column(
      children: [
        // Simple Header
        Padding(
          padding: EdgeInsets.all(16),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Community',
                style: TextStyle(
                  fontFamily: Config.primaryFont,
                  fontSize: Config.fontMedium,
                  fontWeight: FontWeight.bold,
                ),
              ),
              ElevatedButton(
                onPressed: _openCreatePost,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Config.primaryColor,
                  foregroundColor: Colors.white,
                ),
                child: Text('Create Post'),
              ),
            ],
          ),
        ),

        // Simple Posts List
        Expanded(
          child: ListView.builder(
            padding: EdgeInsets.all(8),
            itemCount: _posts.length,
            itemBuilder: (context, index) {
              final post = _posts[index];
              final user = post['user'] ?? {};
              final images = post['images'] as List<dynamic>? ?? [];
              final isOwner = _userId != null && user['id']?.toString() == _userId;
              
              return Card(
                margin: EdgeInsets.only(bottom: 8),
                child: Padding(
                  padding: EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // User Info
                      Row(
                        children: [
                          CircleAvatar(
                            radius: 20,
                            backgroundImage: user['image_url'] != null ? 
                              NetworkImage(user['image_url']) : null,
                            child: user['image_url'] == null 
                              ? Icon(Icons.person, size: 20) 
                              : null,
                          ),
                          SizedBox(width: 12),
                          Expanded(
                            child: Text(
                              '${user['first_name'] ?? ''} ${user['last_name'] ?? ''}',
                              style: TextStyle(
                                fontFamily: Config.primaryFont,
                                fontSize: Config.fontMedium,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                          if (isOwner)
                            IconButton(
                              icon: Icon(Icons.delete, color: Colors.red, size: 20),
                              onPressed: () async {
                                final confirm = await showDialog<bool>(
                                  context: context,
                                  builder: (context) => AlertDialog(
                                    title: Text('Delete Post'),
                                    content: Text('Are you sure?'),
                                    actions: [
                                      TextButton(
                                        onPressed: () => Navigator.of(context).pop(false),
                                        child: Text('Cancel'),
                                      ),
                                      TextButton(
                                        onPressed: () => Navigator.of(context).pop(true),
                                        child: Text('Delete'),
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
                                      SnackBar(content: Text('Failed to delete post')),
                                    );
                                  }
                                }
                              },
                            ),
                        ],
                      ),

                      SizedBox(height: 12),

                      // Content
                      Text(
                        post['content'] ?? '',
                        style: TextStyle(
                          fontFamily: Config.primaryFont,
                          fontSize: Config.fontMedium,
                        ),
                      ),

                      // Images (simple horizontal scroll)
                      if (images.isNotEmpty) ...[
                        SizedBox(height: 12),
                        SizedBox(
                          height: 100,
                          child: ListView.builder(
                            scrollDirection: Axis.horizontal,
                            itemCount: images.length,
                            itemBuilder: (context, imgIdx) {
                              final img = images[imgIdx];
                              return Container(
                                width: 100,
                                margin: EdgeInsets.only(right: 8),
                                child: Image.network(
                                  img['image_url'],
                                  fit: BoxFit.cover,
                                  errorBuilder: (context, error, stackTrace) {
                                    return Container(
                                      color: Colors.grey[300],
                                      child: Icon(Icons.error),
                                    );
                                  },
                                ),
                              );
                            },
                          ),
                        ),
                      ],

                      SizedBox(height: 12),

                      // Actions
                      Row(
                        children: [
                          TextButton.icon(
                            onPressed: () => _likePost(post['id']),
                            icon: Icon(Icons.thumb_up_outlined, size: 16),
                            label: Text('${post['likes_count'] ?? 0}'),
                            style: TextButton.styleFrom(
                              foregroundColor: Config.primaryColor,
                              padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            ),
                          ),
                          SizedBox(width: 16),
                          TextButton.icon(
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
                            icon: Icon(Icons.comment_outlined, size: 16),
                            label: Text('${post['comments_count'] ?? 0}'),
                            style: TextButton.styleFrom(
                              foregroundColor: Config.color524F4F,
                              padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            ),
                          ),
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