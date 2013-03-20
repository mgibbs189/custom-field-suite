<?php

$this->form->load_assets();

echo $this->form(array(
    'group_id' => $metabox['args']['group_id'],
    'front_end' => false,
));
