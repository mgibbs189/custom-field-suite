---
layout: default
title: get_reverse_related
---

## Get reverse-related posts

{% highlight php %}
<?php CFS()->get_reverse_related( $post_id, $options ); ?>
{% endhighlight %}

| Parameter | Required | Type | Notes |
|-----------|----------|------|-------|
| $post_id | Y | int | The original post ID |
| $options['post_type'] | N | mixed | Find related posts by post type(s). Accepts a string or array. |
| $options['field_name'] | N | mixed | Find related posts by field name(s). Accepts a string or array. |
| $options['field_type'] | N | mixed | Find related posts by field type(s). Accepts a string or array (default = 'relationship'). |
| $options['post_status'] | N | mixed | Find related posts by post status(es). Accepts a string or array. |

### Return Value

(array) Related post IDs

### Examples

News items have a "related_events" relationship field. To display related news on an event page:

{% highlight php %}
<?php
// This will return an array of news IDs
$related_ids = CFS()->get_reverse_related( $post->ID, array(
    'field_name' => 'related_events',
    'post_type' => 'news'
) );
{% endhighlight %}

Retrieve items from a specific post type

{% highlight php %}
<?php
$related_ids = CFS()->get_reverse_related( $post->ID, array( 'post_type' => 'news' ) );
{% endhighlight %}
