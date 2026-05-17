<?php
if ( ! defined( 'ABSPATH' ) ) exit;
//EXCURSIONES
function excursiones_register_cpt() {
    $labels = array(
        'name'          => _x( 'Excursiones', 'Post Type General Name', 'funciones-excursiones' ),
        'singular_name' => _x( 'Excursión',   'Post Type Singular Name', 'funciones-excursiones' ),
        'menu_name'     => __( 'Excursiones',        'funciones-excursiones' ),
        'all_items'     => __( 'Todas las excursiones', 'funciones-excursiones' ),
        'add_new_item'  => __( 'Añadir nueva excursión', 'funciones-excursiones' ),
        'edit_item'     => __( 'Editar excursión',   'funciones-excursiones' ),
        'view_item'     => __( 'Ver excursión',      'funciones-excursiones' ),
    );

    $args = array(
        'label'           => __( 'Excursión', 'funciones-excursiones' ),
        'labels'          => $labels,
        'supports' => array( 'title', 'thumbnail' ),
        'public'          => true,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'menu_icon'       => 'dashicons-location-alt',
        'has_archive'     => true,
        'show_in_rest'    => true,
        'capability_type' => 'post',
    );

    register_post_type( 'excursiones', $args );
}

add_action( 'init', 'excursiones_register_cpt', 0 );

//RESERVAS
function reservas_register_cpt() {
    $labels = array(
        'name'          => 'Reservas',
        'singular_name' => 'Reserva',
        'menu_name'     => 'Reservas',
        'all_items'     => 'Todas las reservas',
        'add_new'       => 'Añadir reserva',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false, // No queremos que las reservas tengan una "página" propia pública
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=excursiones', // Lo metemos dentro del menú de Excursiones
        'supports'           => array( 'title' ), // Solo el título (que será el ID o nombre)
        'capability_type'    => 'post',
    );

    register_post_type( 'reservas', $args );
}
add_action( 'init', 'reservas_register_cpt' );