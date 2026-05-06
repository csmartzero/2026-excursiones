<?php
/*
Plugin Name: excursiones
Description: Plugin con las funciones para poder tener excursiones y poder excursiones
Version: 1.0
Author: Francisco Javier Montelongo Costas
Text Domain: funciones-excursiones
*/

function excursiones()
{

    $labels = array(
        'name'                  => _x('Excursiones', 'Post Type General Name', 'text_domain'),
        'singular_name'         => _x('Excursión', 'Post Type Singular Name', 'text_domain'),
        'menu_name'             => __('Excursiones', 'text_domain'),
        'name_admin_bar'        => __('Excursión', 'text_domain'),
        'archives'              => __('Archivo de excursiones', 'text_domain'),
        'attributes'            => __('Atributos de excursión', 'text_domain'),
        'parent_item_colon'     => __('Excursión padre:', 'text_domain'),
        'all_items'             => __('Todas las excursiones', 'text_domain'),
        'add_new_item'          => __('Añadir nueva excursión', 'text_domain'),
        'add_new'               => __('Añadir nueva', 'text_domain'),
        'new_item'              => __('Nueva excursión', 'text_domain'),
        'edit_item'             => __('Editar excursión', 'text_domain'),
        'update_item'           => __('Actualizar excursión', 'text_domain'),
        'view_item'             => __('Ver excursión', 'text_domain'),
        'view_items'            => __('Ver excursiones', 'text_domain'),
        'search_items'          => __('Buscar excursión', 'text_domain'),
        'not_found'             => __('No encontrado', 'text_domain'),
        'not_found_in_trash'    => __('No encontrado en la papelera', 'text_domain'),
        'featured_image'        => __('Imagen destacada', 'text_domain'),
        'set_featured_image'    => __('Asignar imagen destacada', 'text_domain'),
        'remove_featured_image' => __('Eliminar imagen destacada', 'text_domain'),
        'use_featured_image'    => __('Usar como imagen destacada', 'text_domain'),
        'insert_into_item'      => __('Insertar en la excursión', 'text_domain'),
        'uploaded_to_this_item' => __('Subido a esta excursión', 'text_domain'),
        'items_list'            => __('Lista de excursiones', 'text_domain'),
        'items_list_navigation' => __('Navegación de excursiones', 'text_domain'),
        'filter_items_list'     => __('Filtrar excursiones', 'text_domain'),
    );

    $args = array(
        'label'                 => __('Excursión', 'text_domain'),
        'description'           => __('Gestión de excursiones', 'text_domain'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'taxonomies'            => array('category', 'post_tag'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'menu_icon'             => 'dashicons-location-alt',
    );

    register_post_type('excursiones', $args);
}

add_action('init', 'excursiones', 0);
