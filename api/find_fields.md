---
layout: default
title: find_fields
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
