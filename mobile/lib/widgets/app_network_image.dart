import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';

/// Network image tuned for Flutter Web (Chrome): prefers an HTML img element on web to avoid
/// cross-origin canvas/CORS issues when loading images from another domain.
Widget appNetworkImage(
  String url, {
  double? width,
  double? height,
  BoxFit fit = BoxFit.cover,
  Widget Function(BuildContext, Object, StackTrace?)? errorBuilder,
  ImageLoadingBuilder? loadingBuilder,
}) {
  return Image.network(
    url,
    width: width,
    height: height,
    fit: fit,
    webHtmlElementStrategy: kIsWeb ? WebHtmlElementStrategy.prefer : WebHtmlElementStrategy.never,
    loadingBuilder: loadingBuilder,
    errorBuilder: errorBuilder ??
        (context, error, stackTrace) => Container(
              width: width,
              height: height,
              color: Colors.grey.shade300,
              child: Icon(Icons.broken_image_outlined, size: 40, color: Colors.grey.shade500),
            ),
  );
}
