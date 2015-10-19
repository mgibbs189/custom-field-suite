---
layout: default
title: save
---

## Save field values

{% highlight php %}
<?php CFS()->save( field_data, $post_data ); ?>
{% endhighlight %}

| Parameter | Required | Type | Notes |
|-----------|----------|------|-------|
| field_data  | Y | array | An associative array of field data |
| $post_id  | Y | array | An associative array of post data. To update an existing post, set $post_data['ID']. |

$post_data can include other post attributes, such as:

{% highlight php %}
$post_data['post_title']
$post_data['post_type']
$post_data['post_status']
$post_data['post_content']
$post_data['post_date']
$post_data['post_author']
{% endhighlight %}

### Return Value

(int) The post ID

### Examples

Update an existing post

{% highlight php %}
<?php
$field_data = array( 'first_name' => 'Matt' );
$post_data = array( 'ID' => 678 ); // the ID is required
CFS()->save( $field_data, $post_data );
{% endhighlight %}

Create a new post, and attach some custom fields

{% highlight php %}
<?php
$field_data = array( 'first_name' => 'Bill', 'last_name' => 'Gates' );
$post_data = array( 'post_title' => 'My Post', 'post_type' => 'person' );
CFS()->save( $field_data, $post_data );
{% endhighlight %}

How to structure `$field_data` for loop fields

{% highlight php %}
<?php
$field_data = array(
    'text_field' => 'Value',
    'loop_field' => array(
        array(
            'loop_text' => 'Value',
            'loop_date' => '2013-01-01 10:30:00',
        ),
        array(
            'loop_text' => 'Value',
            'loop_date' => '2013-01-02 12:45:00',
        ),
    ),
);
{% endhighlight %}

Copy custom fields from one post to another (using 'format' => 'raw')

{% highlight php %}
<?php
$field_data = CFS()->get( false, 123, array( 'format' => 'raw' ) );
$post_data = array( 'ID' => 456 );
CFS()->save( $field_data, $post_data );
{% endhighlight %}
