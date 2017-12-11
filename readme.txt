=== Custom Field Suite ===
Contributors: mgibbs189
Tags: custom fields, fields, postmeta, relationship, repeater, file upload
Requires at least: 4.4
Tested up to: 4.9.1
Stable tag: trunk
License: GPLv2

A custom fields management UI

== Description ==

= Custom Field Suite (CFS) is a visual custom fields management plugin. =

CFS includes over 12 field types, include text, date, wysiwyg, file upload, relationship, user, and loop (repeatable fields). With CFS, creating and managing custom fields is easy, thanks to our clean and intuitive admin UI.

It also features an [elegant, lightweight API](http://customfieldsuite.com/api.html) for loading (and saving) field values.

= Setting it up =
* Browse to the "Field Groups" admin menu
* Create a Field Group, containing one or more custom fields
* Choose where the Field Group should appear, using Placement Rules (see screenshots)
* Use the [get](http://customfieldsuite.com/api/get.html) method in your template files to display custom fields

This plugin is a free, lightweight alternative to Advanced Custom Fields.

= Important Links =
* [Documentation →](http://customfieldsuite.com)
* [Github →](https://github.com/mgibbs189/custom-field-suite)

= Translations =
* Brazilian Portuguese (pt_BR) - thanks to Felipe Elia
* Catalan (ca) - thanks to Luis Bordas
* Chinese (zh_CN) - thanks to iblackly
* Dutch (nl_NL) - thanks to [wverhoogt](https://github.com/wverhoogt)
* French (fr_FR) - thanks to Jean-Christophe Brebion
* German (de_DE) - thanks to [David Decker](http://deckerweb.de/)
* Italian (it_IT)
* Japanese (ja) - thanks to Karin Suzakura
* Persian (fa_IR) - thanks to Vahid Masoomi
* Polish (pl_PL) - thanks to [Digital Factory](digitalfactory.pl)
* Russian (ru_RU) - thanks to Glebcha
* Spanish (es_ES) - thanks to [Andrew Kurtis](http://www.webhostinghub.com/)
* Turkish (tr_TR) - thanks to Ertuğrul

== Installation ==

1. Download and activate the plugin.
2. Browse to the `Field Groups` menu to configure.

== Screenshots ==
1. A custom field group with field nesting (loop field)
2. Clicking on a field name expands the box to show options
3. Placement Rules determine where field groups appear
4. The Tools area for migrating field groups

== Changelog ==

= 2.5.12 =
* Fix: WP 4.9+ TinyMCE javascript issue

= 2.5.11 =
* Tested against WP 4.9
* Updated jquery-powertip library

= 2.5.10 =
* Improved: notes field is now multi-line
* Fix: ensure that $rules and $extras are arrays

= 2.5.9 =
* Ensured WP 4.7.4 compat
* Improved: Removed ancient code - replace `$cfs` with `CFS()`
* Improved: Admin UI tweaks
* Improved: Better validation error handling (props @chrisvanpatten)

= 2.5.8 =
* New: "Page Hierarchy" placement rule
* New: Term field type
* Improved: smarter field name generation (supports accent characters)
* Fix: graceful error handling for fields with unrecognized field types

= 2.5.7 =
* New: ability to insert loop rows inline (props @chrisvanpatten)
* New: Brazilian Portuguese translations (props Felipe Elia)
* Fix: colliding text when field names are excessively long
* Updated documentation URLs

= 2.5.6.1 =
* Tested against WP 4.6

= 2.5.6 =
* Improved: loop field accessibility improvements (props @chrisvanpatten)
* Improved: set default value for Color fields
* Fix: "Hide the content editor" box (props @jchristopher)
* Fix: do not save custom fields during Preview
* Fix: set minimum select2 character limit to 2
* Fix: ensure that post exists in `get_field` method

= 2.5.5 =
* Tested against WP 4.5
* Select field type now supports Select2 (props @chrisvanpatten)
* True / false fields use basic HTML elements (props @chrisvanpatten)
* Added Hungarian translation (props @ersoma)
* Upgraded to Select2 3.5.4
* Minor UX tweaks

= 2.5.4 =
* New: `cfs_form_before_fields` filter
* New: `cfs_form_after_fields` filter
* Fix: allow tabs in separate field groups to be active simultaneously

= 2.5.3 =
* Fix: issues with Loop field "dynamic label" functionality
* Changed: "Field Groups" menu moved below "Settings" in the admin UI

= 2.5.2 =
* New: support for dynamic Loop labels from select field values (props @superbiche)
* Fix: PHP notice for "Placement" column

= 2.5.1 =
* Fix: issue with $cfs variable

= 2.5 =
* New: [find_fields API method](http://customfieldsuite.com/api/find_fields.html)
* New: `CFS()->field_group->load_field_groups()` method
* New: Turkish transation (props Ertuğrul)
* New: Chinese translation (props iblackly)
* Improved: [documentation overhaul](http://customfieldsuite.com)
* Improved: major code cleanup and refactoring
* Improved: efficiency improvements for internal API methods
* Improved: removed deprecated (< 3.5) code from File field type
* Improved: show post titles instead of IDs for "Placement" admin column
* Improved: now using a built-in WP dashicon
* Changed: removed buggy Gravity Forms integration
* Changed: removed `cfs_relationship_post_types` filter (use `cfs_field_relationship_query_args` instead)
* Changed: removed add-ons screen (for now)
* Changed: toggle icon for loop fields
* Fix: field validation for loop sub-fields
* Fix: CFS()->save gracefully handles missing fields (no errors)
* Fix: wysiwyg fields would break when dragged
* Fix: Remove non-existing post IDs from "Posts" placement rule
* Updated translations
