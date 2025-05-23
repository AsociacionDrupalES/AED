(function (Drupal, $, once) {
  Drupal.behaviors.ppssGenerateLink = {
    attach: function (context, settings) {
      /////////////////
      $(once('data-agreement-plan-id', '[data-agreement-plan-id]')).each(function (e) {
        var $placeholder = $(this);
        var agreementPlanId = $(this).data('agreement-plan-id');
        var agreementStartDate = $(this).data('agreement-start-date');

        // Time to ask for the real link.
        $.ajax({
          type: 'POST',
          cache: false,
          async: true,
          url: drupalSettings.ppssFieldFormatter.url,
          data: {
            id: agreementPlanId,
            startDate: agreementStartDate
          }
        }).always(function (data) {
          $placeholder.replaceWith(data.res);
        });

      });
      /////////////////
    }
  };

})(Drupal, jQuery, once);