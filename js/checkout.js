/**
 * @file
 */

Drupal.behaviors.stripe_registration = {
  attach: function (context, settings) {
    Stripe.setPublishableKey(drupalSettings.stripe_registration.publishable_key);

    (function ($) {
      // Add client site validation to payment fields.
      $(context).find('[data-numeric]').payment('restrictNumeric');
      $(context).find('#edit-card-number').payment('formatCardNumber');
      $(context).find('#edit-exp').payment('formatCardExpiry');
      $(context).find('#edit-cvc').payment('formatCardCVC');

      // Define function for indicating client side error.
      $.fn.toggleInputError = function (erred) {
        this.toggleClass('error', erred);
        return this;
      };

      // Handle form submission.
      $form = $('input[value="' + drupalSettings.stripe_registration.form_id + '"]').parents('form');
      $form.submit(function (event) {

        // Toggle invalid fields.
        var cardType = $.payment.cardType($('#edit-card-number').val());
        $('#edit-card-number').toggleInputError(!$.payment.validateCardNumber($('#edit-card-number').val()));
        $('#edit-exp').toggleInputError(!$.payment.validateCardExpiry($('#edit-exp').payment('cardExpiryVal')));
        $('#edit-cvc').toggleInputError(!$.payment.validateCardCVC($('#edit-cvc').val(), cardType));

        // If all fields are valid, get token from Stripe.
        // @todo what if these fields are valid but drupal has a validation error on the backend?
        if ($.payment.validateCardNumber($('#edit-card-number').val())
          && $.payment.validateCardExpiry($('#edit-exp').payment('cardExpiryVal'))
          && $.payment.validateCardCVC($('#edit-cvc').val(), cardType)) {

          // Prevent multiple clicks.
          $form.find('.form-submit').prop('disabled', true);

          // @see https://groups.google.com/a/lists.stripe.com/forum/#!topic/api-discuss/_t1Z4Fy5xdI
          // We do not simply pass $form here because our expiration fields are combined.
          expiration = $('#edit-exp').payment('cardExpiryVal');
          Stripe.card.createToken({
            number: $('#edit-card-number').val(),
            cvc: $('#edit-cvc').val(),
            exp_month: (expiration.month || 0),
            exp_year: (expiration.year || 0)
          }, stripeResponseHandler);

        }

        // Prevent the form from being submitted. It will be submitted by stripeResponseHandler instead.
        return false;
      });

      /**
       * This function will be called by Stripe.JS once Stripe sends us a response.
       */
      function stripeResponseHandler(status, response) {
        // Grab the form:
        $form = $('input[value="' + drupalSettings.stripe_registration.form_id + '"]').parents('form');

        if (response.error) {
          // Show the errors on the form.
          $form.find('#edit-stripe-messages').text(response.error.message);
          // Re-enable submission.
          $form.find('.form-submit').prop('disabled', false);
        }
        else {
          // Get the token ID.
          var token = response.id;
          $('input[name="stripeToken"]').val(token);

          // Submit the form.
          $form.get(0).submit();
        }
      };
    })(jQuery);

  }
};
