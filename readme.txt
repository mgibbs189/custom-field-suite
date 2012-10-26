=== Custom Field Suite ===
Contributors: logikal16
Donate link: https://uproot.us/contributors/
Tags: custom fields, custom field, fields, post meta, cck, wysiwyg, relationship, loop, file upload
Requires at least: 3.2
Tested up to: 3.5
Stable tag: trunk

Really simple custom field management.

== Description ==

Create groups of custom fields, then choose which edit screens to appear on. Each field group has its own meta box, allowing for plenty of customization. CFS includes a lightweight API for displaying custom fields throughout your site.

**CFS is a fork of Advanced Custom Fields.** The main goals of this plugin are stability, performance, and avoiding feature bloat.

= Why use Custom Field Suite? =
* CFS is easy to use. You and your clients will LOVE it!
* CFS is stable. We test all changes before releasing a new version.
* CFS is fast and uses minimal server resources.
* CFS has [full documentation](https://uproot.us/custom-field-suite/documentation/) and [support forums](https://uproot.us/forums/).
* CFS works well with [Gravity Forms](https://uproot.us/how-to-save-gravity-forms-data-into-custom-field-suite/). It can save GF entries as post items.
* CFS supports [adding your own field types](http://uproot.us/custom-field-suite/docs/custom-field-type/).
* [We're on GitHub!](https://github.com/logikal16/custom-field-suite/)

= Field Types =
* [Text](https://uproot.us/docs/text/)
* [Textarea](https://uproot.us/docs/textarea/)
* [Wysiwyg Editor](https://uproot.us/docs/wysiwyg/)
* [Date](https://uproot.us/docs/date/)
* [Color](https://uproot.us/docs/color/)
* [True / False](https://uproot.us/docs/true-false/)
* [Select](https://uproot.us/docs/select/)
* [File Upload](https://uproot.us/docs/file-upload/)
* [User](https://uproot.us/docs/user/)
* [Relationship](https://uproot.us/docs/relationship/)
* [Loop](https://uproot.us/docs/loop/)

= More Features =
* Field validation
* Drag-and-drop field management
* Loop fields support unlimited nesting!
* Placement Rules let you choose where each field group appears
* Sync feature for importing meta values into existing field groups

= Documentation and Support =
* http://uproot.us/
* http://uproot.us/custom-field-suite/documentation/
* https://uproot.us/forums/

== Installation ==

1. Download and activate the plugin.
2. Browse to the `Field Groups` menu to configure.

== Screenshots ==
1. A custom field group with field nesting (loop field)
2. Clicking on a field name expands the box to show options
3. Placement Rules determine where field groups appear
4. The Tools area for migrating field groups

== Changelog ==

= 1.7.5 =
* Updated German translations (props Sacha Brosi)
* Added hook: cfs_custom_validation
* Hovering over a relationship item will display its post type
* Updated select2 script (placement rules area)
* Refactored ajax handling

= 1.7.4 =
* Improved upgrade script
* Added German translation (props Sascha Brosi)
* Bugfix: javascript issues for fields within sub-loop fields (props @sc0ttkclark)
* Added tooltips to field management page

= 1.7.3 =
* Added field validation
* Bugfix: get_post_type caching causing Gravity Form save error (props producerism)
* Added Hungarian translation (props József Szijártó)

= 1.7.2 =
* Added Media button to WYSIWYG fields
* Bugfix: [fatal error when a post update causes a rules mismatch](https://github.com/logikal16/custom-field-suite/issues/55) (props Gator92)
* Tools page UI improvements

= 1.7.1 =
* Placement rules clarification text
* Allow editing of fields without labels
* Added new "Row Label" Loop option
* Bugfix: nested loop fields (props Hylkep)
* Updated timepicker JS

= 1.7.0 =
* Improved: field management UI
* Improved: select field returns associative array (value, label)
* Improved: rename postmeta keys when fields are renamed
* Improved: delete values when a field is deleted
* Bugfix: clear cache on $cfs->save (props dataworx)
* File field compatibility fixes for WP 3.5
* Added new logo (https://github.com/somerandomdude/Iconic)

[See the full changelog](https://uproot.us/custom-field-suite/changelog/)