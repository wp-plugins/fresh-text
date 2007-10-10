=== Plugin Name ===
Contributors: freshout
Donate link:
Tags: posts, pages
Requires at least: 2.0.2
Tested up to: 2.1
Stable tag: .1

Add classes to your html tags and the innerHTML is then automatically parsed into images
with specified size, font, and color.

== Description ==

After installing, tags with class 'freshout' will have the words changed into
.png files displaying the same word in a specified font and size.  The title
attribute is hijacked for parameters. 

<span class="freshout" title="SIZE,FONT,COLOR">
	This text becomes images.
</span>

For example:

<span class="freshout" title="14,masexy,FF77EE">
	This is sexy text.
</span>

== Installation ==

1. Upload fresh_text folder to the `/wp-content/plugins/` directory
1. Edit replacement.js to use your path to wordpress (line 10)
1. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

= How do I add new fonts? =

Upload the font file to the fonts directory.  ie,
wp-content/plugins/fresh_text/fonts/my_font.ttf

= How do I change the class name that gets parsed =

Just change line 5 of replacement.php from

	var class_to_replace = "freshout";
to
	var class_to_replace = "some_other_name";

= Your plugin sucks. =

That's not what your mom said last night.


== Thanks ==

Stewart Rosenberger
http://www.stewartspeak.com/headings/
For the original text replacement code.


