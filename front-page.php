<?php
/**
 * Front page.
 *
 * Composed entirely from the section library. Which sections appear, and in
 * what order, is controlled from the page editor.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();

packgens_render_sections( 'front', get_the_ID() );

get_footer();
