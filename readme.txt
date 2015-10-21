=== Custom Field Suite ===
Contributors: mgibbs189
Tags: custom fields, fields, forms, meta, postmeta, metabox, wysiwyg, relationship, repeater, upload
Requires at least: 4.0
Tested up to: 4.3.1
Stable tag: trunk
License: GPLv2

A custom fields management UI

== Description ==

= Custom Field Suite (CFS) is a visual custom fields management plugin. =

CFS includes over 12 field types, include text, date, wysiwyg, file upload, relationship, user, and loop (repeatable fields). With CFS, creating and managing custom fields is easy, thanks to our clean and intuitive admin UI.

It also features an [elegant, lightweight API](http://docs.customfieldsuite.com/api.html) for loading (and saving) field values.

= Setting it up =
* Browse to the "Field Groups" admin menu
* Create a Field Group, containing one or more custom fields
* Choose where the Field Group should appear, using Placement Rules (see screenshots)
* Use the [get](http://docs.customfieldsuite.com/api/get.html) method in your template files to display custom fields

This plugin is a free, lightweight alternative to Advanced Custom Fields.

= Important Links =
* [Documentation →](http://docs.customfieldsuite.com)
* [Github →](https://github.com/mgibbs189/custom-field-suite)

= Translations =
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

= 2.5.1 =
* Fix: issue with $cfs variable

= 2.5 =
* New: [find_fields API method](http://docs.customfieldsuite.com/api/find_fields.html)
* New: `CFS()->field_group->load_field_groups()` method
* New: Turkish transation (props Ertuğrul)
* New: Chinese translation (props iblackly)
* Improved: [documentation overhaul](http://docs.customfieldsuite.com)
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

= 2.4.5 =
* Fix: WP 4.3 `wp_richedit_pre` deprecated notice (props @jchristopher)
* Fix: MySQL error when field IDs is empty (props @hubeRsen)
* Fix: CFS no longer forces editor into "Visual" mode
* Bumped minimum version to WP 4.0

= 2.4.4 =
* Added Catalan translation (props Luis Bordas)
* Updated translations

= 2.4.3 =
* Prevent hyperlink field with target="none" from opening new tab (props @jchristopher)
* Enable media filtering of file fields (props @camiloclc)
* Updated jQuery minicolors to fix deselect issue
* Updated translations (props @jcbrebion)

= 2.4.2 =
* New CFS logo (props @chrisvanpatten)
* Added composer.json file

= 2.4.1 =
* Security fix: ensure that only admins can import field groups (props James Golovich)

= 2.4 =
* Added Hyperlink field type (previously an add-on)
* Added Revision support, just use `DEFINE( 'CFS_REVISIONS', true );`
* Added license.txt (props @chrisvanpatten)