<?php
/*
Plugin Name: Excursiones
Description: Plugin para gestionar excursiones con tipos, ubicaciones y detalles como precio y plazas.
Version: 2.3
Author: Francisco Javier Montelongo Costas
Text Domain: funciones-excursiones
*/

/* =========================
   CPT EXCURSIONES
========================= */
function excursiones() {

	$labels = array(
		'name'                  => _x('Excursiones', 'Post Type General Name', 'text_domain'),
		'singular_name'         => _x('Excursión', 'Post Type Singular Name', 'text_domain'),
		'menu_name'             => __('Excursiones', 'text_domain'),
		'name_admin_bar'        => __('Excursión', 'text_domain'),
		'all_items'             => __('Todas las excursiones', 'text_domain'),
		'add_new_item'          => __('Añadir nueva excursión', 'text_domain'),
		'add_new'               => __('Añadir nueva', 'text_domain'),
		'edit_item'             => __('Editar excursión', 'text_domain'),
		'view_item'             => __('Ver excursión', 'text_domain'),
		'search_items'          => __('Buscar excursión', 'text_domain'),
		'not_found'             => __('No encontrado', 'text_domain'),
		'not_found_in_trash'    => __('No encontrado en la papelera', 'text_domain'),
	);

	$args = array(
		'label'               => __('Excursión', 'text_domain'),
		'description'         => __('Gestión de excursiones', 'text_domain'),
		'labels'              => $labels,
		'supports'            => array('title', 'editor', 'thumbnail'),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-location-alt',
		'has_archive'         => true,
		'publicly_queryable'  => true,
		'show_in_rest'        => true,
		'capability_type'     => 'post',
	);

	register_post_type('excursiones', $args);
}
add_action('init', 'excursiones', 0);


/* =========================
   TAXONOMÍAS
========================= */
function excursiones_taxonomias() {

	register_taxonomy(
		'tipo_excursion',
		array('excursiones'),
		array(
			'hierarchical'      => true,
			'labels'            => array(
				'name' => __('Tipos de excursión', 'text_domain'),
			),
			'show_ui'           => true,
			'show_admin_column' => true,
			'rewrite'           => array('slug' => 'tipo-excursion'),
		)
	);

	register_taxonomy(
		'ubicacion_excursion',
		array('excursiones'),
		array(
			'hierarchical'      => true,
			'labels'            => array(
				'name' => __('Ubicaciones', 'text_domain'),
			),
			'show_ui'           => true,
			'show_admin_column' => true,
			'rewrite'           => array('slug' => 'ubicacion-excursion'),
		)
	);
}
add_action('init', 'excursiones_taxonomias', 0);


/* =========================
   METABOX
========================= */
function excursiones_meta_boxes() {
	add_meta_box(
		'excursion_detalles',
		'Detalles de la excursión',
		'excursion_detalles_callback',
		'excursiones',
		'normal',
		'default'
	);
}
add_action('add_meta_boxes', 'excursiones_meta_boxes');


function excursion_detalles_callback($post) {

	wp_nonce_field('guardar_excursion', 'excursion_nonce');

	$precio = get_post_meta($post->ID, '_precio', true);
	$max = get_post_meta($post->ID, '_max_participantes', true);

	echo '<label>Precio</label><br>';
	echo '<input type="number" name="precio" value="' . esc_attr($precio) . '" /><br><br>';

	echo '<label>Máximo participantes</label><br>';
	echo '<input type="number" name="max_participantes" value="' . esc_attr($max) . '" /><br><br>';
}


/* =========================
   GUARDADO SEGURO
========================= */
function guardar_excursion_meta($post_id) {

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

	if (!isset($_POST['excursion_nonce']) || !wp_verify_nonce($_POST['excursion_nonce'], 'guardar_excursion')) return;

	if (!current_user_can('edit_post', $post_id)) return;

	if (get_post_type($post_id) !== 'excursiones') return;

	if (isset($_POST['precio'])) {
		update_post_meta($post_id, '_precio', sanitize_text_field($_POST['precio']));
	}

	if (isset($_POST['max_participantes'])) {
		update_post_meta($post_id, '_max_participantes', sanitize_text_field($_POST['max_participantes']));
	}
}
add_action('save_post', 'guardar_excursion_meta');