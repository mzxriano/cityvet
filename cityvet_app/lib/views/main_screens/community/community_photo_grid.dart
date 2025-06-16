import 'dart:math';

import 'package:flutter/material.dart';

class CommunityPhotoGrid extends StatefulWidget {
    final int maxImages;
    final List<String> imageUrls;
    final Function(int) onImageClicked;
    final Function onExpandClicked;

    const CommunityPhotoGrid({
      super.key,
      required this.maxImages,
      required this.imageUrls,
      required this.onImageClicked,
      required this.onExpandClicked,
    });

  @override
  createState() => _CommunityPhotoGridState();
}

class _CommunityPhotoGridState extends State<CommunityPhotoGrid> {
  @override
  Widget build(BuildContext context) {
    var images = buildImages();

    return GridView(
      shrinkWrap: true,
      physics: NeverScrollableScrollPhysics(),
      gridDelegate: SliverGridDelegateWithMaxCrossAxisExtent(
        maxCrossAxisExtent: 200,
        crossAxisSpacing: 2,
        mainAxisSpacing: 2,
      ),
      children: images,
    );
  }

  List<Widget> buildImages() {
    int numImages = widget.imageUrls.length;
    return List<Widget>.generate(min(numImages, widget.maxImages), (index) {
      String imageUrl = widget.imageUrls[index];

      
      if (index == widget.maxImages - 1) {
        // Check how many more images are left
        int remaining = numImages - widget.maxImages;

       
        if (remaining == 0) {
          return GestureDetector(
            child: Image.asset(
              imageUrl,
              fit: BoxFit.cover,
            ),
            onTap: () => widget.onImageClicked(index),
          );
        } else {
         
          return GestureDetector(
            onTap: () => widget.onExpandClicked(),
            child: Stack(
              fit: StackFit.expand,
              children: [
                Image.asset(imageUrl, fit: BoxFit.cover),
                Positioned.fill(
                  child: Container(
                    alignment: Alignment.center,
                    color: Colors.black54,
                    child: Text(
                      '+' + remaining.toString(),
                      style: TextStyle(
                        fontSize: 32,
                        color: Colors.white
                      ),
                    ),
                  ),
                ),
              ],
            ),
          );
        }
      } else {
        return GestureDetector(
          child: Image.asset(
            imageUrl,
            fit: BoxFit.cover,
          ),
          onTap: () => widget.onImageClicked(index),
        );
      }
    });
  }
}