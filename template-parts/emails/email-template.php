<?php
/**
 * Generic email template for the theme
 *
 * @package estore/theme
 * @since 0.0.1
 */

?>
<!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php echo esc_html( $args['title'] ?? get_bloginfo() ); ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Jura:wght@300..700&display=swap" rel="stylesheet"> <?php // phpcs:ignore ?>
	<style>
		h1 {
			font: 500 32px Jura, serif;
			color: #333;
		}

		p {
			font: 500 16px Jura, serif;
			color: #333;
		}

		a {
			color: #5185c5;
		}

		.website-image {
			margin-bottom: 24px;
		}

		.email-content {
			width: 600px;
			max-width: 100%;
			margin: auto;
		}

		.box {
			padding: 12px;
			border: 1px solid #ECECEC;
			border-radius: 8px;
		}
	</style>
</head>
<body>
	<div class="website-image">
		<?php the_custom_logo(); ?>
	</div>
	<div class="email-content box">
		<?php get_template_part( 'template-parts/emails/email', $args['email-name'] ?? null, $args ?? [] ); ?>
	</div>
</body>
</html>
