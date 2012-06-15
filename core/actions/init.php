<?php

/*--------------------------------------------------------------------------------------
*
*    Create post type
*
*    @author Matt Gibbs
*    @since 1.0.0
*
*-------------------------------------------------------------------------------------*/

$labels = array(
    'name' => __('Field Groups', 'cfs'),
    'singular_name' => __('Field Group', 'cfs'),
    'add_new' => __('Add New', 'cfs'),
    'add_new_item' => __('Add New Field Group', 'cfs'),
    'edit_item' =>  __('Edit Field Group', 'cfs'),
    'new_item' => __('New Field Group', 'cfs'),
    'view_item' => __('View Field Group', 'cfs'),
    'search_items' => __('Search Field Groups', 'cfs'),
    'not_found' =>  __('No Field Groups found', 'cfs'),
    'not_found_in_trash' => __('No Field Groups found in Trash', 'cfs'),
);

register_post_type('cfs', array(
    'labels' => $labels,
    'public' => false,
    'show_ui' => true,
    'show_in_menu' => false,
    'capability_type' => 'page',
    'hierarchical' => false,
    'supports' => array('title'),
));


/*--------------------------------------------------------------------------------------
*
*    Custom columns
*
*    @author Matt Gibbs
*    @since 1.0.0
*
*-------------------------------------------------------------------------------------*/

function cfs_columns_filter()
{
    return array(
        'cb' => '<input type="checkbox" />',
        'title' => __('Title', 'cfs'),
    );
}

add_filter('manage_edit-cfs_columns', 'cfs_columns_filter');
