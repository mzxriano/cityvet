import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:cityvet_app/services/community_service.dart';

class CreatePostView extends StatefulWidget {
  final String token; // Pass the user's auth token
  const CreatePostView({Key? key, required this.token}) : super(key: key);

  @override
  State<CreatePostView> createState() => _CreatePostViewState();
}

class _CreatePostViewState extends State<CreatePostView> {
  final TextEditingController _contentController = TextEditingController();
  final List<File> _images = [];
  bool _isLoading = false;
  String? _error;

  Future<void> _pickImages() async {
    final picker = ImagePicker();
    final picked = await picker.pickMultiImage();
    if (picked != null) {
      setState(() {
        _images.addAll(picked.map((x) => File(x.path)));
      });
    }
  }

  Future<void> _submitPost() async {
    final content = _contentController.text.trim();
    if (content.isEmpty) {
      setState(() { _error = 'Please enter some text.'; });
      return;
    }
    setState(() { _isLoading = true; _error = null; });
    try {
      await CommunityService().createPost(
        content: content,
        images: _images,
        token: widget.token,
      );
      Navigator.of(context).pop(true); // Indicate success
    } catch (e) {
      print('Failed to create posts $e');
      setState(() { _error = 'Failed to create post.'; });
    } finally {
      setState(() { _isLoading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Create Post')),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            TextField(
              controller: _contentController,
              maxLines: 4,
              decoration: InputDecoration(
                labelText: 'What do you want to share?',
                border: OutlineInputBorder(),
              ),
            ),
            SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                ..._images.map((img) => Stack(
                  children: [
                    Image.file(img, width: 80, height: 80, fit: BoxFit.cover),
                    Positioned(
                      right: 0,
                      top: 0,
                      child: GestureDetector(
                        onTap: () {
                          setState(() { _images.remove(img); });
                        },
                        child: Container(
                          color: Colors.black54,
                          child: Icon(Icons.close, color: Colors.white, size: 18),
                        ),
                      ),
                    ),
                  ],
                )),
                GestureDetector(
                  onTap: _pickImages,
                  child: Container(
                    width: 80,
                    height: 80,
                    color: Colors.grey[300],
                    child: Icon(Icons.add_a_photo),
                  ),
                ),
              ],
            ),
            SizedBox(height: 16),
            if (_error != null) ...[
              Text(_error!, style: TextStyle(color: Colors.red)),
              SizedBox(height: 8),
            ],
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _submitPost,
                child: _isLoading ? CircularProgressIndicator() : Text('Post'),
              ),
            ),
          ],
        ),
      ),
    );
  }
} 