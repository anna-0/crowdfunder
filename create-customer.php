<?php
 /*
 Template Name: Create customer
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

global $wpdb;

$supporterstable = 'supporters';

\Stripe\Stripe::setApiKey(STRIPE_TEST_SECRET);

// Fetch the Checkout Session
$id = $_GET['sessionId']; //"cs_test_c1J2QEuqL4CtxpNyuWLHCPvwnecJRIjmwoDVXXgNB8YB9stiW9epegnjwV";

$priceId = $_GET['priceId'];//'price_1HpcWhGwUojdYrl5djRF7aON';

$price = \Stripe\Price::retrieve([
  'id' => $priceId,
  'expand' => ['product']
  ]);

$session = \Stripe\Checkout\Session::retrieve([
  'id' => $id,
  'expand' => ['customer', 'setup_intent.payment_method.billing_details', 'payment_intent.payment_method.billing_details'],
]);


if ($session->setup_intent) {
  $intent = $session->setup_intent;
  $subscription = \Stripe\Subscription::create([
    'customer' => $intent->customer,
    'default_payment_method' => $intent->payment_method,
    'items' => [[
      'price' => $priceId,
      'quantity' => 1,
    ]],
    'billing_cycle_anchor' => 1609502400,
    'proration_behavior' => 'none',
  ]);
}
else if ($session->$payment_intent) {
  $intent = $session->payment_intent;
}

$cardholderName = $intent->payment_method->billing_details->name;

$string = implode('-', array_map('ucwords', explode('-', strtolower($cardholderName))));
if ((strtok($string, ' ') == 'Mrs') || (strtok($string, ' ') == 'Miss') || (strtok($string, ' ') == 'Mr') || (strtok($string, ' ') == 'Ms') || (strtok($string, ' ') == 'Dr')) {
    $string = substr(strstr($string," "), 1);
}
$string = explode(" ", $string);
$firstName = $string[0];

\Stripe\Customer::update($intent->customer,
[
    'name' => $cardholderName,
    'metadata' => ['First name' => $firstName],
]);

$wpdb->insert( $supporterstable, array(
  'name' => $cardholderName, 
  'email' => $session->customer->email,
  'plan' => $price->product->name,
  'amount' => $price->unit_amount,
  'postname' => $session->customer->metadata->postname,
  'datetime' => $intent->created,
));

echo json_encode($session);
