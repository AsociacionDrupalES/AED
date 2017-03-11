(function ($) {
  Drupal.behaviors.aedthBehavior = {
    attach: function (context, settings) {
      ////////////////

      // Agrego comportamiento mobile a facets en pagina de busqueda
      $(".path-videos .region-left > div").wrapAll("<div class='facet-wrapper'/>");
      var $foo = $(".path-videos .region-left");
      $foo.prepend("<h2 class='trigger-button'>Filtrar</h2>");

      $(".path-videos .region-left > h2").on({
        click: function () {
          $(this).toggleClass("active");
          $(".facet-wrapper").toggleClass("open")
        }
      });

      ////////////////
    }
  }
})
(jQuery);
