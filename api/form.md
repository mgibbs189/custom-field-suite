---
layout: default
title: form
---

## Create front-facing input forms

{% highlight php %}
<?php CFS()->form( $params ); ?>
{% endhighlight %}

`$params` defaults:

{% highlight php %}
<?php
$params = array(
    'post_id' => false, // by default, add new post
    'post_title' => false, // set to true to edit the title
    'post_content' => false, // set to true to edit the content
    'excluded_fields' => array(), // array of field names
    'confirmation_url' => '', // redirect URL
    'submit_label' => 'Submit',
);
{% endhighlight %}

### Examples

**(Required)** Add the following code to load all required form assets

{% highlight php %}
<?php CFS()->form->load_assets(); ?>
{% endhighlight %}

Create a new post

{% highlight php %}
<?php
echo CFS()->form( array(
    'post_id' => false,
    'post_type' => 'post',
) );
{% endhighlight %}

Edit the current post

{% highlight php %}
<?php
echo CFS()->form( array( 'post_id' => $post->ID ) );
{% endhighlight %}

Edut the current post, including the title and body

{% highlight php %}
<?php
echo CFS()->form(array(
    'post_id' => $post->ID,
    'post_title' => 'The Title',
    'post_content' => 'The Content',
));
{% endhighlight %}

Edit a specific post (ID = 123)

{% highlight php %}
<?php
echo CFS()->form( array( 'post_id' => 123 ) );
{% endhighlight %}
