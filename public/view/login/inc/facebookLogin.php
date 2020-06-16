<?php
if (defined("FACEBOOKAPLICATIONID") && !empty(FACEBOOKAPLICATIONID)) {
    ?>
    <script>
        window.fbAsyncInit = function () {
            FB.init({
                appId: '<?=FACEBOOKAPLICATIONID?>',
                cookie: true,
                xfbml: true,
                version: '<?=defined('FACEBOOKVERSION') && !empty(FACEBOOKVERSION) ? FACEBOOKVERSION : "v7.0"?>'
            });

            FB.AppEvents.logPageView();

            /**
             * Obter status de login
             */
            FB.getLoginStatus(function (response) {
                console.log("status: ", response);
                statusChangeCallback(response);
            });

            /**
             * Função a ser disparada depois que obtiver a resposta do facebook
             */
            function checkLoginState() {
                FB.getLoginStatus(function (response) {
                    console.log("login: ", response);
                    statusChangeCallback(response);
                });
            }
        };

        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {
                return;
            }
            js = d.createElement(s);
            js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

    </script>

    <div class="fb-login-button" data-size="large" data-button-type="login_with" data-layout="default"
         data-auto-logout-link="false" data-use-continue-as="true" data-width="" onlogin="checkLoginState();"></div>
    <style>
        .fb-login-button {
            float: right;
        }
    </style>
    <?php
}