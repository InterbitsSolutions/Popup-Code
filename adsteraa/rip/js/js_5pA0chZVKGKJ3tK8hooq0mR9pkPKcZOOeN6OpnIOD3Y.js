var Ricardo = Ricardo || {};
(function ($, window, Ricardo) {
    window.RicardoUser = function() {
        var app = this;

        app.isLoggedIn = false;
        app.loggedInCallbacks = [];
        app.notLoggedInCallbacks = [];

        app.InitUserLoggedIn = function() {
            var apiUserIsLogged = "/api/user/islogged";
            $.ajax({
                url: apiUserIsLogged,
                dataType: 'json'
            }).done(function (response) {
                if(response) {
                    app.isLoggedId = true;
                    app.executeLoggedInCallbacks();
                }
                else {
                    app.executeNotLoggedInCallbacks();
                }
            });
        };

        app.addLoggedInCallback = function(callback) {
            app.loggedInCallbacks.push(callback);
        };

        app.addNotLoggedInCallback = function(callback) {
            app.notLoggedInCallbacks.push(callback);
        };

        app.executeLoggedInCallbacks = function() {
            $(app.loggedInCallbacks).each(function(index, callback) {
                callback();
            });
        };

        app.executeNotLoggedInCallbacks = function() {
            $(app.notLoggedInCallbacks).each(function(index, callback) {
                callback();
            });
        };
    };

    Ricardo.User = new RicardoUser();
}(jQuery, window, Ricardo));;
