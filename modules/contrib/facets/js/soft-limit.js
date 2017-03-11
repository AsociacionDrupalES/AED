/**
 * @file
 * Provides the soft limit functionality.
 */

(function ($) {

  "use strict";

  Drupal.behaviors.facetSoftLimit = {
    attach: function (context, settings) {
      if (settings.facets.softLimit != undefined) {
        $.each(settings.facets.softLimit, function (facet, limit) {
          Drupal.facets.applySoftLimit(facet, limit);
        })
      }
    }
  };

  Drupal.facets = Drupal.facets || {};

  /**
   * Applies the soft limit UI feature to a specific facets list.
   */
  Drupal.facets.applySoftLimit = function (facet, limit) {
    var zero_based_limit = limit - 1;
    var facetsList = $('ul[data-drupal-facet-id="' + facet + '"]');

    // Hide facets over the limit.
    facetsList.find('li:gt(' + zero_based_limit + ')').once().hide();

    // Add "Show more" / "Show less" links.
    facetsList.once().filter(function () {
      return $(this).find('li').length > limit;
    }).each(function () {
      var facet = $(this);
      $('<a href="#" class="facets-soft-limit-link"></a>').text(Drupal.t('Show more')).click(function () {
        if (facet.find('li:hidden').length > 0) {
          facet.find('li:gt(' + zero_based_limit + ')').slideDown();
          $(this).addClass('open').text(Drupal.t('Show less'));
        }
        else {
          facet.find('li:gt(' + zero_based_limit + ')').slideUp();
          $(this).removeClass('open').text(Drupal.t('Show more'));
        }
        return false;
      }).insertAfter($(this));
    });
  };

})(jQuery);
