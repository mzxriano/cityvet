import 'package:cityvet_app/components/role.dart';
import 'package:cityvet_app/utils/config.dart';
import 'package:cityvet_app/utils/auth_storage.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:cityvet_app/services/community_service.dart';
import 'package:cityvet_app/views/main_screens/community/post_edit_view.dart';
import 'package:intl/intl.dart'; 

class PostDetailsView extends StatefulWidget {
  final int postId;
  final String token;
  const PostDetailsView({Key? key, required this.postId, required this.token}) : super(key: key);

  @override
  State<PostDetailsView> createState() => _PostDetailsViewState();
}

class _PostDetailsViewState extends State<PostDetailsView> {
  final CommunityService _service = CommunityService();
  Map<String, dynamic>? _post;
  List<dynamic> _comments = [];
  bool _isLoading = true;
  String? _error;
  final TextEditingController _commentController = TextEditingController();
  int? _replyToCommentId;
  String? _userId;

  @override
  void initState() {
    super.initState();
    _initUserIdAndFetch();
  }

  Future<void> _initUserIdAndFetch() async {
    _userId = await AuthStorage().getUserId();
    _fetchPostAndComments();
  }

  Future<void> _fetchPostAndComments() async {
    if (!mounted) return;
    setState(() { _isLoading = true; _error = null; });
    try {
      final postResp = await _service.dio.get('/community/${widget.postId}', options: Options(headers: {'Authorization': 'Bearer ${widget.token}'}));
      final commentsResp = await _service.fetchComments(widget.postId, widget.token);
      if (!mounted) return;
      setState(() {
        _post = postResp.data;
        _comments = commentsResp.data;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() { _error = 'Failed to load post.'; _isLoading = false; });
    }
  }

  Future<void> _addComment() async {
    final content = _commentController.text.trim();
    if (content.isEmpty || !mounted) return;
    try {
      await _service.addComment(
        postId: widget.postId,
        content: content,
        parentId: _replyToCommentId?.toString(),
        token: widget.token,
      );
      if (!mounted) return;
      _commentController.clear();
      setState(() { _replyToCommentId = null; });
      _fetchPostAndComments();
    } catch (e) {
      // Optionally show error
    }
  }

  Future<void> _editPost() async {
    if (_post == null) return;
    final result = await Navigator.of(context).push(MaterialPageRoute(
      builder: (context) => EditPostView(
        token: widget.token,
        post: _post!,
      ),
    ));
    if (result == true) {
      _fetchPostAndComments();
    }
  }

  Future<void> _deletePost() async {
    if (_post == null) return;
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
        await _service.deletePost(_post!['id'], widget.token);
        Navigator.of(context).pop(); 
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Failed to delete post')),
          );
        }
      }
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

  Widget _buildComment(Map<String, dynamic> comment, {int indent = 0}) {
    final user = comment['user'] ?? {};
    print('user role ${user['role_id']}');
    final children = comment['children'] as List<dynamic>? ?? [];
    final isReply = indent > 0;
    
    return Container(
      margin: EdgeInsets.only(
        left: isReply ? 50.0 : 16.0, 
        top: isReply ? 6 : 12,
        bottom: 0,
        right: 16,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Profile Avatar
              CircleAvatar(
                radius: isReply ? 18 : 22, 
                backgroundColor: Colors.grey[300],
                backgroundImage: user['image_url'] != null 
                  ? NetworkImage(user['image_url']) 
                  : null,
                child: user['image_url'] == null
                  ? Icon(Icons.person, size: isReply ? 18 : 22, color: Colors.grey[600])
                  : null,
              ),
              const SizedBox(width: 12),
              
              // Comment Content
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Comment bubble (Facebook style)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14), 
                      decoration: BoxDecoration(
                        color: isReply ? Colors.grey[100] : Colors.grey[200],
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Username
                          Text(
                            '${user['first_name'] ?? ''} ${user['last_name'] ?? ''}'.trim(),
                            style: TextStyle(
                              fontFamily: Config.primaryFont,
                              fontSize: isReply ? 15 : 16,
                              fontWeight: FontWeight.w600,
                              color: Config.color524F4F,
                            ),
                          ),
                          const SizedBox(height: 6), 
                          RoleWidget()['Owner'],
                          const SizedBox(height: 6), 
                          // Comment text
                          Text(
                            comment['content'] ?? '',
                            style: TextStyle(
                              fontSize: isReply ? 15 : 16, 
                              fontFamily: Config.primaryFont,
                              color: Config.tertiaryColor,
                              height: 1.4,
                            ),
                          ),
                        ],
                      ),
                    ),
                    
                    if (!isReply) ...[
                      const SizedBox(height: 10), 
                      Row(
                        children: [
                          const SizedBox(width: 16), 
                          // _buildActionButton('Like', Icons.thumb_up_alt_outlined, () {
                          //   
                          // }),
                          const SizedBox(width: 20),
                          _buildActionButton('Reply', Icons.reply_outlined, () {
                            setState(() { _replyToCommentId = comment['id']; });
                            FocusScope.of(context).requestFocus(FocusNode());
                          }),
                          const SizedBox(width: 20),
                          Text(
                            _formatTime(comment['created_at']), 
                            style: TextStyle(
                              fontSize: 13, 
                              color: Colors.grey[600],
                              fontWeight: FontWeight.w400,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
          
          if (children.isNotEmpty) ...[
            const SizedBox(height: 8),
            ...children.map<Widget>((child) => _buildComment(child, indent: indent + 1)).toList(),
          ],
          
          const SizedBox(height: 8),
        ],
      ),
    );
  }

  Widget _buildActionButton(String label, IconData icon, VoidCallback onPressed) {
    return GestureDetector(
      onTap: onPressed,
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            icon,
            size: 17, 
            color: Colors.grey[600],
          ),
          const SizedBox(width: 5),
          Text(
            label,
            style: TextStyle(
              fontSize: 13, 
              color: Colors.grey[600],
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Post Details'),
          backgroundColor: Colors.white,
          foregroundColor: Colors.black87,
          elevation: 0,
        ),
        body: const Center(
          child: CircularProgressIndicator(),
        ),
      );
    }

    if (_error != null) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Post Details'),
          backgroundColor: Colors.white,
          foregroundColor: Colors.black87,
          elevation: 0,
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error_outline, size: 64, color: Colors.grey[400]),
              const SizedBox(height: 16),
              Text(
                _error!,
                style: TextStyle(
                  fontSize: 18, 
                  color: Colors.grey[600],
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: _fetchPostAndComments,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );
    }

    final post = _post ?? {};
    final user = post['user'] ?? {};
    final images = post['images'] as List<dynamic>? ?? [];

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: const Text(
          'Post Details',
          style: TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 20,
          ),
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
        actions: [
          // Show edit/delete buttons only for post owner
          if (_userId != null && _post != null && _post!['user'] != null && 
              _post!['user']['id']?.toString() == _userId) ...[
            IconButton(
              icon: const Icon(Icons.edit, color: Colors.blue),
              onPressed: _editPost,
            ),
            IconButton(
              icon: const Icon(Icons.delete, color: Colors.red),
              onPressed: _deletePost,
            ),
          ],
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Post Content
                  Container(
                    width: double.infinity,
                    margin: const EdgeInsets.all(16),
                    padding: const EdgeInsets.all(22), 
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.08),
                          blurRadius: 12,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // User Info
                        Row(
                          children: [
                            CircleAvatar(
                              radius: 26, 
                              backgroundColor: Colors.grey[300],
                              backgroundImage: user['image_url'] != null 
                                ? NetworkImage(user['image_url']) 
                                : null,
                              child: user['image_url'] == null
                                ? Icon(Icons.person, size: 26, color: Colors.grey[600])
                                : null,
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    '${user['first_name'] ?? ''} ${user['last_name'] ?? ''}'.trim(),
                                    style: TextStyle(
                                      fontFamily: Config.primaryFont,
                                      fontSize: 18, 
                                      fontWeight: FontWeight.w600,
                                      color: Config.color524F4F,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    _formatTime(post['created_at']), 
                                    style: TextStyle(
                                      fontSize: 14, 
                                      color: Colors.grey[600],
                                      fontWeight: FontWeight.w400,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 18), 
                        
                        // Post Content
                        Text(
                          post['content'] ?? '',
                          style: TextStyle(
                            fontFamily: Config.primaryFont,
                            fontSize: 17, 
                            color: Config.tertiaryColor,
                            height: 1.5,
                          ),
                        ),
                        
                        // Images
                        if (images.isNotEmpty) ...[
                          const SizedBox(height: 18),
                          SizedBox(
                            height: 160,
                            child: ListView.separated(
                              scrollDirection: Axis.horizontal,
                              itemCount: images.length,
                              separatorBuilder: (_, __) => const SizedBox(width: 12),
                              itemBuilder: (context, imgIdx) {
                                final img = images[imgIdx];
                                return ClipRRect(
                                  borderRadius: BorderRadius.circular(12),
                                  child: Image.network(
                                    img['image_url'],
                                    width: 160,
                                    height: 160,
                                    fit: BoxFit.cover,
                                    errorBuilder: (context, error, stackTrace) {
                                      return Container(
                                        width: 160,
                                        height: 160,
                                        color: Colors.grey[200],
                                        child: Icon(Icons.image, color: Colors.grey[400]),
                                      );
                                    },
                                  ),
                                );
                              },
                            ),
                          ),
                        ],
                        
                        const SizedBox(height: 18),
                        
                        // Engagement Stats
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              decoration: BoxDecoration(
                                color: Colors.grey[100],
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Row(
                                children: [
                                  Icon(Icons.thumb_up_alt_outlined, size: 20, color: Colors.grey[600]),
                                  const SizedBox(width: 7),
                                  Text(
                                    '${post['likes_count'] ?? 0}', 
                                    style: TextStyle(
                                      color: Colors.grey[600],
                                      fontSize: 15,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 12),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10), 
                              decoration: BoxDecoration(
                                color: Colors.grey[100],
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Row(
                                children: [
                                  Icon(Icons.comment_outlined, size: 20, color: Colors.grey[600]),
                                  const SizedBox(width: 7),
                                  Text(
                                    '${post['comments_count'] ?? 0}', 
                                    style: TextStyle(
                                      color: Colors.grey[600],
                                      fontSize: 15, 
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  
                  // Comments Section Header
                  if (_comments.isNotEmpty) ...[
                    Container(
                      width: double.infinity,
                      height: 8,
                      color: Colors.grey[100],
                    ),
                    Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Text(
                        'Comments',
                        style: TextStyle(
                          fontWeight: FontWeight.w600,
                          fontSize: 20, 
                          color: Colors.black87,
                        ),
                      ),
                    ),
                    
                    // Comments List
                    ..._comments.map<Widget>((c) => _buildComment(c)).toList(),
                  ],
                  
                  const SizedBox(height: 100), 
                ],
              ),
            ),
          ),
          
          // Comment Input (Fixed at bottom)
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border(
                top: BorderSide(color: Colors.grey[200]!, width: 1),
              ),
            ),
            child: SafeArea(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (_replyToCommentId != null) ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.blue[50],
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.blue[200]!),
                      ),
                      child: Row(
                        children: [
                          Icon(Icons.reply, size: 18, color: Colors.blue[600]), 
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Replying to comment',
                              style: TextStyle(
                                fontSize: 15, 
                                color: Colors.blue[700],
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ),
                          GestureDetector(
                            onTap: () => setState(() { _replyToCommentId = null; }),
                            child: Icon(Icons.close, size: 18, color: Colors.blue[600]), 
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                  ],
                  Row(
                    children: [
                      Expanded(
                        child: Container(
                          decoration: BoxDecoration(
                            color: Colors.grey[100],
                            borderRadius: BorderRadius.circular(24),
                          ),
                          child: TextField(
                            controller: _commentController,
                            decoration: const InputDecoration(
                              hintText: 'Write a comment...',
                              hintStyle: TextStyle(fontSize: 16), 
                              border: InputBorder.none,
                              contentPadding: EdgeInsets.symmetric(horizontal: 18, vertical: 14), 
                            ),
                            style: const TextStyle(fontSize: 16), 
                            maxLines: null,
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Container(
                        decoration: BoxDecoration(
                          color: Colors.green[600],
                          borderRadius: BorderRadius.circular(24),
                        ),
                        child: IconButton(
                          icon: const Icon(Icons.send, color: Colors.white, size: 22),
                          onPressed: _addComment,
                          padding: const EdgeInsets.all(12), 
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _commentController.dispose();
    super.dispose();
  }
}