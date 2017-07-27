jQuery(document).ready(function () {

    if ($('.userNav').length) {
        Ricardo.User.addLoggedInCallback(function() {
            var lang        = $('html').attr('lang');
            var apiUserMenu = "/api/user/menu/" + lang;

            $.ajax({
                url     : apiUserMenu,
                dataType: "html"
            }).success(function (htmlContent) {
                $('.userNav').replaceWith(htmlContent);
                $('.userNav [data-action="popover"]').popover();

                if(mobileSize) {
                    Ricardo.Theme.InitFancyBoxUserNav();
                }
            });
        });
    }
});;
