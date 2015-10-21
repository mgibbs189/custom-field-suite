---
layout: default
title: WYSIWYG field
---

## Text

Generates a visual editor field

### Field Options

| Option | Purpose |
|--------|---------|
| Formatting | Whether to pass the value through WP's the_content filter |

### Return Value

(string) The formatted HTML

### Template Usage

{% highlight php %}
<?php
echo CFS()->get( 'first_name' );
{% endhighlight %}
