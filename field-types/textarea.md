---
layout: default
title: Textarea
---

## {{ page.title }}

Generates a multi-line text field

### Field Options

| Option | Purpose |
|--------|---------|
| Default Value | A default value, which appears before first save |
| Formatting | Whether to automatically add newlines to the return value |

### Return Value

(string) The inputted text

### Template Usage

{% highlight php %}
<?php
echo CFS()->get( 'first_name' );
{% endhighlight %}
