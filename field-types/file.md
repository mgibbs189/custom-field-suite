---
layout: default
title: File Upload field
---

## File Upload

Generates a file upload field, using the native WordPress uploader

### Field Options

| Option | Purpose |
|--------|---------|
| Return Value | Return either the file URL (default) or attachment ID |

### Return Value

(string or int) Either the file URL or attachment ID

### Template Usage

{% highlight php %}
<?php
echo CFS()->get( 'my_file' );
{% endhighlight %}
