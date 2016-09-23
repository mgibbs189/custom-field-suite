---
layout: default
title: Term
---

## {{ page.title }}

Generates a widget for selecting taxonomy terms. It includes drag-n-drop ordering capabilities.

### Return Value

(array) An array of term_ids

### Template Usage

{% highlight php %}
<?php
$values = CFS()->get( 'field_terms' );
foreach ( $values as $term_id ) {
    $the_term = get_term($term_id);
    echo $the_term->name;
}
{% endhighlight %}
