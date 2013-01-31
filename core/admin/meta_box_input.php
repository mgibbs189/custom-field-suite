<?php

// Passed from add_meta_box
$group_id = $metabox['args']['group_id'];

echo $this->form(
    array('front_end' => false, 'group_id' => $group_id)
);
