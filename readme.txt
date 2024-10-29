=== Advanced Media Downloader ===
Contributors: ceramedia
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=G4742MW6WF4B6
Tags: download, media, resize, image, crop, blur, combine, javascript, less, css
Tested up to: 3.5.1
Stable tag: 1.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The plugin makes it possible to download files from the media library and resize, crop, mask, blur it on the fly.

== Description ==

The plugin makes it possible to download files from the media library and resize, crop, mask, blur it on the fly. It also has a nice option to download all your less, javascript or css files in a combined file.

After installation you can download all files from the media library by the following uri:
/download/file/[post_id]

To resize or filter this image add one or more of the following get variables:

*   width         int     Width of the image
*   height        int     Height of the image
*   crop          bool    By default proportions are maintained, crop cuts off the overflow
*   refpoint      string  Optional values are nw|n|ne|e|se|s|sw|w, sets the point from where to resize
*   mask          bool    Creates a mask instead of resizing (requires crop to be set)
*   blur          int     Adds gaussian blur to the image, 0-50 for intensity. Note more intensity requires more cpu / memory
*   forceratio    bool    Forces to resize in the given ratio, even when there are not enough pixels
*   save          bool    Force download as attachment to the browser

Example: /download/file/[post_id]?width=320&height=240&crop=true&refpoint=n

**Javascript, Less, CSS**

These can be downloaded with one of the following uri's:

*   /download/less/master (probably best to just use @import in less case)
*   /download/js/mootools/mootools-core-1.4.5+mootools/mootools-more-1.4.0.1-assets+modernizr/modernizr-latest
*   /download/css/960/960+style

Explanation (will break down the javascript example):

1. First part "/download/js/" refers to the folder js within the theme folder.
1. Next part "mootools/mootools-core-1.4.5" is a relative path from that js folder. Note that the extension will be added for you. This is to make sure only javascript files are combined.
1. To add more files append as much as needed with the + sign. Each file will be loaded relatively from the js folder.

Structure from js example is:

[themefolder]/

>js/

>	>mootools/

>	>	>mootools-core-1.4.5.js

>	>	>mootools-more-1.4.0.1-assets.js

>	>modernizr/

>	>	>modernizr-latest.js.

You can use amd_ prefixed functions to generate links or/and img tags.

== Installation ==

1. Upload the folder `advanced-media-downloader` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Check out the description for usage options