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
  bool _tokenLoaded = false;

  @override
  void initState() {
    super.initState();
    _initTokenAndFetch();
  }

  Future<void> _initTokenAndFetch() async {
    _token = await AuthStorage().getToken();
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
    if (_token == null) return;
    await Navigator.of(context).push(MaterialPageRoute(
      builder: (context) => PostDetailsView(postId: postId, token: _token!),
    ));
    _fetchPosts();
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
      return Center(child: Text(_error!));
    }
    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text('Community', style: TextStyle(
              fontFamily: Config.primaryFont,
              fontSize: Config.fontMedium,
            ),),
            ElevatedButton(
              onPressed: () => _openCreatePost(),
              style: ElevatedButton.styleFrom(
                backgroundColor: Config.primaryColor,
                foregroundColor: Colors.white
              ),
              child: Text('Create Post', style: TextStyle(
                fontFamily: Config.primaryFont,
                fontSize: Config.fontSmall
              ),)
            )
          ],
        ),
        Config.heightMedium,
        Expanded(
          child: ListView.builder(
            itemCount: _posts.length,
            itemBuilder: (context, index) {
              final post = _posts[index];
              final user = post['user'] ?? {};
              final images = post['images'] as List<dynamic>? ?? [];
              return Card(
                color: Colors.white,
                margin: EdgeInsets.symmetric(vertical: 5),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Padding(
                      padding: EdgeInsets.all(20),
                      child: Row(
                        children: [
                          CircleAvatar(
                            radius: 35,
                            backgroundImage: user['image_url'] != null ? 
                              NetworkImage(user['image_url']) : 
                              null,
                          ),
                          SizedBox(width: 15),
                          Text('${user['first_name'] ?? ''} ${user['last_name'] ?? ''}', style: 
                            TextStyle(fontFamily: Config.primaryFont, fontSize: Config.fontBig, color: Config.color524F4F)),
                        ],
                      ),
                    ),
                    SizedBox(height: 5),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: 20),
                      child: Text(post['content'] ?? '', style: TextStyle(
                        fontFamily: Config.primaryFont,
                        fontSize: Config.fontMedium,
                        color: Config.tertiaryColor
                      )),
                    ),

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
                        IconButton(
                          icon: Icon(Icons.thumb_up_alt_outlined),
                          onPressed: _token == null ? null : () => _likePost(post['id']),
                        ),
                        Text('${post['likes_count'] ?? 0}'),
                        SizedBox(width: 16),
                        Icon(Icons.comment_outlined),
                        Text('${post['comments_count'] ?? 0}'),
                      ],
                    ),
                    Divider(),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        child: Text('Comments'),
                        onPressed: () => _openPostDetails(post['id']),
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}
