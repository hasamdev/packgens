<?php
/**
 * Demo content definitions. Included by seed-demo.php.
 *
 * The copy here comes from the Packgens design file so the seeded site matches
 * the approved layouts.
 *
 * @package Packgens
 */

if ( PHP_SAPI !== 'cli' || ! defined( 'PG_DEMO_FLAG' ) ) {
	exit( 1 );
}

pg_say( 'Importing media...' );

$img = array(
	'hero_pattern'  => pg_image( 'f82b9be5', 'Box mockup pattern' ),
	'hero_dark'     => pg_image( 'c88e6f07', 'Custom printed boxes' ),
	'hands_cubes'   => pg_image( '41f4151e', 'Hands holding cube boxes' ),
	'sketching'     => pg_image( '033ea409', 'Packaging design sketches' ),
	'kukui'         => pg_image( 'a5024aeb', 'Branded subscription box' ),
	'blue_badge'    => pg_image( '9728fcab', 'Printed presentation folder' ),
	'hylyte'        => pg_image( '96043132', 'Printed beverage cartons' ),
	'dieline'       => pg_image( '083b1bd2', 'Box mockup and dieline' ),
	'box_orange'    => pg_image( '52864e85', 'Hands holding a branded box' ),
	'voucher'       => pg_image( '24d3b9aa', 'Gift voucher unboxing' ),
	'curiosity'     => pg_image( '3a60160d', 'Comparison panel' ),
	'iconix'        => pg_image( '49667793', 'Cosmetics packaging set' ),

	// Products.
	'p_gable'       => pg_image( '3b7ccf5b', 'Custom gable box' ),
	'p_choc'        => pg_image( 'f694b482', 'Chocolate presentation box' ),
	'p_floral'      => pg_image( '1786c2d7', 'Floral gift box' ),
	'p_display'     => pg_image( 'b5fd73a9', 'Retail display box' ),
	'p_cubes'       => pg_image( '5a9ed3bb', 'Printed cube boxes' ),
	'p_pouch'       => pg_image( '40a72b8f', 'Stand up pouch' ),
	'p_perfume'     => pg_image( '52b27f82', 'Rigid perfume box' ),
	'p_cream'       => pg_image( 'd6e32cc4', 'Cosmetic jar carton' ),
	'p_xmas'        => pg_image( '9abc7220', 'Seasonal gift box' ),
	'p_truffle'     => pg_image( '40eaae45', 'Chocolate truffle box' ),
	'p_coffee'      => pg_image( '527f0383', 'Coffee bag packaging' ),
	'p_beauty'      => pg_image( 'ea2f9c1d', 'Beauty subscription box' ),
	'p_pots'        => pg_image( 'ae12b337', 'Retail brand packaging set' ),
	'p_green'       => pg_image( 'b803b6a2', 'Eco cartons' ),
	'p_kit'         => pg_image( '9e194dfa', 'Product launch kit' ),
	'p_mailer'      => pg_image( 'c9b16e6b', 'Printed mailer box' ),

	// Categories.
	'c_mailer'      => pg_image( '2ae6f215', 'Custom mailer boxes' ),
	'c_rigid'       => pg_image( '2bff5505', 'Rigid boxes' ),
	'c_display'     => pg_image( '47ba9144', 'Display boxes' ),
	'c_retail'      => pg_image( '50cf98d7', 'Retail boxes' ),
	'c_food'        => pg_image( '8095cf51', 'Food packaging' ),
	'c_eco'         => pg_image( 'ba0fb37f', 'Eco packaging' ),
	'c_print'       => pg_image( 'ba6e149f', 'Printed packaging' ),
	'c_shipping'    => pg_image( 'c61abd97', 'Shipping boxes' ),
	'c_bags'        => pg_image( '663a8245', 'Paper bags' ),
	'c_tape'        => pg_image( 'f7377eaf', 'Branded tape' ),

	// Logos.
	'l_alloura'     => pg_image( 'd421e633', 'Alloura' ),
	'l_riot'        => pg_image( 'd5de8a97', 'Riot Games' ),
	'l_encompass'   => pg_image( 'ddd008f9', 'Encompass Media Group' ),
	'l_vetcs'       => pg_image( '607e89d4', 'VetCS' ),
	'l_nutrafol'    => pg_image( '8cbc03b9', 'Nutrafol' ),
	'l_loreal'      => pg_image( '9df28282', "L'Oreal" ),

	// Payments and delivery.
	'pay_visa'      => pg_image( 'c312eb04', 'Visa' ),
	'pay_mc'        => pg_image( '91309ddf', 'Mastercard' ),
	'pay_amex'      => pg_image( '13ce01a7', 'American Express' ),
	'pay_paypal'    => pg_image( '8e4b5f0f', 'PayPal' ),
	'pay_stripe'    => pg_image( '1c4648d9', 'Stripe' ),
	'pay_discover'  => pg_image( '9b96b6d9', 'Discover' ),
	'pay_wire'      => pg_image( 'd9427ccd', 'Wire transfer' ),
	'del_dhl'       => pg_image( '97a7f16c', 'DHL' ),
	'del_fedex'     => pg_image( '249da63c', 'FedEx' ),
	'del_ups'       => pg_image( 'cd11a62c', 'UPS' ),

	// People.
	'a1'            => pg_image( '2f119087', 'Customer portrait' ),
	'a2'            => pg_image( '5412a2bc', 'Customer portrait' ),
	'a3'            => pg_image( '6d797d1a', 'Customer portrait' ),
	'a4'            => pg_image( '821c093b', 'Customer portrait' ),
	'a5'            => pg_image( '9effeed8', 'Customer portrait' ),
	'a6'            => pg_image( 'bccb7ee3', 'Customer portrait' ),
	'a7'            => pg_image( 'c142779d', 'Customer portrait' ),
	'a8'            => pg_image( '08296deb', 'Customer portrait' ),

	// Blog.
	'b1'            => pg_image( 'def6e52c', 'Label printing sheets' ),
	'b2'            => pg_image( 'c61abd97', 'Shipping box' ),

	// Icons / misc.
	'i_design'      => pg_image( 'da67bff7', 'Design icon' ),
	'i_global'      => pg_image( 'da748b41', 'Worldwide icon' ),
	'i_price'       => pg_image( '597bea03', 'Pricing icon' ),
	'i_artwork'     => pg_image( 'cfe2afd5', 'Artwork icon' ),
	'trustpilot'    => pg_image( '385b6873', 'Trustpilot rating' ),
	'google'        => pg_image( 'b9600f8e', 'Google reviews' ),
);

/* =========================================================================
   Pages
   ========================================================================= */

pg_say( 'Creating pages...' );

$pages = array(
	'home'       => pg_page( 'home', 'Home' ),
	'about'      => pg_page( 'about-us', 'About Us', 'templates/about.php' ),
	'contact'    => pg_page( 'contact-us', 'Contact Us', 'templates/contact.php' ),
	'faq'        => pg_page( 'faqs', 'FAQs', 'templates/faq.php' ),
	'reviews'    => pg_page( 'reviews', 'Reviews', 'templates/reviews.php' ),
	'industries' => pg_page( 'industries', 'Industries', 'templates/industries.php' ),
	'blog'       => pg_page( 'blog', 'Blog' ),
	'quote'      => pg_page( 'get-a-quote', 'Get a Quote' ),
	'terms'      => pg_page( 'terms-conditions', 'Terms &amp; Conditions', '', '<p>Placeholder terms. Replace this with your own trading terms.</p>' ),
	'privacy'    => pg_page( 'privacy-policy', 'Privacy Policy', '', '<p>Placeholder privacy policy. Replace this with your own.</p>' ),
	'returns'    => pg_page( 'return-policy', 'Return Policy', '', '<p>Placeholder returns policy. Replace this with your own.</p>' ),
);

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $pages['home'] );
update_option( 'page_for_posts', $pages['blog'] );

/* =========================================================================
   Theme options (Customizer)
   ========================================================================= */

pg_say( 'Setting theme options...' );

$mods = array(
	'topbar_enabled'        => true,
	'topbar_message'        => '30% off Packgens Shop - limited time!',
	'topbar_chat_label'     => 'Chat With Us',
	'topbar_chat_url'       => get_permalink( $pages['contact'] ),
	'header_cta_text'       => 'Place An Order',
	'header_cta_url'        => get_permalink( $pages['quote'] ),
	'header_shipping_title' => 'Free Shipping',
	'header_shipping_text'  => 'On Orders for over &pound;80',
	'phone'                 => '0800 3457516',
	'phone_note'            => 'Advice & Sales',
	'phone_secondary'       => '+1 123 989 11 93',
	'whatsapp'              => '+1 891 1234 11 92',
	'email'                 => 'hello@packgens.test',
	'address'               => "2972 Westheimer Rd,\nSanta Ana, Illinois 85486",
	'opening_hours'         => "Mon to Fri 9:00 - 18:00\nWeekend: Closed",
	'social_facebook'       => 'https://facebook.com/',
	'social_instagram'      => 'https://instagram.com/',
	'social_linkedin'       => 'https://linkedin.com/',
	'social_youtube'        => 'https://youtube.com/',
	'quote_page_url'        => get_permalink( $pages['quote'] ),
	'reviews_page_url'      => get_permalink( $pages['reviews'] ),
	'form_success_text'     => 'Thank you. Your request has been sent and a packaging specialist will be in touch shortly.',
);

foreach ( $mods as $key => $value ) {
	set_theme_mod( 'packgens_' . $key, $value );
}

if ( function_exists( 'wc_get_page_id' ) ) {
	$shop_id = wc_get_page_id( 'shop' );

	if ( $shop_id > 0 ) {
		set_theme_mod( 'packgens_shop_page_url', get_permalink( $shop_id ) );
	}
}

/* =========================================================================
   Global option fields
   ========================================================================= */

pg_say( 'Setting global content...' );

pg_field(
	'usp_strip',
	array(
		'enable' => 1,
		'items'  => array(
			array(
				'icon'  => 'award',
				'title' => 'Premium Quality',
				'text'  => 'The quality you expect from a specialist packaging manufacturer.',
			),
			array(
				'icon'  => 'thumbs-up',
				'title' => '100% Satisfaction Guaranteed',
				'text'  => 'No hassle, no hurdles. If you are not happy, neither are we.',
			),
			array(
				'icon'  => 'headphones',
				'title' => 'Need help? 0800 3457516',
				'text'  => 'Mon to Fri, 9am to 6pm. Click for instant information.',
			),
		),
	),
	'option'
);

pg_field(
	'footer',
	array(
		'about'          => '',
		'newsletter_title' => 'Get 10% off your first order',
		'newsletter_text'  => 'Join the newsletter for packaging tips and launch offers.',
		'button_1'       => array( 'title' => 'Call me back', 'url' => get_permalink( $pages['contact'] ), 'target' => '' ),
		'button_2'       => array( 'title' => 'Order Now', 'url' => get_permalink( $pages['quote'] ), 'target' => '' ),
		'payment_title'  => 'We Accept Payment Method',
		'payment_logos'  => array_filter( array( $img['pay_visa'], $img['pay_mc'], $img['pay_amex'], $img['pay_paypal'], $img['pay_stripe'], $img['pay_discover'], $img['pay_wire'] ) ),
		'delivery_title' => 'Reliable Delivery',
		'delivery_logos' => array_filter( array( $img['del_dhl'], $img['del_fedex'], $img['del_ups'] ) ),
		'copyright'      => '&copy; %year% Packgens. All rights reserved.',
	),
	'option'
);

pg_field(
	'quote_form',
	array(
		'title'        => 'Get Instant Quote',
		'intro'        => '',
		'submit_label' => 'Send Free Quote',
		'consent'      => 'We only use your details to prepare your quote.',
		'categories'   => array(),
	),
	'option'
);

/* =========================================================================
   Product categories and products
   ========================================================================= */

$product_ids = array();

if ( post_type_exists( 'product' ) ) {
	pg_say( 'Creating product categories...' );

	$cats = array(
		'Custom Mailer Boxes' => array( $img['c_mailer'], 'Fully custom-printed mailer boxes from 25 units. No setup fees and no die-cut charges.' ),
		'Rigid Boxes'         => array( $img['c_rigid'], 'Premium rigid boxes for products that deserve a considered unboxing.' ),
		'Display Boxes'       => array( $img['c_display'], 'Counter and floor display packaging built to sell at the shelf.' ),
		'Retail Boxes'        => array( $img['c_retail'], 'Retail-ready cartons printed to your brand guidelines.' ),
		'Food Packaging'      => array( $img['c_food'], 'Food-safe cartons, trays and carriers for hospitality brands.' ),
		'Eco Packaging'       => array( $img['c_eco'], 'Recyclable kraft and FSC-certified board options.' ),
		'Printed Packaging'   => array( $img['c_print'], 'Offset and digital print across every board type we stock.' ),
		'Shipping Boxes'      => array( $img['c_shipping'], 'Corrugated shipping cartons tested for the courier network.' ),
		'Paper Bags'          => array( $img['c_bags'], 'Twist-handle and rope-handle paper bags in any print.' ),
		'Branded Tape'        => array( $img['c_tape'], 'Custom printed packing tape that finishes the unboxing.' ),
	);

	$cat_ids = array();

	foreach ( $cats as $name => $data ) {
		$cat_ids[ $name ] = pg_term( 'product_cat', $name, $data[0], $data[1] );
	}

	pg_say( 'Creating products...' );

	$products = array(
		array( 'Custom Mailer Boxes', 'p_mailer', 'Printed mailer boxes with a tuck-front closure, built for the courier network.', 0.47, 'Best Seller', 'Custom Mailer Boxes' ),
		array( 'Gable Carry Boxes', 'p_gable', 'Gable boxes with a built-in handle, ideal for takeaway and gifting.', 0.64, 'New', 'Food Packaging' ),
		array( 'Chocolate Presentation Boxes', 'p_choc', 'Compartment boxes that hold each piece exactly where you placed it.', 1.15, 'Best Price', 'Rigid Boxes' ),
		array( 'Floral Gift Boxes', 'p_floral', 'Soft-touch gift boxes with ribbon closures and printed liners.', 0.98, '', 'Retail Boxes' ),
		array( 'Counter Display Boxes', 'p_display', 'Shelf-ready display units that ship flat and assemble in seconds.', 1.42, '', 'Display Boxes' ),
		array( 'Printed Cube Boxes', 'p_cubes', 'Six-sided print with a tuck-top closure for launch kits and samples.', 0.72, 'Made in USA', 'Printed Packaging' ),
		array( 'Stand Up Pouches', 'p_pouch', 'Resealable pouches with matte or gloss finishes and clear windows.', 0.39, '', 'Food Packaging' ),
		array( 'Rigid Perfume Boxes', 'p_perfume', 'Wrapped rigid boxes with foam inserts and metallic foiling.', 2.10, '', 'Rigid Boxes' ),
		array( 'Cosmetic Cartons', 'p_cream', 'Slim folding cartons designed for skincare and cosmetics ranges.', 0.55, '', 'Retail Boxes' ),
		array( 'Seasonal Gift Boxes', 'p_xmas', 'Limited-run seasonal boxes with printed inner and outer surfaces.', 1.05, '', 'Retail Boxes' ),
		array( 'Truffle Boxes', 'p_truffle', 'Two-piece rigid boxes with die-cut inserts for confectionery.', 1.68, '', 'Rigid Boxes' ),
		array( 'Coffee Bags', 'p_coffee', 'Valved coffee bags printed in up to seven colours.', 0.44, '', 'Food Packaging' ),
		array( 'Beauty Subscription Boxes', 'p_beauty', 'Monthly subscription mailers built for repeat unboxing.', 0.89, '', 'Custom Mailer Boxes' ),
		array( 'Retail Brand Sets', 'p_pots', 'Coordinated carton, sleeve and label sets for a whole range.', 1.24, '', 'Printed Packaging' ),
		array( 'Eco Kraft Cartons', 'p_green', 'Uncoated kraft cartons with soy-based inks.', 0.51, '', 'Eco Packaging' ),
		array( 'Product Launch Kits', 'p_kit', 'Presentation kits with foam inserts and magnetic closures.', 3.40, '', 'Rigid Boxes' ),
	);

	foreach ( $products as $index => $row ) {
		list( $title, $image_key, $description, $price, $badge, $category ) = $row;

		$product_id = pg_post(
			'product',
			$title,
			array(
				'post_content' => '<p>' . esc_html( $description ) . '</p><p>Every order includes a free artwork check, a digital proof before printing and delivery inside 10 to 12 working days.</p>',
				'post_excerpt' => $description,
			),
			$img[ $image_key ]
		);

		$product_ids[] = $product_id;

		update_post_meta( $product_id, '_regular_price', $price );
		update_post_meta( $product_id, '_price', $price );
		update_post_meta( $product_id, '_sku', sprintf( 'PG-%03d', $index + 1 ) );
		update_post_meta( $product_id, '_virtual', 'no' );
		update_post_meta( $product_id, '_manage_stock', 'no' );
		update_post_meta( $product_id, '_stock_status', 'instock' );

		wp_set_object_terms( $product_id, 'simple', 'product_type' );

		if ( ! empty( $cat_ids[ $category ] ) ) {
			wp_set_object_terms( $product_id, array( (int) $cat_ids[ $category ] ), 'product_cat' );
		}

		// Feature the first six so the homepage grid fills.
		if ( $index < 6 ) {
			wp_set_object_terms( $product_id, 'featured', 'product_visibility', true );
		}

		$gallery = array( $img['p_cubes'], $img['p_display'], $img['p_choc'], $img['p_kit'] );
		update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_filter( $gallery ) ) );

		pg_field(
			'product_details',
			array(
				'badge'      => $badge,
				'subtitle'   => 'Custom printed',
				'price_unit' => '/ unit',
				'price_note' => 'Volume pricing is applied automatically at checkout.',
				'tiers'      => array(
					array( 'quantity' => 50, 'price' => wc_price_or_plain( $price * 1.25 ), 'note' => '', 'is_default' => 0 ),
					array( 'quantity' => 100, 'price' => wc_price_or_plain( $price ), 'note' => 'Save 10% vs 50 units', 'is_default' => 1 ),
					array( 'quantity' => 250, 'price' => wc_price_or_plain( $price * 0.92 ), 'note' => '', 'is_default' => 0 ),
					array( 'quantity' => 500, 'price' => wc_price_or_plain( $price * 0.86 ), 'note' => '', 'is_default' => 0 ),
					array( 'quantity' => 1000, 'price' => wc_price_or_plain( $price * 0.78 ), 'note' => '', 'is_default' => 0 ),
				),
				'specs'      => array(
					array( 'label' => 'Board weight', 'value' => '350gsm' ),
					array( 'label' => 'Board type', 'value' => 'FBB / kraft / corrugated' ),
					array( 'label' => 'Printing', 'value' => 'CMYK offset, up to 7 colours' ),
					array( 'label' => 'Finishing', 'value' => 'Matte, gloss or soft-touch lamination' ),
					array( 'label' => 'Minimum order', 'value' => '25 units' ),
					array( 'label' => 'Turnaround', 'value' => '10 to 12 working days' ),
					array( 'label' => 'Certification', 'value' => 'FSC, recyclable' ),
				),
				'tabs'       => array(
					array(
						'title'   => 'Artwork Guidelines',
						'icon'    => 'file-text',
						'content' => '<p>Send print-ready PDF, AI or EPS files with 3mm bleed and fonts outlined. Our studio checks every file free of charge and sends a digital proof before anything goes to press.</p>',
					),
					array(
						'title'   => 'Shipping &amp; Delivery',
						'icon'    => 'truck',
						'content' => '<p>Standard production is 10 to 12 working days from proof approval. Express production is available on request. Delivery is free on orders over 80.</p>',
					),
					array(
						'title'   => 'Sustainability',
						'icon'    => 'leaf',
						'content' => '<p>All board is FSC certified and fully recyclable. Soy-based inks and water-based coatings are available across the range.</p>',
					),
				),
			),
			$product_id
		);

		pg_field(
			'faqs_section',
			array(
				'enable' => 1,
				'title'  => 'Frequently Asked Questions',
				'groups' => array(
					array(
						'title' => '',
						'items' => array(
							array( 'title' => 'What is the minimum order quantity?', 'description' => '<p>25 units. There is no maximum, and unit pricing improves at every tier.</p>' ),
							array( 'title' => 'Do you offer design help?', 'description' => '<p>Yes. Our studio will prepare a dieline and check your artwork at no cost.</p>' ),
							array( 'title' => 'Can I see a proof before production?', 'description' => '<p>Always. Nothing is printed until you approve the digital proof.</p>' ),
						),
					),
				),
			),
			$product_id
		);
	}
}

/**
 * Format a price for the tier labels without depending on WooCommerce.
 *
 * @param float $amount Amount.
 * @return string
 */
function wc_price_or_plain( $amount ) {
	if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
		return get_woocommerce_currency_symbol() . number_format( (float) $amount, 2 );
	}

	return number_format( (float) $amount, 2 );
}

/* =========================================================================
   Testimonials
   ========================================================================= */

pg_say( 'Creating testimonials...' );

$testimonials = array(
	array( 'Tracy Schuppe', 'Lead Web Supervisor', 'The proof came back the same day and the finished boxes were better than the mockup. Our unboxing videos have never looked this good.', 5, 'trustpilot', 'a1' ),
	array( 'Armando McClure', 'Senior Markets Architect', 'We moved three product lines over and cut our packaging spend by a fifth. The structural advice alone was worth it.', 5, 'google', 'a5' ),
	array( 'Jamie Wilkinson', 'Chief Implementation Officer', 'Ordering 25 units to test a concept, then 5,000 once it worked, made a launch possible that would not have been.', 5, '', 'a3' ),
	array( 'Priya Raman', 'Founder', 'The team caught a bleed problem in our artwork before it cost us a print run. That is the sort of detail you want.', 5, 'trustpilot', 'a2' ),
	array( 'Daniel Okafor', 'Operations Manager', 'Delivery has hit the promised date every single time across eleven orders.', 5, '', 'a7' ),
	array( 'Sofia Marchetti', 'Brand Director', 'Soft-touch lamination on a rigid box completely changed how our product is received at retail.', 5, 'google', 'a4' ),
	array( 'Ben Turner', 'Ecommerce Lead', 'The mailer boxes survive the courier network without a scuff. Returns from damage are effectively zero now.', 5, '', 'a8' ),
	array( 'Aisha Bello', 'Head of Marketing', 'Being able to order coordinated boxes, tape and bags from one supplier saves us hours every month.', 5, 'trustpilot', 'a6' ),
	array( 'Marcus Lee', 'Product Manager', 'Kraft cartons with soy inks let us drop a sustainability claim onto the pack honestly.', 4.5, '', 'a5' ),
	array( 'Hannah Wright', 'Studio Manager', 'We send the dieline straight into our artwork template now. Two days off every project.', 5, 'google', 'a2' ),
	array( 'Tomas Nowak', 'Supply Chain Lead', 'Volume pricing applied automatically at checkout, so there was nothing to negotiate or chase.', 5, '', 'a7' ),
	array( 'Grace Adeyemi', 'Co-Founder', 'Our subscription box needed to survive three couriers and still feel like a gift. It does.', 5, 'trustpilot', 'a6' ),
	array( 'Oliver Bennett', 'Head of Retail', 'Shelf-ready cartons printed to our brand guide without a single colour correction round.', 5, 'google', 'a8' ),
	array( 'Yuki Tanaka', 'Packaging Designer', 'They produced a structure for an awkward product shape that nobody else would quote on.', 5, '', 'a4' ),
	array( 'Elena Rossi', 'Marketing Manager', 'The window carton lifted sell-through enough that we reprinted within six weeks.', 4.5, 'trustpilot', 'a1' ),
	array( 'Samuel Reid', 'Founder', 'Twenty-five units let us test three designs at once instead of betting on one.', 5, '', 'a3' ),
);

$testimonial_ids = array();

foreach ( $testimonials as $row ) {
	list( $name, $role, $text, $rating, $source, $avatar ) = $row;

	$id = pg_post( 'customer', $name, array( 'post_content' => $text ), $img[ $avatar ] );

	pg_field(
		'testimonial',
		array(
			'name'        => $name,
			'designation' => $role,
			'review'      => $text,
			'rating'      => $rating,
			'date'        => gmdate( 'Ymd', strtotime( '-' . wp_rand( 5, 300 ) . ' days' ) ),
			'source'      => $source,
		),
		$id
	);

	$testimonial_ids[] = $id;
}

/* =========================================================================
   Materials
   ========================================================================= */

pg_say( 'Creating materials...' );

$material_groups = array(
	'Printing Options'        => array(
		array( 'Offset Litho', 'The sharpest option for large runs and precise brand colours.', 'p_cubes' ),
		array( 'Digital Print', 'Cost effective below 500 units, with no plate charges.', 'p_pots' ),
		array( 'Flexographic', 'Best suited to corrugated shipping cartons.', 'p_mailer' ),
		array( 'Screen Print', 'Heavy ink lay-down for bold single-colour designs.', 'p_green' ),
	),
	'Material'                => array(
		array( 'Folding Boxboard', 'A smooth white board that prints beautifully at 300 to 400gsm.', 'c_retail' ),
		array( 'Kraft Board', 'Uncoated recycled board with a natural finish.', 'c_eco' ),
		array( 'Corrugated E-Flute', 'Thin corrugated board that protects without bulk.', 'c_shipping' ),
		array( 'Rigid Greyboard', 'Wrapped rigid board for premium presentation boxes.', 'c_rigid' ),
	),
	'Special Finishes'        => array(
		array( 'Foil Stamping', 'Metallic foil in gold, silver or a custom colour.', 'p_perfume' ),
		array( 'Spot UV', 'A raised gloss varnish over selected artwork.', 'p_truffle' ),
		array( 'Embossing', 'Raised detail you can feel through the pack.', 'p_kit' ),
		array( 'Window Patching', 'Clear or frosted windows die-cut into the face.', 'p_display' ),
	),
	'Coating and Lamination'  => array(
		array( 'Matte Lamination', 'A soft, non-reflective surface that resists fingerprints.', 'p_cream' ),
		array( 'Gloss Lamination', 'High shine that lifts photographic artwork.', 'p_floral' ),
		array( 'Soft-Touch', 'A velvet feel that signals a premium product.', 'p_beauty' ),
		array( 'Aqueous Coating', 'A water-based protective coat that stays recyclable.', 'p_coffee' ),
	),
);

foreach ( $material_groups as $group_name => $items ) {
	$term_id = pg_term( 'category_materials', $group_name );

	foreach ( $items as $item ) {
		list( $title, $summary, $image_key ) = $item;

		$material_id = pg_post( 'material', $title, array( 'post_content' => '<p>' . esc_html( $summary ) . '</p>' ), $img[ $image_key ] );

		if ( $term_id ) {
			wp_set_object_terms( $material_id, array( $term_id ), 'category_materials' );
		}

		pg_field( 'material_section', array( 'summary' => $summary, 'specs' => array(), 'button' => '' ), $material_id );
	}
}

/* =========================================================================
   Blog
   ========================================================================= */

pg_say( 'Creating blog posts...' );

// Remove the WordPress sample post and comment so the demo blog is clean.
$sample = get_page_by_path( 'hello-world', OBJECT, 'post' );

if ( $sample ) {
	wp_delete_post( $sample->ID, true );
}

$blog_cats = array(
	'Get Started'         => pg_term( 'category', 'Get Started' ),
	'Print Your Designs'  => pg_term( 'category', 'Print Your Designs' ),
	'Branding'            => pg_term( 'category', 'Branding' ),
	'Product & Packaging' => pg_term( 'category', 'Product &amp; Packaging' ),
);

$posts = array(
	array(
		'How to choose the right mailer box for your product',
		array( 'Get Started', 'Print Your Designs' ),
		'p_mailer',
		"<p>The right mailer protects the product, survives the courier network and still looks considered when it lands. Getting there is mostly a question of three decisions.</p>\n<h2>Start with the product, not the box</h2>\n<p>Measure the product at its widest point in all three dimensions, then add clearance for any void fill. A box that is 10mm too large will rattle; one that is 5mm too small will not close.</p>\n<h2>Pick the board before the print</h2>\n<p>E-flute corrugated handles most ecommerce shipping. Folding boxboard suits retail shelves. Rigid board is for products where the unboxing is part of the purchase.</p>\n<h2>Design for the flat, check on the form</h2>\n<p>Artwork is laid out on a flat dieline but read in three dimensions. Always review a proof folded up before approving a run.</p>",
	),
	array(
		'Seven finishes that make a box feel more expensive',
		array( 'Branding' ),
		'p_perfume',
		"<p>Finish is the part of packaging people notice with their hands rather than their eyes. These are the seven that make the biggest difference for the least cost.</p>\n<h2>Soft-touch lamination</h2>\n<p>A velvet surface that signals quality immediately and hides fingerprints better than gloss.</p>\n<h2>Spot UV</h2>\n<p>Gloss varnish over a matte base creates contrast without adding a colour.</p>\n<h2>Foil stamping</h2>\n<p>Metallic foil catches light in a way printed metallic ink never quite matches.</p>",
	),
	array(
		'A practical guide to sustainable packaging claims',
		array( 'Product & Packaging' ),
		'p_green',
		"<p>Recyclable, recycled, compostable and biodegradable mean four different things. Using the wrong one on a pack is a compliance problem as well as a trust problem.</p>\n<h2>Recyclable</h2>\n<p>The material can be recycled through the streams your customers actually have access to. Coatings and laminations affect this.</p>\n<h2>Recycled content</h2>\n<p>A percentage of the board came from post-consumer waste. State the percentage.</p>",
	),
	array(
		'What your artwork files need before they go to press',
		array( 'Print Your Designs' ),
		'b1',
		"<p>Most delays in packaging production come from artwork, not printing. A short checklist removes almost all of them.</p>\n<h2>Bleed and safety</h2>\n<p>3mm bleed on every edge, and keep critical text 5mm inside the trim.</p>\n<h2>Colour space</h2>\n<p>Supply CMYK, not RGB, and name any Pantone references explicitly.</p>\n<h2>Fonts</h2>\n<p>Outline them, or supply the font files with the artwork.</p>",
	),
	array(
		'Low minimum orders and why they change product launches',
		array( 'Get Started' ),
		'p_cubes',
		"<p>A 25-unit minimum turns packaging from a commitment into an experiment. That changes what a small brand can reasonably try.</p>\n<h2>Test the concept, then scale</h2>\n<p>Print a short run, put it in front of real customers, then commit to volume once the design has earned it.</p>",
	),
	array(
		'Designing display packaging that actually sells at shelf',
		array( 'Branding', 'Product & Packaging' ),
		'p_display',
		"<p>A display unit has about three seconds to do its job. Everything about the design should serve that window.</p>\n<h2>One message, read at two metres</h2>\n<p>If the header card needs more than one line to explain the product, the product is not ready for a display unit.</p>",
	),
);

$author_id = (int) get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) )[0];

foreach ( $posts as $index => $row ) {
	list( $title, $categories, $image_key, $content ) = $row;

	$post_id = pg_post(
		'post',
		$title,
		array(
			'post_content' => $content,
			'post_author'  => $author_id,
			'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( ( $index + 1 ) * 9 ) . ' days' ) ),
		),
		$img[ $image_key ]
	);

	$term_ids = array();

	foreach ( $categories as $category ) {
		if ( ! empty( $blog_cats[ $category ] ) ) {
			$term_ids[] = (int) $blog_cats[ $category ];
		}
	}

	if ( $term_ids ) {
		wp_set_object_terms( $post_id, $term_ids, 'category' );
	}
}

require __DIR__ . '/seed-sections.php';
