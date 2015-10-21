---
layout: default
title: Hyperlink
---

## {{ page.title }}

Generates a URL and Label input field

### Field Options

| Option | Purpose |
|--------|---------|
| Output Format | When to return an HTML hyperlink, or an array of input values |

### Return Value

(string or array) An HTML hyperlink, or an array of PHP input values

### Template Usage

If "Output Format" option is set to "HTML"

{% highlight php %}
<?php
echo CFS()->get( 'the_hyperlink' );
{% endhighlight %}

If "Output Format" is set to "PHP Array"

{% highlight php %}
<?php
$link = CFS()->get( 'the_hyperlink' );
/*
    Returns:
    array(
        'url' => 'http://google.com',
        'text' => 'Visit Google',
        'target' => '_blank'
    )
*/
{% endhighlight %}
