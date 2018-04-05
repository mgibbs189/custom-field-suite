=== Custom Field Suite ===
Contributors: mgibbs189
Tags: custom fields, fields, postmeta, relationship, repeater, file upload
Requires at least: 4.7
Tested up to: 4.9.5
Stable tag: trunk
License: GPLv2

Add custom fields to your post types

== Description ==

Custom Field Suite (CFS) lets you add custom fields to your posts. It's lightweight and battle-tested (there's not much to break).

= Things to know =
* We do not provide support.
* This is a free plugin. We're not selling anything.
* CFS includes 14 [field types](http://customfieldsuite.com/field-types.html). There are no plans to add more.
* If you want all the bells-and-whistles, use ACF.

= Field types =
* Text
* Textarea
* WYSIWYG
* Date
* Color
* True / False
* Select
* File Upload
* Relationship
* Term
* User
* Loop (repeatable fields)
* Hyperlink
* Tab (group fields)

= Usage =
* Browse to the "Field Groups" admin menu
* Create a Field Group containing one or more custom fields
* Choose where the Field Group should appear, using the Placement Rules box
* Use the [get](http://customfieldsuite.com/api/get.html) method in your template files to display custom fields

= Links =
* [Documentation →](http://customfieldsuite.com)
* [Github →](https://github.com/mgibbs189/custom-field-suite)

== Changelog ==

= 2.5.12 =
* Fix: WP 4.9+ TinyMCE javascript issue

= 2.5.11 =
* Tested against WP 4.9
* Updated jquery-powertip library

= 2.5.10 =
* Improved: notes field is now multi-line
* Fix: ensure that $rules and $extras are arrays