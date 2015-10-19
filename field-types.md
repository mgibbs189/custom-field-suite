---
layout: default
title: Field Types
---

## Text
Generates a single-line text field

| Option | Purpose |
|--------|---------|
| Default Value | An initial value, which appears before the first save |

Returns: (string) The inputted text

## Textarea
Generates a multi-line text field

| Option | Purpose |
|--------|---------|
| Default Value | An initial value, which appears before the first save |
| Formatting | Whether or not to automatically add newlines to the return value |

Returns: (string) The inputted text

## WYSIWYG Editor
Generates a visual editor field

| Option | Purpose |
|--------|---------|
| Formatting | Whether to pass the value through WP's the_content filter ("Default" or "None") |

Returns: (string) The formatted HTML content

## Date
Generates a single-line text field with a built-in datepicker widget

Returns: (string) The date, in `YYYY-MM-DD HH:MM:SS` format

## Color
Generates a text field with a built-in color picker widget

Returns: (string) A HEX color value

## True / False
Generates a single checkbox, with optional descriptive text beside it

| Option | Purpose |
|--------|---------|
| Message | (optional) Descriptive text beside the checkbox |

Returns: (int) 1 or 0

## Select
Generates either a single select dropdown, or a multi-select input field

| Option | Purpose |
|--------|---------|
| Choices | A list of dropdown options, one per line. For each line, you can optionally use `value : label`. Use `{empty}` for an empty value. |
| Multi-select? | Allow for the selection of multiple values |

Returns: (array) An array of selected values (or an empty array)

## File Upload
Generates a file upload field, using the native WordPress uploader

| Option | Purpose |
|--------|---------|
| Return Value | Return either the file URL (default) or attachment ID |

Returns: (mixed) Either the file URL or attachment ID

## User
Generates a widget for selecting users. It includes drag-n-drop ordering capabilities.

Returns: (array) An array of user IDs

## Relationship
Generates a widget for selecting other post type items. It includes drag-n-drop ordering capabilities.

| Option | Purpose |
|--------|---------|
| Post Types | (optional) Limit the list to the chosen post types |
| Limits | (optional) Require a min and/or max number of selected items |

Returns: (array) An array of post IDs

## Loop
A loop field lets you create repeatable fields. E.g. a file upload field could be placed into a loop to create a gallery.

| Option | Purpose |
|--------|---------|
| Row Display | Whether to expand the row values by default |
| Row Label | (optional) Override the "Loop Row" header text. You can also dynamically populate the row label with field values. If you have a text field named "first_name", enter {first_name} to use the field value as the row label. |
| Button Label | Override the "Add Row" button text |

Returns: (array) An array of associative data arrays
