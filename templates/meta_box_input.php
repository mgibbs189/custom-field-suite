<?php

$this->form->load_assets();

echo $this->form(array(
    'post_id'       => $post->ID,
    'field_groups'  => $metabox['args']['group_id'],
    'front_end'     => false,
));
