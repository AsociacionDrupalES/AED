(function ($) {
  Drupal.behaviors.mainMenu = {
    attach: function (context, settings) {

      $(context).find('#block-aed-th-main-menu').once('main-menu').each(function () {
        var $this = $(this);
        var $hiddenMenu = $this.find('.hidden-menu');
        var $menuOpen = $this.find('.menu-open');
        var $menuClose = $hiddenMenu.find('.menu-close');

        $menuOpen.on('click', function (e) {
          e.preventDefault();
          $hiddenMenu.addClass('open');
        });

        $menuClose.on('click', function (e) {
          e.preventDefault();
          $hiddenMenu.removeClass('open');
        });

        // ESC key support.
        // ----------------
        $(document).keyup(function (e) {
          if (e.keyCode === 27) {
            $menuClose.trigger('click');
          }
        });

        // Add submenu support.
        // --------------------
        var $openSubmenu = $('<span class="open-submenu">');

        $openSubmenu.on('click', function (e) {
          $(this).siblings('ul').toggleClass('js-open');
        });

        $openSubmenu.appendTo($this.find('.menu-item--expanded'));

      });

      $(context).find('[href="/user/login"]').once('main-menu').each(function () {
        var $a = $(this);
        var $loginBlock = $(context).find('.user-login-overlay');
        var $closeLoginBlock = $loginBlock.find('.close');

        $a.on('click', function (e) {
          e.preventDefault();
          $loginBlock.removeClass('visually-hidden');
        });

        $closeLoginBlock.on('click', function (e) {
          e.preventDefault();
          $loginBlock.addClass('visually-hidden');
        });

        $(document).keyup(function (e) {
          if (e.keyCode === 27) {
            $closeLoginBlock.trigger('click');
          }
        });

      });


    }
  };
})(jQuery);