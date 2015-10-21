---
layout: default
title: True / False field
---

## Text

Generates a single checkbox, with optional descriptive text beside it

### Field Options

| Option | Purpose |
|--------|---------|
| Message | (optional) Descriptive text beside the checkbox |

### Return Value

(int) 1 or 0

### Template Usage

{% highlight php %}
<?php
echo CFS()->get( 'is_valid' );
{% endhighlight %}
