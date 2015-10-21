---
layout: default
title: Date field
---

## Text

Generates a single-line text field with a built-in datepicker widget

### Return Value

(string) The date, in `YYYY-MM-DD` format

### Template Usage

Output the raw date, e.g. `2015-10-31`

{% highlight php %}
<?php
echo CFS()->get( 'my_date' );
{% endhighlight %}

Output a formatted date, e.g. `October 31, 2015`

{% highlight php %}
<?php
echo date( 'F j, Y', strtotime( CFS()->get('my_date') ) );
{% endhighlight %}
