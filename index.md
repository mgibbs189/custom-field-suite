---
layout: default
title: Getting Started
---

## 1. Create a Field Group

Begin by creating a Field Group. A Field Group is a bundle of one or more custom fields. We can manage the fields within each Field Group. Each Field Group also has a Placement Rules box - here you specify where the Field Group should appear.

## 2. Enter Content

The Field Group will appear on edit screens as determined by Placement Rules. For example, if you choose "post_type = event" within Placement Rules, then the field group will appear on all event edit screens. Simply fill out the fields.

## 3. Pull Data Into Your Template

In order to display custom field values, you’ll need to add code to your template file. CFS comes with a lightweight API for pulling values into your template. See the get() method for more information. To output the **first_name** value, add the following code to your template:

{% highlight php %}
<?php echo CFS()->get( 'first_name' ); ?>
{% endhighlight %}