=== Custom Field Suite ===
Contributors: mgibbs189
Tags: custom fields, fields, forms, meta, postmeta, metabox, wysiwyg, relationship, repeater, upload
Requires at least: 4.0
Tested up to: 4.3
Stable tag: trunk
License: GPLv2

Custom Field Suite (CFS) allows you to attach custom fields to posts types.

== Description ==

= Custom Field Suite (CFS) allows you to attach custom fields to posts types. =

CFS lets you visually create an manage custom fields. You first create a Field Group, which contains one or more custom fields.
You're able to easily add custom fields through the admin UI. There's over 12 field types to choose from, include text, date,
wysiwyg, file upload, relationship, user, loop (repeatable fields), etc.

After you've set up your field group, you simply set where the fields appear using the Placement Rules box. You can attach custom fields to
entire post types, specific post items, pages using a specific template, etc.

CFS also supports the creation of custom field types by using the `cfs_field_types` filter.

This plugin is a free, lightweight alternative to Advanced Custom Fields.

= Important Links =
* [Documentation →](http://customfieldsuite.com/projects/cfs/documentation/)
* [Github →](https://github.com/mgibbs189/custom-field-suite)

= Translations =
* Dutch (nl_NL) - thanks to [wverhoogt](https://github.com/wverhoogt)
* German (de_DE) - thanks to [David Decker](http://deckerweb.de/)
* Spanish (es_ES) - thanks to [Andrew Kurtis](http://www.webhostinghub.com/)
* Persian (fa_IR) - thanks to Vahid Masoomi
* French (fr_FR) - thanks to Jean-Christophe Brebion
* Italian (it_IT)
* Japanese (ja) - thanks to Karin Suzakura
* Polish (pl_PL) - thanks to [Digital Factory](digitalfactory.pl)
* Russian (ru_RU) - thanks to Glebcha
* Catalan (ca) - thanks to Luis Bordas

== Installation ==

1. Download and activate the plugin.
2. Browse to the `Field Groups` menu to configure.

== Screenshots ==
1. A custom field group with field nesting (loop field)
2. Clicking on a field name expands the box to show options
3. Placement Rules determine where field groups appear
4. The Tools area for migrating field groups

== Changelog ==

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

= 2.3.11 =
* Appended CFS version number to assets, to flush caches (props @chrisvanpatten)
* Added Dutch translations (props @wverhoogt)

= 2.3.10 =
* New: `cfs_relationship_display` filter
* New: `cfs_user_display` filter
* New: `cfs_save_field_group_rules` filter
* Improved loop field dynamic label support

= 2.3.9 =
* UI refresh and cleanup (props @chrisvanpatten)
* New: user field min/max validation (props @chrisvanpatten)
* New: loop field min/max validation (props @christopherdro)
* Improved Add-ons screen (incl. new "Synchronize" add-on)
* Replaced tooltip library
* Updated translations

= 2.3.8 =
* Fixed validation issue with WYSIWYG fields
* Increased toggle speed when viewing admin fields
* Cleanup of Tools and Add-ons pages
* Updated translations

= 2.3.7 =
* WYWISYG resize support
* Fixed WYSIWYG issue with all editors in text mode (props @sc0ttkclark)
* Fixed issue with partially-saved field groups

= 2.3.6 =
* Added Spanish translation (props Andrew Curtis)
* Updated translations
* Upgraded select2 to 3.5.1
* Hide Gravity Forms options when disabled
* Fixed label for "Post Formats"
* Fixed issue with Duplicate Posts plugin (props hissy)
* Added `cfs_disable_admin` to optionally hide group creation screens

= 2.3.5 =
* Fixed WYSIWYG "code" button from showing repeatedly

= 2.3.4 =
* Re-added WYSIWYG "code" button for WP 3.9+
* Date picker now highlights current day

= 2.3.3 =
* Added Hi-res select2 images
* Added Post Format placement rule (props @jchristopher)
* Fixed array_orderby method for PHP 5.3
* Updated translations

= 2.3.2 =
* Code refactoring
* Corrected Add-ons page with new URL
* Use `CFS()` instead of `$cfs` for future API usage
* Changed CFS->form init priority to 100 (for better compatibility)
* Fixed relationship fields not being scrollable (props @jchristopher)

= 2.3.1 =
* Refreshed field design (props @jchristopher)
* Removed deprecated WP 3.5 code (file upload field)
* Minor tweaks to the output of Exports

= 2.3.0 =
* Added Tab field type
* Updated translations