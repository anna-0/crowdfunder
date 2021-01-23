<?php
add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );
add_theme_support( 'post-thumbnails' );
add_filter( 'https_local_ssl_verify', '__return_true' );

function enqueue_parent_styles() {
   wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}

$livedonate = '11593';
$livesuccess = '11908';
$livecreate = '/22550-2';
$liveretrieve = '/22551-2';

$testdonate = '78';
$testsuccess = '113';
$testcreate = 'http://localhost/wordpress/testsite/crowdfunding/create';
$testretrieve = 'http://localhost/wordpress/testsite/crowdfunding/create-customer';

function wpb_hook_javascript() {
	if (is_page ('78')) { 
?>
<script src="https://js.stripe.com/v3/"></script>
<script type="text/javascript">
	// Create an instance of the Stripe object with your publishable API key
	const stripe = Stripe("pk_test_51HnlnCGwUojdYrl58MuM6JARYQsidbu7XzHqy1HQlM9NwyA4f4KrAhvW6bIcfvwSyYG0wzGR38wBV3gtFvnL2XPF00Ql0aOmr7");
	let priceId = null;
	let postname = false;

	function getSecondPart(str) {
		return str.split('?')[1];
	}

	document.addEventListener('click', (event) => {
		let element = event.target;
		if (element.parentElement.classList.contains('checkout-button')) {
			event.preventDefault();
			element.innerHTML = 'Loading...';
			element.style.opacity = 0.5;
		//	postname = document.querySelector('.postname').checked; //element.parentElement.parentElement.parentElement.getElementsByTagName('input').item('#postname').checked;
			let query = element.href;
			priceId = getSecondPart(query);
			console.log(query);
			createCheckoutSession(priceId, postname).then(function(data) {
				// Call Stripe.js method to redirect to the new Checkout page
				stripe
					.redirectToCheckout({
					sessionId: data.sessionId
				})
					.then(handleResult);
			});
		}
	});

	// Create a Checkout Session with the selected plan ID
	let createCheckoutSession = function(priceId, postname) {
		return fetch("http://localhost/wordpress/testsite/crowdfunding/create/", {
			method: "POST",
			headers: {
				"Content-Type": "application/json"
			},
			body: JSON.stringify({
				priceId: priceId,
				postname: postname,
			})
		}).then(function(result) {
			return result.json();
		});
	};

	// Handle any errors returned from Checkout
	let handleResult = function(result) {
		if (result.error) {
			let displayError = document.getElementById("error-message");
			displayError.textContent = result.error.message;
		}
	};
</script>
<?php
	}
	else if (is_page ('113')) {
?>

<script src="https://js.stripe.com/v3/"></script>

<script>  

	const urlParams = new URLSearchParams(window.location.search);
	const sessionId = urlParams.get("session_id");
	const priceId = urlParams.get('price_id');

	if (sessionId) {
		// Retrieve a copy of the Checkout session to inspect the data
		fetch("http://localhost/wordpress/testsite/crowdfunding/create-customer?sessionId=" + sessionId + "&priceId=" + priceId)
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
		/*const manageBillingForm = document.querySelector('#manage-billing-form');
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
            });*/
	}
</script>

<?php
	}
}
add_action('wp_head', 'wpb_hook_javascript');
/*
 global $wpdb;
 $charset_collate = $wpdb->get_charset_collate();
 require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

 //$table_name = $wpdb->prefix . 'supporters';
 $sql = "CREATE TABLE `supporters` (
	`id` INTEGER NOT NULL AUTO_INCREMENT,
	`name` text NOT NULL,
	`email` char(100) NOT NULL,
	`plan` char(100) NOT NULL,
	`amount` int(11) NOT NULL,
	`postname` boolean NOT NULL,
	`datetime` varchar(100) NOT NULL,
	PRIMARY KEY (id)
 ) $charset_collate;";
 dbDelta( $sql );
