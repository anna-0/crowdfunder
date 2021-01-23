<?php
 /*
 Template Name: Crowdfunding
 * @package    ThemeGrill
 * @subpackage ColorMag Pro
 * @since      ColorMag Pro 1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

?>

<div id="content" class="clearfix">
	
	<?php
		date_default_timezone_set('GMT');
		
		// TABITHA: Change date for deadline, and date to start counting GoCardless payments from  
		$deadline = "2020-12-16 23:55:00";

		// TABITHA: Amount to add to whatever you want the total amount of cash/backers to be
		$plusamount = 9000 + 2400 + 48 + 75;
		$plusbackers = 242 + 71 + 1;

		$goal = 30000;

		// Connect to API
		require 'vendor/autoload.php';
		
		$response = new \Stripe\StripeClient(STRIPE_LIVE_SECRET);

		set_transient( 'stripe', $response );
		
		$stripe = get_transient( 'stripe' );

		if ( false === $stripe ) {
			$response = new \Stripe\StripeClient(STRIPE_LIVE_SECRET);

			set_transient( 'stripe', $response );
		}
	
		$charges = $stripe->charges->all(['limit' => 100]);
		$customers = $stripe->customers->all(['limit' => 100]);
		$subscrips = $stripe->subscriptions->all(['limit' => 100]);
		$schedules = $stripe->subscriptionSchedules->all(['limit' => 3]);

		foreach ($customers->autoPagingIterator() as $customer) {
			$cArray[] = $customer;
		}

		$backers = $plusbackers;
		$amount = 0;
		$Array = [];

		foreach ($schedules as $schedule) {	
			foreach ($cArray as $customer) {
				if ($customer->id == $schedule->customer) {
					$Array[] = $customer;
				}
			}
		}

		// Put actual amounts in arrays
		foreach ($subscrips->autoPagingIterator() as $subscrip) {
			if ($subscrip->status != 'canceled') {
				$amount += $subscrip->plan->amount;
				foreach ($cArray as $customer) {
					if ($customer->id == $subscrip->customer) {
						$Array[] = $customer;
					}
				}
			}
		}

		// Count Stripe customers and add to customer array
		foreach ($charges->autoPagingIterator() as $charge) {
			if ($charge->status == 'succeeded' && $charge->description != 'Subscription creation')  {
				$amount += $charge->amount;
				foreach ($cArray as $customer) {
					if ($customer->id == $charge->customer) {
						$Array[] = $customer;
					}
				}
			}
		}

		// Handles null
		$car = array_replace($Array,array_fill_keys(array_keys($Array, null),''));
		
		// Count unique customers
		$uniqueC = array_unique(array_column($car, 'email')); 
		$backers += count($uniqueC);
		
		foreach ($cArray as $customer) {
			if (in_array($customer, $Array)) {
				foreach ($uniqueC as $email) {
					if (($email == $customer->email) && ($customer->metadata['postname'] == 'true')) {
						if ($customer->name == '') {
							continue;
						}
						$name = $customer->name;
						$string = implode('-', array_map('ucwords', explode('-', strtolower($name))));
						if ((strtok($string, ' ') == 'Mrs') || (strtok($string, ' ') == 'Miss') || (strtok($string, ' ') == 'Mr') || (strtok($string, ' ') == 'Ms')) {
							$string = substr(strstr($string," "), 1);
						}
						$supporters[] = $string;
					}
				}
			}
		}

	$supporters = array_unique($supporters);
//		$supporters = array_slice($supporters, 0, 5, true);

		// Count up totals and percentages for template
		$amount = $amount / 100;
		$total = $amount + $plusamount;
		$percentage = ($total / $goal) * 100;

		if ($percentage > 100) {
			$percentage = 100;
		}

		// Countdown to days
		$today = new DateTime();
		$today->setTimezone(new DateTimeZone('Europe/London'));
		$deadline = new DateTime($deadline, new DateTimeZone('Europe/London'));
		$interval = $today->diff($deadline);
	?>

	<!--Editable H1-->
	<header class="entry-header">
		<?php if ( is_front_page() ) : ?>
			<h2 class="entry-title">
				<?php the_title(); ?>
			</h2>
		<?php else : ?>
			<h1 class="entry-title">
				<?php the_title(); ?>
			</h1>
		<?php endif; ?>
	</header>

	<!--Body-->
	<div class="container">
		<div class="sub-main">
			<div class="frame">
				<iframe width="800" height="100%" src="https://www.youtube.com/embed/PGZuor3GDA4" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
			<span class="video-caption">Watch our video featuring the faces and places from our local community</span>
			<ul class="social-icons" style="margin-bottom:1em;">
				<li><a href="https://www.facebook.com/RomanRoadLDN" target="_blank"><span class="iconify" data-icon="bx:bxl-facebook" data-inline="false"></span>Roman Road LDN</a></li>
				<li><a href="https://twitter.com/romanroadldn" target="_blank"><span class="iconify" data-icon="bx:bxl-twitter" data-inline="false"></span>romanroadldn</a></li>
				<li><a href="https://www.instagram.com/romanroadldn/" target="_blank"><span class="iconify" data-icon="bx:bxl-instagram" data-inline="false"></span>romanroadldn</a></li>
				<li><a href="https://www.linkedin.com/company/roman-road-ldn/" target="_blank"><span class="iconify" data-icon="bx:bxl-linkedin-square" data-inline="false"></span>Roman Road LDN</a></li>
			</ul>
		</div>

		<div class="sub-main">
			<div class="content">
				<em><p>Roman Road LDN - Using Local Journalism to Strengthen Community</p></em>
				<p>Support our work to strengthen the community and support the high street during these difficult times. Our normal revenue streams have dramatically decreased and this funding will allow us to continue providing our services to East London's Roman Road community for another year.</p>
				<div class="grid-container">
					<div class="item1">
						<span id="currentAmount">
							£<span class="count-number" data-id="pounds" data-to="<?php echo number_format($total, 0, '.', '');?>">
								<?php echo number_format($total, 0, '.', ',');?></span>						
							</span>
							<br> of £<?php echo number_format($goal, 0, '', ',')?> goal
					</div>
					<div class="item2" id="right">
						<span class="bold larger count-number" data-to="<?php echo $backers; ?>"><?php echo $backers; ?></span><br>backers
					</div>
					<div class="item3">
						<!--Progress bar-->
						<div id="progress"><div id="bar"></div></div>
					</div>
					<div class="item4">
						<span class="count-number" data-to="<?php echo floor($percentage); ?>"><?php echo floor($percentage); ?></span>%
					</div>
					<div class="item5" id="right">
						<span class="days-left">
							<?php 
								if ($interval->days > 1) {
									echo $interval->format('%d days left');
								}
								else if ($interval->days == 1) {
									echo $interval->format('%d day left');
								}
								else if (($interval->format('%h') < 24) && ($interval->format('%r%h')[0] == '+')) {
									echo $interval->format('%h hours left');
								}
								else if (($interval->format('%r%h')[0] == '-') && ($interval->format('%h') < 24)) {
									echo 'This campaign has ended.';
								}
							?>
						</span>
					</div>
				</div>
				
				<a href="https://romanroadlondon.com/campaign/donate/">
					<button id="back-it">Back it now</button>
				</a>

				<!-- AddToAny BEGIN -->
				<div class="share-buttons">
					<p>Help by sharing:</p>
					<a href="https://www.addtoany.com/add_to/facebook?linkurl=https%3A%2F%2Fromanroadlondon.com%2Fcampaign%2F&amp;linkname=" target="_blank"><img class="icons" src="https://static.addtoany.com/buttons/facebook.svg" style="background-color:#3b5998"></a>
					<a href="https://twitter.com/intent/tweet?text=Please+consider+donating+to+%40RomanRoadLDN+so+that+it+can+continue+supporting+its+community+and+high+street+with+community-led+journalism+%23WeLoveRomanRoadLDN+https%3A%2F%2Fromanroadlondon.com%2Fcampaign%2F" target="_blank"><img class="icons" src="https://static.addtoany.com/buttons/twitter.svg" style="background-color:#00aced"></a>
					<a href="https://www.addtoany.com/add_to/linkedin?linkurl=https%3A%2F%2Fromanroadlondon.com%2Fcampaign%2F&amp;linkname=" target="_blank"><img class="icons" src="https://static.addtoany.com/buttons/linkedin.svg" style="background-color:#007bb6"></a>
					<a href="https://www.addtoany.com/add_to/email?linkurl=https%3A%2F%2Fromanroadlondon.com%2Fcampaign%2F&amp;linkname=" target="_blank"><img class="icons" src="https://static.addtoany.com/buttons/email.svg" style="background-color:royalblue"></a>
					<a href="https://www.addtoany.com/add_to/whatsapp?linkurl=https%3A%2F%2Fromanroadlondon.com%2Fcampaign%2F&amp;linkname=" target="_blank"><img class="icons" src="https://static.addtoany.com/buttons/whatsapp.svg" style="background-color:#4FCE5D"></a>
				</div>
				<!-- AddToAny END -->
			</div>
			<div class="supporters">
				<div class="title">
					<h4>Our supporters ♥</h4>
				</div>
				<div class="body">
					<div class="person">
						<?php 
					foreach ($supporters as $supporter) {
						echo "<p>$supporter</p>";
					} ?>
					</div>
				</div>
				<div class="seeall">
				</div>
			</div>
		</div>

		<div class="sub-main">
			<!--Gutenberg content-->
			<div class="gutenberg-blocks">
				<div class="sidebar-box">
					<?php
					while ( have_posts() ) : the_post();
						the_content();
					endwhile; 
					?>
				</div>
			</div>
		</div>

		<div class="sub-main">
			<div class="sidebar-box">
				<div class="sidebar-margin">
					<h2>Donation Options</h2>
					<h4>Become a Patron</h4>
					<span class="sidebar-text">
						<p>Not subscribed as a Patron yet? Join us as an annual Patron and become part of the ecosystem we are building for our community, where businesses, residents and community groups can support each other. Receive a free tote bag and our quarterly Patron Review.</p>
						<p>If you wish to increase an existing donation (options are £2, £4, £8 or £16 per month) please email hello@romanroadlondon.com</p>
					</span>
				</div>
				<a class="patron-link" href="https://romanroadlondon.com/campaign/donate/">
					<div class="card card-patron">
						<div class="card-header">
							<p><span class="price">£24</span><br>Patron Annual Donation</p>
						</div>
						<div class="card-body">
							<p>Join our Patron Scheme with an annual donation to become part of our community ecosystem (£24 per annum is the equivalent of donating £2 per month). </p>
						</div>
					</div>
				</a>
				<a class="patron-link" href="https://romanroadlondon.com/campaign/donate/">
					<div class="card card-patron">
						<div class="card-header">
							<p><span class="price">£48</span><br>Patron Annual Donation</p>
						</div>
						<div class="card-body">
							<p>Join our Patron Scheme with an annual donation to become part of our community ecosystem (£48 per annum is the equivalent of donating £4 per month).</p>
						</div>
					</div>
				</a>
				<a class="patron-link" href="https://romanroadlondon.com/campaign/donate/">
					<div class="card card-patron">
						<div class="card-header">
							<p><span class="price">£96</span><br>Patron Annual Donation</p>
						</div>
						<div class="card-body">
							<p>Join our Patron Scheme with an annual donation to become part of our community ecosystem (£96 per annum is the equivalent of donating £8 per month).</p>
						</div>
					</div>
				</a>
				<a class="patron-link" href="https://romanroadlondon.com/campaign/donate/">
					<div class="card card-patron">
						<div class="card-header">
							<p><span class="price">£192</span><br>Patron Annual Donation</p>
						</div>
						<div class="card-body">
							<p>Join our Patron Scheme with an annual donation to become part of our community ecosystem (£192 per annum is the equivalent of donating £16 per month).</p>
						</div>
					</div>
				</a>
				<br>
				<div class="sidebar-margin">
				<h4>Make a Single Donation</h4>
					<span class="sidebar-text">
						<p>Prefer to donate on a one-off basis? Or maybe you are a Patron already and would like to offer an additional single donation? It costs £30,000 per year for Roman Road LDN to fund its editorial team of two. Your single donation will help cover the costs of our Commissioning Editor and our paid Intern. (We pay our interns the Minimum Living Wage and they work for a maximum of three months).</p>
					</span>
				</div>
				<a class="patron-link" href="https://romanroadlondon.com/campaign/donate/">
					<div class="card card-single">
						<div class="card-header">
							<p><span class="price">£15</span><br>Sponsor one hour</p>
						</div>
						<div class="card-body">
							<p>If just 5% of the 40,000 people who lived in our catchment area, sponsored our ‘Editorial Team of Two’ for one hour, it would be enough to fund us for one year. Every little helps! </p>
						</div>
					</div>
				</a>
				<a class="patron-link" href="https://romanroadlondon.com/campaign/donate/">
					<div class="card card-single">
						<div class="card-header">
							<p><span class="price">£30</span><br>Sponsor an event listing</p>
						</div>
						<div class="card-body">
							<p>Sponsor our ‘Editorial Team of Two’ for two hours – that’s how long it takes to source and write an event listing for our Things To Do section.</p>
						</div>
					</div>
				</a>
				<a class="patron-link" href="https://romanroadlondon.com/campaign/donate/">
					<div class="card card-single">
						<div class="card-header">
							<p><span class="price">£70</span><br>Sponsor a news story</p>
						</div>
						<div class="card-body">
							<p>Sponsor our 'Editorial Team of Two' for half a day - that's the time it takes to research and write a news story such as the opening of a new shop or launch of a grassroots campaign.</p>
						</div>
					</div>
				</a>
				<a class="patron-link" href="https://romanroadlondon.com/campaign/donate/">
					<div class="card card-single">
						<div class="card-header">
							<p><span class="price">£100</span><br>Sponsor an article</p>
						</div>
						<div class="card-body">
							<p>Sponsor our ‘Editorial Team of Two’ for one day – that’s how long it takes to research and write one of our longer features, helping raise awareness of more nuanced topics.</p>
						</div>
					</div>
				</a>
				<a class="patron-link" href="https://romanroadlondon.com/campaign/donate/">
					<div class="card card-single">
						<div class="card-header">
							<p><span class="price">£550</span><br>Sponsor a newsletter</p>
						</div>
						<div class="card-body">
							<p>Sponsor our 'Editorial Team of Two' for one week - that's how long it takes to plan, produce and write all the news, features and events for our weekly newsletter, The Slice.</p>
						</div>
					</div>
				</a>
				<a class="patron-link" href="https://romanroadlondon.com/campaign/donate/">
					<div class="card card-single">
						<div class="card-header">
							<p><span class="price">£1,000</span><br>Sponsor TWO newsletters!</p>
						</div>
						<div class="card-body">
							<p>Sponsor two issues of our weekly newsletter, The Slice. That's two weeks of work for our 'Editorial Team of Two'.</p>
						</div>
					</div>
				</a>
			</div>

		</div>
		
</div><!-- #content -->
	
<style>
	@keyframes expandWidth {
		0% {
			width: 0%;
		}
		100% {
			width: <?php echo $percentage; ?>%;
		}
	}
	#bar {
		width: <?php echo $percentage; ?>%;
		height: 10px;
		margin-top: 5px;
		background-color: #7bdcb5;
		animation-name: expandWidth;
		animation-duration: 3s;
	}

	#progress {
		width: 100%;
		background-color: rgb(235, 235, 235);
	}

</style>

<script>

	// Count numbers animation
	const animationDuration = 2000;
	const frameDuration = 1000 / 60;
	const totalFrames = Math.round( animationDuration / frameDuration );
	// An ease-out function that slows the count as it progresses
	const easeOutQuad = t => t * ( 2 - t );
			

		// The animation function, which takes an Element
	function animateCountUp(el) {
		let frame = 0;
		let countTo = 0;
		countTo = parseInt(el.dataset.to, 10);
		// Start the animation running 60 times per second
		const counter = setInterval( () => {
			frame++;
			// Calculate our progress as a value between 0 and 1
			// Pass that value to our easing function to get our
			// progress on a curve
			const progress = easeOutQuad( frame / totalFrames );
			// Use the progress value to calculate the current count
			const currentCount = Math.round( countTo * progress );

			// If the current count has changed, update the element
			if ( parseInt(el.innerHTML) !== currentCount ) {
				el.innerHTML = numberWithCommas(currentCount);
			}
			
			
			// If we’ve reached our last frame, stop the animation
			if ( frame === totalFrames ) {
				clearInterval( counter );
			}
		}, frameDuration );
	};

	function numberWithCommas(x) {
		return x.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
	}

	document.addEventListener('DOMContentLoaded', () => {
		document.querySelectorAll('.count-number').forEach(animateCountUp)
	});
</script>
<script src="https://code.iconify.design/1/1.0.7/iconify.min.js"></script>
<?php

get_footer();

?>