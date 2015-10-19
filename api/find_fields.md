---
layout: default
title: find_fields()
---

## Find fields based on group ID, post ID, etc.

{% highlight php %}
<?php CFS()->find_fields( $params ); ?>
{% endhighlight %}

| Parameter | Required | Type | Notes |
|-----------|----------|------|-------|
| $params['post_id'] | N | mixed | A post ID to filter matches |
| $params['group_id'] | N | mixed | (int) group ID, or (array) group IDs |
| $params['field_id'] | N | mixed | (int) field ID, or (array) field IDs |
| $params['field_type'] | N | mixed | (string) field type, or (array) field types |
| $params['field_name'] | N | mixed | (string) field name, or (array) field names |
| $params['parent_id'] | N | mixed | (int) parent ID, or (array) parent IDs |

Examples:

{% highlight php %}
<?php

// Find all fields within a specific field group (ID = 123)
$fields = CFS()->find_fields( array( 'group_id' => 123 ) );


// Find all fields with a specific field type
$fields = CFS()->find_fields( array( 'field_type' => 'relationship' ) );


// Find all text or textarea fields within a specific field group
$fields = CFS()->find_fields( array(
  'field_type' => array( 'text', 'textarea' ),
  'group_id' => 123,
) );

{% endhighlight %}
