<?php
 /*
 Template Name: Donate
 * @package    ThemeGrill
 * @subpackage ColorMag
 * @since      ColorMag 1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/**
 * Hook: colormag_before_body_content.
 */
do_action( 'colormag_before_body_content' );
?>

<?php 
    
require 'vendor/autoload.php';

?>

	<div id="primary">
		<div id="content" class="clearfix">
			<?php
			/**
			 * Hook: colormag_before_single_page_loop.
			 */
			do_action( 'colormag_before_single_page_loop' );

			while ( have_posts() ) :
				the_post();

				get_template_part( 'content', 'page' );

				/**
				 * Hook: colormag_before_comments_template.
				 */
				do_action( 'colormag_before_comments_template' );

				/**
				 * Functions hooked into colormag_action_after_inner_content action.
				 *
				 * @hooked colormag_render_comments - 10
				 */
				do_action( 'colormag_action_comments' );

				/**
				 * Hook: colormag_after_comments_template.
				 */
				do_action( 'colormag_after_comments_template' );

			endwhile;

			/**
			 * Hook: colormag_after_single_page_loop.
			 */
			do_action( 'colormag_after_single_page_loop' );
			?>
		</div><!-- #content -->
	</div><!-- #primary -->
    <script src="https://js.stripe.com/v3/"></script>

<script>  

  const urlParams = new URLSearchParams(window.location.search);
  const sessionId = urlParams.get("session_id");
  const priceId = urlParams.get('price_id');

  if (sessionId) {
    // Retrieve a copy of the Checkout session to inspect the data
    fetch("/get-checkout-session.php?sessionId=" + sessionId + "&priceId=" + priceId)
      .then(function(result){
        return result.json()
      })
      .then(function(session){
        var sessionJSON = JSON.stringify(session, null, 2);
        document.querySelector("pre").textContent = sessionJSON;
      })
      .catch(function(err){
        console.log('Error when fetching Checkout session', err);
      });

  // In production, this should check CSRF, and not pass the session ID.
  // The customer ID for the portal should be pulled from the 
  // authenticated user on the server.
  const manageBillingForm = document.querySelector('#manage-billing-form');
  manageBillingForm.addEventListener('submit', function(e) {
    e.preventDefault();
    fetch('http://localhost/wordpress/testsite/crowdfunding/wp-content/themes/colormag-child/create-customer.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        sessionId: sessionId
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        window.location.href = data.url;
      })
      .catch((error) => {
        console.error('Error:', error);
      });
  });
}

</script>

<?php
colormag_sidebar_select();

/**
 * Hook: colormag_after_body_content.
 */
do_action( 'colormag_after_body_content' );

get_footer();
