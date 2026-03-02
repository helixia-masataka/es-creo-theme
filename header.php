<?php
//条件分岐書く場合はここに記述
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

	<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php wp_head(); ?>
	</head>

	<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<?php get_template_part('template-parts/template-header'); ?>
		<div id="page-container" data-page="<?= get_data_page_type(); ?>">
			<main class="l-main">