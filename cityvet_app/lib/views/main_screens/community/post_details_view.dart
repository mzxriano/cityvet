import 'package:cityvet_app/utils/config.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:cityvet_app/services/community_service.dart';

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

  @override
  void initState() {
    super.initState();
    _fetchPostAndComments();
  }

  Future<void> _fetchPostAndComments() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final postResp = await _service.dio.get('/community/${widget.postId}', options: Options(headers: {'Authorization': 'Bearer ${widget.token}'}));
      final commentsResp = await _service.fetchComments(widget.postId, widget.token);
      setState(() {
        _post = postResp.data;
        _comments = commentsResp.data;
        _isLoading = false;
      });
    } catch (e) {
      setState(() { _error = 'Failed to load post.'; _isLoading = false; });
    }
  }

  Future<void> _addComment() async {
    final content = _commentController.text.trim();
    if (content.isEmpty) return;
    try {
      await _service.addComment(
        postId: widget.postId,
        content: content,
        parentId: _replyToCommentId?.toString(),
        token: widget.token,
      );
      _commentController.clear();
      setState(() { _replyToCommentId = null; });
      _fetchPostAndComments();
    } catch (e) {
      // Optionally show error
    }
  }

  Widget _buildComment(Map<String, dynamic> comment, {int indent = 0}) {
    final user = comment['user'] ?? {};
    final children = comment['children'] as List<dynamic>? ?? [];
    return Padding(
      padding: EdgeInsets.only(left: 16.0 * indent, top: 8, bottom: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 30,
                backgroundImage: user['image_url'] != null ? 
                  NetworkImage(user['image_url']) : 
                  null,
              ),
              SizedBox(width: 15),
              Text('${user['first_name'] ?? ''} ${user['last_name'] ?? ''}', style: 
                TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium, color: Config.color524F4F)),
              SizedBox(width: 8),
              TextButton(
                child: Text('Reply', style: TextStyle(fontSize: 12)),
                onPressed: () {
                  setState(() { _replyToCommentId = comment['id']; });
                  FocusScope.of(context).requestFocus(FocusNode());
                },
              ),
            ],
          ),
          Config.heightSmall,
          Text(comment['content'] ?? '', style: TextStyle(fontSize: Config.fontMedium, fontFamily: Config.primaryFont, color: Config.tertiaryColor)),
          ...children.map<Widget>((child) => _buildComment(child, indent: indent + 1)).toList(),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) return Center(child: CircularProgressIndicator());
    if (_error != null) return Center(child: Text(_error!));
    final post = _post ?? {};
    final user = post['user'] ?? {};
    final images = post['images'] as List<dynamic>? ?? [];
    return Scaffold(
      appBar: AppBar(title: Text('Post Details')),
      body: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  CircleAvatar(
                    radius: 20,
                    backgroundImage: user['image_url'] != null ? 
                      NetworkImage(user['image_url']) : 
                      null,
                  ),
                  SizedBox(width: 20),
                  Text('${user['first_name'] ?? ''} ${user['last_name'] ?? ''}', style: 
                    TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontMedium, color: Config.color524F4F)),
                ],
              ),
              SizedBox(height: 20),
              Text(post['content'] ?? '', style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontMedium,
                color: Config.tertiaryColor
              ),),
              if (images.isNotEmpty) ...[
                SizedBox(height: 8),
                SizedBox(
                  height: 120,
                  child: ListView.separated(
                    scrollDirection: Axis.horizontal,
                    itemCount: images.length,
                    separatorBuilder: (_, __) => SizedBox(width: 8),
                    itemBuilder: (context, imgIdx) {
                      final img = images[imgIdx];
                      return Image.network(img['image_url'], width: 120, height: 120, fit: BoxFit.cover);
                    },
                  ),
                ),
              ],
              SizedBox(height: 8),
              Row(
                children: [
                  Icon(Icons.thumb_up_alt_outlined),
                  Text('${post['likes_count'] ?? 0}'),
                  SizedBox(width: 16),
                  Icon(Icons.comment_outlined),
                  Text('${post['comments_count'] ?? 0}'),
                ],
              ),
              Divider(height: 32),
              Text('Comments', style: TextStyle(fontWeight: FontWeight.bold, fontSize: Config.fontMedium)),
              ..._comments.map<Widget>((c) => _buildComment(c)).toList(),
              SizedBox(height: 16),
              if (_replyToCommentId != null)
                Text('Replying to comment #$_replyToCommentId', style: TextStyle(fontSize: 12, color: Colors.blue)),
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _commentController,
                      decoration: InputDecoration(hintText: 'Add a comment...'),
                    ),
                  ),
                  IconButton(
                    icon: Icon(Icons.send),
                    onPressed: _addComment,
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
} 