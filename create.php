<?php
 /*
 Template Name: Create
 * @package    ThemeGrill
 * @subpackage ColorMag
 * @since      ColorMag 1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php

require 'vendor/autoload.php';
\Stripe\Stripe::setApiKey(STRIPE_TEST_SECRET);

header('Content-Type: application/json');

$YOUR_DOMAIN = 'http://localhost/wordpress/testsite/crowdfunding';
//$YOUR_DOMAIN = 'https://romanroadlondon.com';


$input = file_get_contents('php://input');
$body = json_decode($input);

$price_id = $body->priceId;
$metadata = $body->postname;

$customer = \Stripe\Customer::create([
  'metadata' => ['postname' => $metadata],
]);

//$price = \Stripe\Price::retrieve($price_id);

//if ($price->product == 'prod_IPhI4nYO2m8q9X') {
  $checkout_session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['bacs_debit'],
    'mode' => 'setup',
    'customer' => $customer->id,
    'success_url' => $YOUR_DOMAIN . '/thank-you?session_id={CHECKOUT_SESSION_ID}' . '&price_id=' . $price_id,
    'cancel_url' => $YOUR_DOMAIN . '/support-us',
  ]);  
//}
/*
if ($price->product == 'prod_IUx988lqmSx0LI'){
  $checkout_session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'mode' => 'payment',
    'line_items' => [[
      'price' => $price_id,
      'quantity' => 1,
      'description' => $price->description,
    ]],
	'payment_intent_data' => [
      'description' => $price->description,
    ],
    'customer' => $customer->id,
    'success_url' => $YOUR_DOMAIN . '/thank-you?session_id={CHECKOUT_SESSION_ID}' . '&price_id=' . $price_id,
    'cancel_url' => $YOUR_DOMAIN . '/donate',
  ]);
}*/


echo json_encode(['sessionId' => $checkout_session['id'], 'priceId' => $price_id]);
