---
layout: default
title: get_field_info
---

## Get field properties

{% highlight php %}
<?php CFS()->get_field_info( $field_name, $post_id ); ?>
{% endhighlight %}

| Parameter | Required | Type | Notes |
|-----------|----------|------|-------|
| $field_name | N | mixed | Enter a field name, or FALSE for all fields |
| $post_id | N | mixed | Enter a post ID, or FALSE to use the current post ID |

### Examples

Get a field label

{% highlight php %}
<?php
$props = CFS()->get_field_info( 'first_name' );
echo $props['label'];
{% endhighlight %}

Get a field label (alternate)

{% highlight php %}
<?php
$props = CFS()->get_field_info();
echo $props['first_name']['label'];
{% endhighlight %}

Get all field properties (as an associative array)

{% highlight php %}
<?php
$props = CFS()->get_field_info();
{% endhighlight %}

Output all select field choices

{% highlight php %}
<?php
$props = CFS()->get_field_info( 'my_select' );
$choices = $props['options']['choices']; // an array of choices
foreach ( $choices as $value => $label ) {
    echo '<div>' . $value . ' = ' . $label . '</div>';
}
{% endhighlight %}
