---
layout: default
title: API
---

## Get field values

{% highlight php %}
<?php CFS()->get( $field_name, $post_id, $options ); ?>
{% endhighlight %}

| Parameter  | Required  | Type  | Notes  |
|---|---|---|---|
| $field_name  | N  | mixed  | Enter a field name, or FALSE to get all fields  |
| $post_id  | N   | mixed  | Enter a post ID, or FALSE to use the current post ID  |
| $options  | N  | array  | `$options['format']` can be 'api', 'input', or 'raw'  |

Examples:

{% highlight php %}
<?php

// Output a field value
echo CFS()->get( 'first_name' );


// Store all field values for the current post
$fields = CFS()->get();


// Output a field from a specific post ID
echo CFS()->get( 'first_name', 678 );


// Retrieve the raw, unformatted values for post with ID = 42
$field_data = CFS()->get( false, 42, array( 'format' => 'raw' ) );


// Get values from within a loop
$loop = CFS()->get( 'gallery' );
foreach ( $loop as $row ) {
    echo $row['gallery_title'];
    echo $row['gallery_image'];
}
{% endhighlight %}

## Get field properties

{% highlight php %}
<?php CFS()->get_field_info( $field_name, $post_id ); ?>
{% endhighlight %}

Examples:

{% highlight php %}
<?php

// Get a field label
$props = CFS()->get_field_info( 'first_name' );
echo $props['label'];


// Get a field label (alternate)
$props = CFS()->get_field_info();
echo $props['first_name']['label'];


// Get all field properties (as an associative array)
$props = CFS()->get_field_info();


// Output all select field choices
$props = CFS()->get_field_info( 'my_select' );
$choices = $props['options']['choices']; // an array of choices
foreach ( $choices as $value => $label ) {
    echo '<div>' . $value . ' = ' . $label . '</div>';
}
{% endhighlight %}

## Find fields based on group ID, post ID, etc.

## CFS()->get_reverse_related()

## CFS()->save()

## CFS()->form()
