<?php

$this->form->load_assets();

echo $this->form(array(
    'field_groups' => $metabox['args']['group_id'],
    'front_end' => false,
));
