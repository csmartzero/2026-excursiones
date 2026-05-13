<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function excursiones_register_taxonomias() {
    register_taxonomy(
        'tipo_excursion',
        array( 'excursiones' ),
        array(
            'hierarchical'      => true,
            'labels'            => array(
                'name'          => __( 'Tipos de excursión', 'funciones-excursiones' ),
                'singular_name' => __( 'Tipo de excursión',  'funciones-excursiones' ),
                'search_items'  => __( 'Buscar tipos',       'funciones-excursiones' ),
                'all_items'     => __( 'Todos los tipos',    'funciones-excursiones' ),
                'edit_item'     => __( 'Editar tipo',        'funciones-excursiones' ),
                'add_new_item'  => __( 'Añadir tipo',        'funciones-excursiones' ),
                'menu_name'     => __( 'Tipos de excursión', 'funciones-excursiones' ),
            ),
            'show_ui'           => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'tipo-excursion' ),
            'show_in_rest'      => true,
        )
    );
}
add_action( 'init', 'excursiones_register_taxonomias', 0 );
