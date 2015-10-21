---
layout: default
title: Relationship field
---

## Relationship

Generates a widget for selecting other post type items. It includes drag-n-drop ordering capabilities.

### Field Options

| Option | Purpose |
|--------|---------|
| Post Types | (optional) Limit the list to the chosen post types |
| Limits | (optional) Require a min and/or max number of selected items |

### Return Value

(array) An array of post IDs

### Template Usage

{% highlight php %}
<?php
$values = CFS()->get( 'related_posts' );
foreach ( $values as $post_id ) {
    $the_post = get_post( $post_id );
    echo $the_post->post_title;
}
{% endhighlight %}
