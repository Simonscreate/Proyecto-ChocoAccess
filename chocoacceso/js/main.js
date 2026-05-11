(function ($) {
    "use strict";

    // Navegación suave
    $(".nav-link").on('click', function (event) {
        if (this.hash !== "") {
            event.preventDefault();
            var hash = this.hash;
            $('html, body').animate({
                scrollTop: $(hash).offset().top - 70
            }, 800);
        }
    });

    // Iniciar animaciones WOW
    new WOW().init();

})(jQuery);