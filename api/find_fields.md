---
layout: default
title: find_fields
---

## Find fields based on group ID, post ID, etc.

{% highlight php %}
<?php CFS()->find_fields( $params ); ?>
{% endhighlight %}

`$params` defaults:

{% highlight php %}
<?php
$params = array(
    'post_id' => false, // (int) single post ID
    'group_id' => array(), // (int) group ID, or (array) group IDs
    'field_id' => array(), // (int) field ID, or (array) field IDs
    'field_type' => array(), // (string) field type, or (array) field types
    'field_name' => array(), // (string) field name, or (array) field names
    'parent_id' => array() // (int) group ID, or (array) group IDs
);
{% endhighlight %}

### Examples

Find all fields within a specific field group

{% highlight php %}
<?php
$fields = CFS()->find_fields( array( 'group_id' => 123 ) );
{% endhighlight %}

Find all fields with a specific field type

{% highlight php %}
<?php
$fields = CFS()->find_fields( array( 'field_type' => 'relationship' ) );
{% endhighlight %}

Find all text or textarea fields within a specific field group

{% highlight php %}
<?php
$fields = CFS()->find_fields( array(
  'field_type' => array( 'text', 'textarea' ),
  'group_id' => 123,
) );
{% endhighlight %}
