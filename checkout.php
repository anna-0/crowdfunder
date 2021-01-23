
<script type="text/javascript">

  // Create an instance of the Stripe object with your publishable API key
  const stripe = Stripe("pk_test_51HnlnCGwUojdYrl58MuM6JARYQsidbu7XzHqy1HQlM9NwyA4f4KrAhvW6bIcfvwSyYG0wzGR38wBV3gtFvnL2XPF00Ql0aOmr7");
  const checkoutButton = document.getElementById("checkout-button");
  const checkbox = document.querySelector('#postname');
  let postname;

  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const priceId = urlParams.get('p');

  checkoutButton.addEventListener("click", function(evt) {
    if (checkbox.checked) {
      postname = true;
    } else {postname = false;}
      createCheckoutSession(priceId, postname).then(function(data) {
        // Call Stripe.js method to redirect to the new Checkout page
        stripe
          .redirectToCheckout({
            sessionId: data.sessionId
          })
          .then(handleResult);
      });
    });

    // Create a Checkout Session with the selected plan ID
  let createCheckoutSession = function(priceId, postname) {
  return fetch("/create-session.php", {
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