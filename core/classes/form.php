<?php

class cfs_Form
{
    public $parent;

    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    public function create_form($options)
    {
        $options = array(
            'post_id' => false,
            'post_title' => false,
            'post_status' => 'draft',
            'form' => array(
                'title' => false,
                'description' => false,
                'groups' => array(
                    array(
                        'title' => false,
                        'description' => false,
                        'prev_button_text' => 'Previous',
                        'next_button_text' => 'Next',
                        'field_groups' => array(),
                        'fields' => array(
                            'first_field',
                            'second_field',
                            'third_field' => array(
                                'label' => 'The Third Field',
                            ),
                        ),
                    ),
                    array(
                        'title' => false,
                        'description' => false,
                        'prev_button_text' => 'Previous',
                        'next_button_text' => 'Next',
                        'field_groups' => array(),
                        'fields' => array(
                            'first_field',
                            'second_field',
                            'third_field' => array(
                                'label' => 'The Third Field',
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}
