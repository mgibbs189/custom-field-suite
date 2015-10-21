---
layout: default
title: User field
---

## User

Generates a widget for selecting users. It includes drag-n-drop ordering capabilities.

### Return Value

(array) An array of user IDs

### Template Usage

{% highlight php %}
<?php
$values = CFS()->get( 'field_users' );
foreach ( $values as $user_id ) {
    $the_user = get_user_by( 'id', $user_id );
    echo $the_user->user_login;
}
{% endhighlight %}
