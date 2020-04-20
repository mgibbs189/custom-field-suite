<?php

CFS()->form->load_assets();

echo CFS()->form( [
    'post_id'       => $post->ID,
    'field_groups'  => $metabox['args']['group_id'],
    'front_end'     => false,
] );
