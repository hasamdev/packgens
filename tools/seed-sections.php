<?php
/**
 * Section content and navigation menus. Included by seed-data.php.
 *
 * @package Packgens
 */

if ( PHP_SAPI !== 'cli' || ! defined( 'PG_DEMO_FLAG' ) ) {
	exit( 1 );
}

pg_say( 'Building page sections...' );

$quote_url   = get_permalink( $pages['quote'] );
$contact_url = get_permalink( $pages['contact'] );
$about_url   = get_permalink( $pages['about'] );
$blog_url    = get_permalink( $pages['blog'] );
$reviews_url = get_permalink( $pages['reviews'] );
$industry_url = get_permalink( $pages['industries'] );
$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

/**
 * Shorthand for a link field value.
 *
 * @param string $title Label.
 * @param string $url   URL.
 * @return array
 */
function pg_link( $title, $url ) {
	return array( 'title' => $title, 'url' => $url, 'target' => '' );
}

/* =========================================================================
   Front page
   ========================================================================= */

$home = $pages['home'];

pg_field(
	'hero_section',
	array(
		'enable'       => 1,
		'layout'       => 'center',
		'eyebrow'      => '',
		'title'        => 'Custom Packaging Boxes Designed Online, Delivered',
		'description'  => 'Grow your sales, satisfy your customers and protect your product with custom boxes that suit what you sell and genuinely represent your brand.',
		'image'        => $img['hero_pattern'],
		'rating_text'  => '',
		'rating_value' => 5,
		'button_1'     => pg_link( 'Shop All Packaging', $shop_url ),
		'button_2'     => pg_link( 'Get Free Quote', $quote_url ),
		'slides'       => array(
			array(
				'title'       => 'Custom Packaging Boxes Designed Online, Delivered',
				'description' => 'Grow your sales, satisfy your customers and protect your product with custom boxes that suit what you sell and genuinely represent your brand.',
				'button_1'    => pg_link( 'Shop All Packaging', $shop_url ),
				'button_2'    => pg_link( 'Get Free Quote', $quote_url ),
			),
			array(
				'title'       => 'From 25 Boxes, With No Setup Fees',
				'description' => 'Test a design at low volume and scale once it has earned it. No die charges, no plate charges, no minimum you cannot meet.',
				'button_1'    => pg_link( 'Start a Test Run', $quote_url ),
				'button_2'    => pg_link( 'Talk to a Specialist', $contact_url ),
			),
			array(
				'title'       => 'Free Artwork Check on Every Order',
				'description' => 'Our studio checks bleed, colour and fonts before anything reaches the press, and sends a digital proof for approval.',
				'button_1'    => pg_link( 'Upload Your Artwork', $quote_url ),
				'button_2'    => pg_link( 'See How It Works', $about_url ),
			),
		),
		'features'     => array(
			array( 'icon' => 'truck', 'title' => 'Free shipping', 'text' => 'On all orders over 80' ),
			array( 'icon' => 'credit-card', 'title' => 'Secure payment', 'text' => 'All major cards accepted' ),
			array( 'icon' => 'headphones', 'title' => '24/7 customer support', 'text' => 'Contact us any time' ),
			array( 'icon' => 'shield-check', 'title' => '1 million+ customers', 'text' => 'Happy customers worldwide' ),
		),
	),
	$home
);

pg_field(
	'categories_section',
	array(
		'enable'      => 1,
		'title'       => 'Explore all categories',
		'description' => '',
		'source'      => 'auto',
		'limit'       => 12,
		'button'      => '',
	),
	$home
);

pg_field(
	'about_section',
	array(
		'enable'        => 1,
		'eyebrow'       => 'Who You Are',
		'title'         => 'The World&rsquo;s Best Packaging Are On Packgens',
		'description'   => '',
		'body'          => '<p>Grow your sales, satisfy your customers and protect your product with custom boxes that suit what you sell. From a 25-unit test run to a full retail rollout, the process is the same: pick a style, upload artwork, approve a proof.</p>',
		'image_left'    => $img['p_cream'],
		'image_right'   => $img['p_cubes'],
		'rating_logo'   => $img['trustpilot'],
		'rating_score'  => 'TrustScore 4.9',
		'rating_link'   => pg_link( '6,789 Reviews', $reviews_url ),
		'proof_avatars' => array(
			array( 'image' => $img['a1'] ),
			array( 'image' => $img['a5'] ),
			array( 'image' => $img['a6'] ),
		),
		'proof_rating'  => 5,
		'proof_text'    => '1.8K+ 5 Star Reviews',
		'points'        => array(),
		'button'        => pg_link( 'About Us', $about_url ),
	),
	$home
);

pg_field(
	'showcase_section',
	array(
		'enable'      => 1,
		'title'       => 'Need printed packaging? Let us help you',
		'description' => '',
		'items'       => array(
			array( 'image' => $img['c_shipping'], 'title' => 'Clothing &amp; Box', 'text' => 'Printed shipping boxes that hold their shape through the courier network and still look considered on arrival.', 'link' => pg_link( 'Shop Now', $shop_url ) ),
			array( 'image' => $img['c_tape'], 'title' => 'Branding &amp; Tapes', 'text' => 'Custom-printed tape that seals the box and carries your brand across every parcel you send.', 'link' => pg_link( 'Shop Now', $shop_url ) ),
			array( 'image' => $img['c_eco'], 'title' => 'Branding &amp; Boxes', 'text' => 'Kraft mailers printed inside and out, so the unboxing is part of the product.', 'link' => pg_link( 'Shop Now', $shop_url ) ),
			array( 'image' => $img['c_bags'], 'title' => 'Product Bags', 'text' => 'Paper carriers and bags printed to match the rest of your packaging range.', 'link' => pg_link( 'Shop Now', $shop_url ) ),
			array( 'image' => $img['c_display'], 'title' => 'Display Boxes', 'text' => 'Counter and shelf displays that present a range without extra assembly in store.', 'link' => pg_link( 'Shop Now', $shop_url ) ),
			array( 'image' => $img['c_print'], 'title' => 'Printed Packaging', 'text' => 'Full-colour litho printing across board weights, with a free artwork check on every order.', 'link' => pg_link( 'Shop Now', $shop_url ) ),
			array( 'image' => $img['c_mailer'], 'title' => 'Mailer Boxes', 'text' => 'Fold-flat mailers cut to your product, so nothing rattles and nothing is wasted.', 'link' => pg_link( 'Shop Now', $shop_url ) ),
			array( 'image' => $img['c_rigid'], 'title' => 'Rigid Boxes', 'text' => 'Wrapped greyboard with a magnetic closure, for products that need to feel expensive.', 'link' => pg_link( 'Shop Now', $shop_url ) ),
			array( 'image' => $img['c_retail'], 'title' => 'Retail Boxes', 'text' => 'Shelf-ready cartons printed to your brand guide and packed to your planogram.', 'link' => pg_link( 'Shop Now', $shop_url ) ),
			array( 'image' => $img['c_food'], 'title' => 'Food Packaging', 'text' => 'Food-safe board and coatings, with grease resistance where the product needs it.', 'link' => pg_link( 'Shop Now', $shop_url ) ),
		),
	),
	$home
);

pg_field(
	'cta_alt_section',
	array(
		'enable'      => 1,
		'style'       => 'green',
		'title'       => 'Ready to think outside the box? Let&rsquo;s get started',
		'description' => 'Get in touch with a Packgens specialist for a free instant price quote.',
		'phone_label' => 'Call Us Toll Free',
		'phone'       => '0800 3457516',
		'button'      => pg_link( 'Get a Free Quote', $quote_url ),
	),
	$home
);

pg_field(
	'chat_section',
	array(
		'enable'      => 1,
		'icon'        => 'sparkles',
		'title'       => 'Chat With Your Personal Shopping Box Assistant!',
		'description' => 'Decades of material know-how, label science, and testing so your brand always looks its best.',
		'button'      => pg_link( 'Chat With Us', $contact_url ),
	),
	$home
);

pg_field(
	'promo_section',
	array(
		'enable'      => 1,
		'eyebrow'     => 'Order Packgens Packaging For What Comes Next.',
		'title'       => 'Custom Packaging, Made For Your Product',
		'description' => '',
		'image'       => $img['hero_dark'],
		'phone_label' => 'Call us toll free',
		'phone'       => '0800 3457516',
		'button'      => pg_link( 'Get a Free Quote', $quote_url ),
	),
	$home
);

pg_field(
	'cta_section',
	array(
		'enable'      => 1,
		'style'       => 'blue',
		'title'       => 'Ready To Think Outside The Box? Let&rsquo;s Get Started!',
		'description' => 'Get in touch with a Packgens custom packaging specialist for a free instant price quote.',
		'phone_label' => 'Click For Instant Information!',
		'phone'       => '0800 3457516',
		'button'      => pg_link( 'Get a Free Quote', $quote_url ),
	),
	$home
);

/*
 * The homepage grid runs in a set order, so the products are named rather than
 * pulled from the featured flag.
 */
$featured_products = array_values(
	array_filter(
		array_map(
			static function ( $slug ) {
				$product = get_page_by_path( $slug, OBJECT, 'product' );

				return $product ? (int) $product->ID : 0;
			},
			array(
				'gable-carry-boxes',
				'chocolate-presentation-boxes',
				'floral-gift-boxes',
				'counter-display-boxes',
				'retail-brand-sets',
				'stand-up-pouches',
				'rigid-perfume-boxes',
				'cosmetic-cartons',
				'seasonal-gift-boxes',
				'truffle-boxes',
				'custom-mailer-boxes',
			)
		)
	)
);

pg_field(
	'products_section',
	array(
		'enable'      => 1,
		'title'       => 'Top Featured Products',
		'description' => '',
		'source'      => 'manual',
		'products'    => $featured_products,
		'limit'       => 11,
		'layout'      => 'grid',
		'button'      => pg_link( 'Browse All Products', $shop_url ),
	),
	$home
);

pg_field(
	'process_section',
	array(
		'enable'      => 1,
		'title'           => 'Your Design To Your Packaging In 3 Simple Steps',
		'description'     => 'Pick a style, send us artwork, approve the proof. We handle everything after that.',
		'image'           => $img['sketching'],
		'cta_title'       => 'Ready To Think Outside The Box? Let&rsquo;s Get Started!',
		'cta_description' => 'Get in touch with a Packgens custom packaging specialist for a free instant price quote.',
		'cta_phone_label' => 'Call Us Toll Free',
		'cta_phone'       => '0800 3457516',
		'cta_button'      => pg_link( 'Get a Free Quote', $quote_url ),
		'items'           => array(
			array( 'title' => 'Pick Your Style &amp; Size', 'description' => 'Choose a box style and enter your dimensions, or send us the product and we will spec it for you.', 'image' => 0, 'photo' => $img['sketching'] ),
			array( 'title' => 'Artwork Preparation', 'description' => 'Upload your files and our studio checks bleed, colour and fonts free of charge.', 'image' => 0, 'photo' => 0 ),
			array( 'title' => 'Print &amp; Fast Shipping', 'description' => 'Approve the digital proof and your order ships within 10 to 12 working days.', 'image' => 0, 'photo' => 0 ),
		),
	),
	$home
);

pg_field(
	'why_section',
	array(
		'enable'      => 1,
		'eyebrow'     => 'Why Choose Us',
		'title'       => 'Why Choose Us Your Packaging Design In The In Provide Gurarantee',
		'description' => 'Every order includes a free artwork check, a digital proof and unlimited design revisions before anything is printed.',
		'items'       => array(
			array( 'icon' => 'why-printing', 'image' => 0, 'title' => 'High Quality Offset Printing', 'description' => 'Get your orders processed and delivered promptly, ensuring the fastest turnaround time possible.' ),
			array( 'icon' => 'why-turnaround', 'image' => 0, 'title' => 'Quickest Turnaround Time', 'description' => 'Enjoy the added perk of free shipping on your orders, making it even more cost-effective for you.' ),
			array( 'icon' => 'why-price', 'image' => 0, 'title' => 'Cheapest Prices', 'description' => 'Benefit from our regular discounted rates and get the best custom packaging at the lowest prices.' ),
			array( 'icon' => 'why-plate', 'image' => 0, 'title' => 'No Die &amp; Plate Charges', 'description' => 'Enjoy the benefit of no additional costs for die and plate setups on your custom orders.' ),
			array( 'icon' => 'why-quantity', 'image' => 0, 'title' => 'Starting From 50 Boxes', 'description' => 'Order as few or as many boxes as you need without any minimum quantity restrictions.' ),
			array( 'icon' => 'why-design', 'image' => 0, 'title' => 'Custom Design Size &amp; Style', 'description' => 'Avail professional design services without any added fees, ensuring your vision comes to life.' ),
		),
		'media'       => array_filter( array( $img['hylyte'], $img['dieline'] ) ),
	),
	$home
);

pg_field(
	'comparison_section',
	array(
		'enable'        => 1,
		'eyebrow'       => 'Comparison Features',
		'title'         => 'Packaging material comparison',
		'description'   => 'See how Packgens compares against a typical trade printer before you commit to a run.',
		'table_heading' => 'Begin with Packgens',
		'us_label'      => 'Packgens',
		'them_label'    => 'Other suppliers',
		'rows'          => array(
			array( 'text' => 'Free artwork check and digital proof on every order', 'us' => 1, 'them' => 0 ),
			array( 'text' => 'No die, plate or setup charges', 'us' => 1, 'them' => 0 ),
			array( 'text' => 'Minimum order of 25 units', 'us' => 1, 'them' => 1 ),
			array( 'text' => 'Unlimited design revisions before print', 'us' => 1, 'them' => 0 ),
		),
		'points'        => array(
			array( 'text' => 'Compare suppliers easily' ),
			array( 'text' => 'Explore alternative formats' ),
			array( 'text' => 'Cut development cost and time' ),
		),
		'promo'         => array(
			'image'  => $img['voucher'],
			'title'  => 'Get 20% off your first purchase',
			'button' => pg_link( 'Order Now', $quote_url ),
		),
	),
	$home
);

pg_field(
	'stats_section',
	array(
		'enable'      => 1,
		'title'       => 'Trusted For Packgens, Boxes Made For Today',
		'description' => '',
		'image'       => $img['box_orange'],
		'items'       => array(
			array( 'value' => '', 'title' => '10+ Years of Packgens Innovation', 'description' => 'Decades of material know-how, label science, and testing so your brand always looks its best.', 'icon' => 'stat-innovation' ),
			array( 'value' => '', 'title' => 'Millions of Boxes Sold Worldwide', 'description' => 'From small shops to global brands, chances are people boxes have already landed in your customers&rsquo; hands.', 'icon' => 'stat-worldwide' ),
		),
	),
	$home
);

pg_field(
	'industries_section',
	array(
		'enable'      => 1,
		'title'       => 'Brands In Every Industry With Packgens',
		'description' => 'From food and beauty to electronics and subscription brands, every range runs on the same production platform.',
		'items'       => array(
			array( 'image' => $img['p_coffee'], 'title' => 'Food &amp; Beverage', 'description' => 'Food-safe cartons, carriers and pouches.', 'button' => pg_link( 'Learn More', $industry_url ) ),
			array( 'image' => $img['p_cream'], 'title' => 'Beauty &amp; Cosmetics', 'description' => 'Slim cartons and rigid sets for skincare ranges.', 'button' => pg_link( 'Learn More', $industry_url ) ),
			array( 'image' => $img['p_kit'], 'title' => 'Electronics', 'description' => 'Protective inserts and presentation kits.', 'button' => pg_link( 'Learn More', $industry_url ) ),
			array( 'image' => $img['p_beauty'], 'title' => 'Subscription', 'description' => 'Repeat-unboxing mailers built for the courier network.', 'button' => pg_link( 'Learn More', $industry_url ) ),
			array( 'image' => $img['p_pots'], 'title' => 'Retail', 'description' => 'Shelf-ready cartons printed to brand guidelines.', 'button' => pg_link( 'Learn More', $industry_url ) ),
			array( 'image' => $img['p_green'], 'title' => 'Eco Brands', 'description' => 'Kraft board, soy inks and recyclable coatings.', 'button' => pg_link( 'Learn More', $industry_url ) ),
		),
		'button'      => pg_link( 'View All Industry', $industry_url ),
	),
	$home
);

$home_faqs = array(
	'enable' => 1,
	'title'  => 'Frequently Asked Questions',
	'description' => 'Everything you need to know about custom packaging.',
	'groups' => array(
		array(
			'title'    => '',
			'subtitle' => '',
			'items'    => array(
				array( 'title' => 'What is your minimum order quantity?', 'description' => '<p>There is no large minimum. You can order as few as 25 units or as many as 25,000+. That flexibility lets small businesses get properly custom packaging without committing to a warehouse full of it.</p>' ),
				array( 'title' => 'How long does production take?', 'description' => '<p>Standard production is 10 to 12 working days from the moment you approve the digital proof. Express production is available on request.</p>' ),
				array( 'title' => 'Do you offer design help?', 'description' => '<p>Yes. Our studio prepares the dieline, checks your artwork and sends a proof, all included in the price.</p>' ),
				array( 'title' => 'Can I see a proof before production starts?', 'description' => '<p>Always. Nothing goes to press until you have approved a digital proof. Physical samples are available on request.</p>' ),
				array( 'title' => 'What materials do you use?', 'description' => '<p>Folding boxboard, kraft, corrugated E-flute and rigid greyboard, in weights from 250gsm upwards.</p>' ),
				array( 'title' => 'Do you ship internationally?', 'description' => '<p>Yes, worldwide through DHL, FedEx and UPS. Delivery is free on orders over 80.</p>' ),
				array( 'title' => 'What file formats do you accept for artwork?', 'description' => '<p>Print-ready PDF, AI and EPS files with 3mm bleed and outlined fonts. We will check every file before printing.</p>' ),
				array( 'title' => 'Are your boxes eco-friendly and recyclable?', 'description' => '<p>All of our board is FSC certified and fully recyclable. Soy-based inks and water-based coatings are available across the range.</p>' ),
			),
		),
	),
	'button' => '',
);

pg_field( 'faqs_section', $home_faqs, $home );

pg_field(
	'brands_section',
	array(
		'enable'      => 1,
		'title'       => 'Trusted Brands',
		'description' => 'Join over 1k+ brands creating custom packaging every time.',
		'logos'       => array_filter( array( $img['l_loreal'], $img['l_encompass'], $img['l_nutrafol'], $img['l_alloura'], $img['l_riot'], $img['l_vetcs'] ) ),
	),
	$home
);

pg_field(
	'reviews_section',
	array(
		'enable'      => 1,
		'title'       => 'Our happy customers are saying',
		'description' => 'Trusted by hundreds of brands, with verified reviews on Trustpilot and Google.',
		'items'       => array(),
		'badges'      => array(
			array( 'icon' => 'trustpilot', 'name' => 'Trustpilot', 'score' => '4.9', 'count' => '1,000+ Reviews', 'link' => pg_link( 'Trustpilot', $reviews_url ) ),
			array( 'icon' => 'google', 'name' => 'Google Reviews', 'score' => '4.9', 'count' => '546+ Reviews', 'link' => pg_link( 'Google', $reviews_url ) ),
		),
		'button'      => pg_link( 'View All Reviews', $reviews_url ),
	),
	$home
);

pg_field(
	'content_section',
	array(
		'enable'            => 1,
		'columns'           => '2',
		'content'           => '<h2>Custom packaging boxes with your logo</h2><p>Packgens builds packaging that makes products stand out. We design and manufacture product boxes that represent your brand, protect what is inside and give customers something worth opening.</p><p>Our team works with you to craft boxes that match your size, style and functionality needs, whether you are launching a skincare line, shipping subscription kits or refreshing retail packaging.</p><p>Production and design collaborate from the first concept to the last box. We print your logo carefully, use long-lasting materials and check every box against your quality standards. We never bill for design revisions.</p>',
		'content_secondary' => '<h2>Create wholesale boxes that build brand value</h2><p>We design packaging that goes beyond protection. At Packgens we help you craft a branded unboxing experience that connects with your audience and supports your sales goals.</p><p><strong>We design packaging for businesses across a wide range of industries:</strong></p><ul><li>Folding cartons and product boxes</li><li>Sleeves</li><li>Gift boxes</li><li>Point of purchase displays</li><li>Food and beverage cartons</li><li>Corrugated mailers</li><li>Hang tags</li><li>Wrapping paper</li></ul>',
	),
	$home
);

pg_field(
	'blogs_section',
	array(
		'enable'      => 1,
		'title'       => 'Packaging Insights &amp; Latest Blog',
		'description' => 'Expert tips, industry trends, and guides to help you create the perfect packaging.',
		'limit'       => 9,
		'button'      => pg_link( 'Visit Our Blog', $blog_url ),
	),
	$home
);

/* =========================================================================
   Inner pages
   ========================================================================= */

// About.
pg_field(
	'hero_section',
	array(
		'enable'       => 1,
		'layout'       => 'center',
		'eyebrow'      => 'Because good packaging should be the easiest part of your launch',
		'title'        => 'Packgens makes custom packaging accessible to every business',
		'description'  => '',
		'image'        => $img['hero_pattern'],
		'rating_text'  => '',
		'rating_value' => 5,
		'button_1'     => pg_link( 'Get a Free Quote', $quote_url ),
		'button_2'     => '',
	),
	$pages['about']
);

pg_field(
	'stats_section',
	array(
		'enable' => 1,
		'title'  => '',
		'description' => '',
		'image'  => 0,
		'items'  => array(
			array( 'value' => '1000+', 'title' => 'Satisfied customers', 'description' => 'All over the globe', 'icon' => '' ),
			array( 'value' => '10+', 'title' => 'Years of experience', 'description' => 'In custom packaging', 'icon' => '' ),
			array( 'value' => '4.9', 'title' => 'Average rating', 'description' => 'Google and Trustpilot verified', 'icon' => '' ),
		),
	),
	$pages['about']
);

pg_field(
	'about_section',
	array(
		'enable'      => 1,
		'eyebrow'     => '',
		'title'       => 'All about Packgens and what we do best',
		'description' => '',
		'body'        => '<p>We provide the platform, technology and infrastructure that keep our network running smoothly. From design and production to logistics, we help companies of every size deliver high quality packaging worldwide.</p>',
		'image_left'  => $img['kukui'],
		'image_right' => $img['blue_badge'],
		'points'      => array(
			array( 'text' => 'Flexible payment terms, including Net-15 and Net-30' ),
			array( 'text' => 'Fast turnarounds, with orders delivered in 10 to 12 working days' ),
			array( 'text' => 'Design and structural support included on every order' ),
		),
		'button'      => pg_link( 'Submit Your Order Today', $quote_url ),
	),
	$pages['about']
);

pg_field( 'reviews_section', array( 'enable' => 1, 'title' => 'What our customers say', 'description' => '', 'items' => array(), 'badges' => array(), 'button' => pg_link( 'View All Reviews', $reviews_url ) ), $pages['about'] );
pg_field( 'cta_section', array( 'enable' => 1, 'style' => 'green', 'title' => 'Ready to think outside the box?', 'description' => 'Get in touch with a Packgens specialist for a free instant price quote.', 'phone_label' => 'Book a call anytime', 'phone' => '0800 3457516', 'button' => pg_link( 'Get a Free Quote', $quote_url ) ), $pages['about'] );

// Contact.
pg_field(
	'hero_section',
	array(
		'enable'       => 1,
		'layout'       => 'split',
		'eyebrow'      => '',
		'title'        => 'Contact Us',
		'description'  => 'Fully custom-printed packaging from 25 units. No setup fees, no die-cut charges, just packaging your customers remember.',
		'image'        => $img['hands_cubes'],
		'rating_text'  => '4.9  12,400+ reviews',
		'rating_value' => 5,
		'button_1'     => '',
		'button_2'     => '',
	),
	$pages['contact']
);

// FAQ.
pg_field(
	'hero_section',
	array(
		'enable'       => 1,
		'layout'       => 'compact',
		'title'        => 'Frequently Asked Questions',
		'description'  => 'Everything you need to know about ordering custom packaging.',
		'image'        => 0,
		'rating_text'  => '',
		'rating_value' => 5,
		'button_1'     => '',
		'button_2'     => '',
	),
	$pages['faq']
);

pg_field(
	'faqs_section',
	array(
		'enable' => 1,
		'title'  => '',
		'description' => '',
		'groups' => array(
			array(
				'title'    => 'Ordering',
				'subtitle' => 'Pricing',
				'items'    => array_slice( $home_faqs['groups'][0]['items'], 0, 4 ),
			),
			array(
				'title'    => 'Product &amp; Sizing',
				'subtitle' => 'Finding the right size',
				'items'    => array_slice( $home_faqs['groups'][0]['items'], 4, 4 ),
			),
		),
		'button' => '',
	),
	$pages['faq']
);

// Reviews.
pg_field(
	'hero_section',
	array(
		'enable'       => 1,
		'layout'       => 'compact',
		'title'        => 'Customer Reviews',
		'description'  => 'Verified feedback from brands that pack with Packgens.',
		'image'        => 0,
		'rating_text'  => '4.9  12,400+ reviews',
		'rating_value' => 5,
		'button_1'     => '',
		'button_2'     => '',
	),
	$pages['reviews']
);

// Industries.
pg_field(
	'hero_section',
	array(
		'enable'       => 1,
		'layout'       => 'split',
		'title'        => 'Packaging for every industry',
		'description'  => 'Whatever you make, there is a board, a structure and a finish that suits it.',
		'image'        => $img['p_pots'],
		'rating_text'  => '',
		'rating_value' => 5,
		'button_1'     => pg_link( 'Get a Free Quote', $quote_url ),
		'button_2'     => '',
	),
	$pages['industries']
);

pg_field(
	'industries_section',
	array(
		'enable'      => 1,
		'title'       => 'Industries we serve',
		'description' => '',
		'items'       => array(
			array( 'image' => $img['p_coffee'], 'title' => 'Food &amp; Beverage', 'description' => 'Food-safe cartons, carriers and pouches.', 'button' => pg_link( 'Get a Quote', $quote_url ) ),
			array( 'image' => $img['p_cream'], 'title' => 'Beauty &amp; Cosmetics', 'description' => 'Slim cartons and rigid sets for skincare ranges.', 'button' => pg_link( 'Get a Quote', $quote_url ) ),
			array( 'image' => $img['p_kit'], 'title' => 'Electronics', 'description' => 'Protective inserts and presentation kits.', 'button' => pg_link( 'Get a Quote', $quote_url ) ),
			array( 'image' => $img['p_beauty'], 'title' => 'Subscription', 'description' => 'Repeat-unboxing mailers built for the courier network.', 'button' => pg_link( 'Get a Quote', $quote_url ) ),
			array( 'image' => $img['p_pots'], 'title' => 'Retail', 'description' => 'Shelf-ready cartons printed to brand guidelines.', 'button' => pg_link( 'Get a Quote', $quote_url ) ),
			array( 'image' => $img['p_green'], 'title' => 'Eco Brands', 'description' => 'Kraft board, soy inks and recyclable coatings.', 'button' => pg_link( 'Get a Quote', $quote_url ) ),
			array( 'image' => $img['p_display'], 'title' => 'Pharmacy &amp; Health', 'description' => 'Tamper-evident cartons and leaflet insertion.', 'button' => pg_link( 'Get a Quote', $quote_url ) ),
			array( 'image' => $img['p_truffle'], 'title' => 'Confectionery', 'description' => 'Compartment boxes and window cartons.', 'button' => pg_link( 'Get a Quote', $quote_url ) ),
		),
		'button'      => '',
	),
	$pages['industries']
);

// Quote page.
pg_field(
	'hero_section',
	array(
		'enable'       => 1,
		'layout'       => 'form',
		'title'        => 'Get an instant quote',
		'description'  => 'Tell us what you need and a packaging specialist will come back to you with pricing, usually the same working day.',
		'image'        => 0,
		'rating_text'  => '4.9  12,400+ reviews',
		'rating_value' => 5,
		'button_1'     => '',
		'button_2'     => '',
	),
	$pages['quote']
);

// Blog index.
pg_field(
	'hero_section',
	array(
		'enable'       => 1,
		'layout'       => 'split',
		'title'        => 'The Packgens blog',
		'description'  => 'Expert tips, industry trends and guides to help you create the perfect packaging.',
		'image'        => $img['hands_cubes'],
		'rating_text'  => '4.9  12,400+ reviews',
		'rating_value' => 5,
		'button_1'     => pg_link( 'Get a Free Quote', $quote_url ),
		'button_2'     => '',
	),
	$pages['blog']
);

/* =========================================================================
   Product category sections
   ========================================================================= */

if ( taxonomy_exists( 'product_cat' ) ) {
	pg_say( 'Building category sections...' );

	foreach ( get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) ) as $term ) {
		if ( ! get_term_meta( $term->term_id, PG_DEMO_FLAG, true ) ) {
			continue;
		}

		$context = 'term_' . $term->term_id;

		pg_field(
			'hero_section',
			array(
				'enable'       => 1,
				'layout'       => 'form',
				'title'        => $term->name,
				'description'  => $term->description,
				'image'        => 0,
				'rating_text'  => '4.9  12,400+ reviews',
				'rating_value' => 5,
				'button_1'     => pg_link( 'Get a Free Quote', $quote_url ),
				'button_2'     => pg_link( 'Need help choosing?', $contact_url ),
				'features'     => array(
					array( 'icon' => 'package', 'title' => 'All box styles', 'text' => '' ),
					array( 'icon' => 'leaf', 'title' => 'Kraft and eco board', 'text' => '' ),
					array( 'icon' => 'award', 'title' => 'Luxury finishes', 'text' => '' ),
					array( 'icon' => 'zap', 'title' => 'Fast turnaround', 'text' => '' ),
				),
			),
			$context
		);

		pg_field(
			'products_section',
			array(
				'enable'      => 1,
				'title'       => $term->name . ' styles',
				'description' => '',
				'source'      => 'category',
				'products'    => array(),
				'limit'       => 12,
				'layout'      => 'grid',
				'button'      => '',
			),
			$context
		);

		pg_field(
			'materials_section',
			array(
				'enable'      => 1,
				'title'       => 'Packaging materials',
				'description' => 'Print, board, finish and coating options available for this range.',
				'items'       => array(),
				'button'      => '',
			),
			$context
		);

		pg_field( 'faqs_section', $home_faqs, $context );

		pg_field(
			'cta_section',
			array(
				'enable'      => 1,
				'style'       => 'blue',
				'title'       => 'Not sure which style fits your product?',
				'description' => 'Send us the dimensions and we will spec it for you, free of charge.',
				'phone_label' => 'Call us toll free',
				'phone'       => '0800 3457516',
				'button'      => pg_link( 'Get a Free Quote', $quote_url ),
			),
			$context
		);
	}
}

/* =========================================================================
   Menus
   ========================================================================= */

pg_say( 'Building menus...' );

/**
 * Create a menu from a nested definition and assign it to a location.
 *
 * @param string $name     Menu name.
 * @param string $location Theme location.
 * @param array  $items    Menu items.
 * @return void
 */
function pg_menu( $name, $location, $items ) {
	$existing = wp_get_nav_menu_object( $name );

	if ( $existing ) {
		wp_delete_nav_menu( $existing->term_id );
	}

	$menu_id = wp_create_nav_menu( $name );

	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	$add = function ( $item, $parent = 0 ) use ( $menu_id, &$add ) {
		$item_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $item['title'],
				'menu-item-url'       => $item['url'],
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent,
				'menu-item-classes'   => $item['classes'] ?? '',
			)
		);

		if ( ! empty( $item['image'] ) ) {
			update_post_meta( $item_id, '_packgens_menu_image', (int) $item['image'] );
		}

		foreach ( $item['children'] ?? array() as $child ) {
			$add( $child, $item_id );
		}
	};

	foreach ( $items as $item ) {
		$add( $item );
	}

	$locations              = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

$category_children = array();

if ( taxonomy_exists( 'product_cat' ) ) {
	foreach ( get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'number' => 10 ) ) as $term ) {
		if ( ! get_term_meta( $term->term_id, PG_DEMO_FLAG, true ) ) {
			continue;
		}

		$category_children[] = array(
			'title' => $term->name,
			'url'   => get_term_link( $term ),
			'image' => (int) get_term_meta( $term->term_id, 'thumbnail_id', true ),
		);
	}
}

pg_menu(
	'Packgens Primary',
	'primary',
	array(
		array( 'title' => 'Home', 'url' => home_url( '/' ) ),
		array(
			'title'    => 'Industries',
			'url'      => $industry_url,
			'classes'  => 'mega cards',
			// The first four categories show as image cards; the rest fill the
			// link column, and the last child becomes the Explore All button.
			'children' => array_merge(
				array_slice( $category_children, 0, 4 ),
				array(
					array(
						'title'    => 'All Categories',
						'url'      => $shop_url,
						'children' => array_map(
							static function ( $child ) {
								return array( 'title' => $child['title'], 'url' => $child['url'] );
							},
							array_slice( $category_children, 4, 8 )
						),
					),
					array(
						'title'   => 'Explore All',
						'url'     => $shop_url,
						'classes' => 'mega-explore',
					),
				)
			),
		),
		array( 'title' => 'Boxes', 'url' => $shop_url ),
		array( 'title' => 'Other Printing', 'url' => $shop_url ),
		array( 'title' => 'About', 'url' => $about_url ),
		array( 'title' => 'Blog', 'url' => $blog_url ),
		array( 'title' => 'Contact Us', 'url' => $contact_url ),
	)
);

pg_menu(
	'Packgens Footer Information',
	'footer_info',
	array(
		array( 'title' => 'Home', 'url' => home_url( '/' ) ),
		array( 'title' => 'About Us', 'url' => $about_url ),
		array( 'title' => 'Industries', 'url' => $industry_url ),
		array( 'title' => 'Boxes', 'url' => $shop_url ),
		array( 'title' => 'FAQs', 'url' => get_permalink( $pages['faq'] ) ),
		array( 'title' => 'Blog', 'url' => $blog_url ),
		array( 'title' => 'Contact Us', 'url' => $contact_url ),
	)
);

pg_menu(
	'Packgens Footer Industries',
	'footer_industry',
	array(
		array( 'title' => 'Retail Boxes', 'url' => $shop_url ),
		array( 'title' => 'Cosmetic Boxes', 'url' => $shop_url ),
		array( 'title' => 'Food Boxes', 'url' => $shop_url ),
		array( 'title' => 'Candle Boxes', 'url' => $shop_url ),
		array( 'title' => 'Automotive Boxes', 'url' => $shop_url ),
	)
);

pg_menu(
	'Packgens Footer Products',
	'footer_products',
	array_map(
		static function ( $child ) {
			return array( 'title' => $child['title'], 'url' => $child['url'] );
		},
		array_slice( $category_children, 0, 5 )
	)
);

pg_menu(
	'Packgens Footer Legal',
	'footer_legal',
	array(
		array( 'title' => 'Terms &amp; Conditions', 'url' => get_permalink( $pages['terms'] ) ),
		array( 'title' => 'Privacy Policy', 'url' => get_permalink( $pages['privacy'] ) ),
		array( 'title' => 'Return Policy', 'url' => get_permalink( $pages['returns'] ) ),
	)
);

flush_rewrite_rules();

pg_say( 'Done. Visit ' . home_url( '/' ) );
