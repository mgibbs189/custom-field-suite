---
layout: default
title: get_field_info()
---

## Get field properties

{% highlight php %}
<?php CFS()->get_field_info( $field_name, $post_id ); ?>
{% endhighlight %}

| Parameter | Required | Type | Notes |
|-----------|----------|------|-------|
| $field_name | N | mixed | Enter a field name, or FALSE for all fields |
| $post_id | N | mixed | Enter a post ID, or FALSE to use the current post ID |

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
