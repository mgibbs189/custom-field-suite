=== Custom Field Suite ===
Contributors: mgibbs189
Tags: custom fields, fields, postmeta, relationship, repeater, file upload
Requires at least: 4.9
Tested up to: 5.6
Stable tag: trunk
License: GPLv2

Add custom fields to your post types

== Description ==

Custom Field Suite (CFS) lets you add custom fields to your posts. It's lightweight and battle-tested (there's not much to break).

= Things to know =
* We do not provide support.
* This is a free plugin. We're not selling anything.
* CFS includes 14 [field types](https://mgibbs189.github.io/custom-field-suite/field-types.html). There are no plans to add more.
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
* Use the [get](https://mgibbs189.github.io/custom-field-suite/api/get.html) method in your template files to display custom fields

= Links =
* [Documentation →](https://mgibbs189.github.io/custom-field-suite/)
* [Github →](https://github.com/mgibbs189/custom-field-suite)

== Changelog ==

= 2.6 =
New: moved CFS into "Settings" menu
Improved: relationship fields now only run 1 query to retrieve related posts
Improved: code modernization
Improved: styling tweaks
Fix: "Posts" field group rule ajax wasn't loading
