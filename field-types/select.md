---
layout: default
title: Select
---

## {{ page.title }}

Generates either a single-select dropdown, or a multi-select input field

### Field Options

| Option | Purpose |
|--------|---------|
| Choices | A list of dropdown options, one per line. For each line, you can optionally use `value : label`. Use `{empty}` for an empty value. |
| Multiple? | Whether to support multiple selections |

### Return Value

(array) An array of selected values, **even for single-select fields**

### Template Usage

{% highlight php %}
<?php
$values = CFS()->get( 'my_select' );
foreach ( $values as $key => $label ) {
    echo $label;
}
{% endhighlight %}
