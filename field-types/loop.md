---
layout: default
title: Loop field
---

## Loop

A loop field lets you create repeatable fields. E.g. a file upload field could be placed into a loop to create a gallery.

To use it, first create a Loop field. **After saving**, drag other fields directly below the Loop field. You will notice that the fields will appear "nested". Save, then you're ready to go!

### Field Options

| Option | Purpose |
|--------|---------|
| Row Display | Whether to expand the row values by default |
| Row Label | (optional) Override the "Loop Row" header text. You can also dynamically populate the row label with field values. If you have a text field named "first_name", enter `{first_name}` to use the field value as the row label. |
| Button Label | (optional) Override the "Add Row" button text |

### Return Value

(array) An array of value arrays

### Template Usage

{% highlight php %}
<?php
/*
    A loop field named "gallery" with sub-fields "slide_title" and "upload"
    Loop fields return an associative array containing *ALL* sub-fields and their values
    NOTE: Values of sub-loop fields are returned when using get() on the parent loop!
*/
$fields = CFS()->get( 'gallery' );
foreach ( $fields as $field ) {
    echo $field['slide_title'];
    echo $field['upload'];
}
{% endhighlight %}
