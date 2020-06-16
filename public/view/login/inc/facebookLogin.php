<?php
if (defined("FACEBOOKAPLICATIONID") && !empty(FACEBOOKAPLICATIONID)) {
    ?>
    <script>
        /**
         * Login with the user facebook
         */
        async function loginUserFBBase() {
            if(typeof loginFacebook === "function") {
                loginFacebook(await getUserFB());
            } else {
                toast("Admin! Implemente a função `loginFacebook(profile)` em seu código para fazer algo com os dados retornados!", 10000, "toast-warning");
                console.log("Admin! Implemente a função `loginFacebook(profile)` em seu código para fazer algo com os dados retornados!", await getUserFB());
            }

            /**
             * after work with the data user, logout
             */
            FB.logout();
        }

        /**
         * Get the facebook perfil
         */
        async function getUserFB() {
            return new Promise(s => {
                FB.api(
                    '/me',
                    'GET',
                    {"fields":"id,name,picture{url},email"},
                    function(response) {
                        s(response);
                    }
                );
            });
        }

        window.fbAsyncInit = function () {
            FB.init({
                appId: '<?=FACEBOOKAPLICATIONID?>',
                cookie: true,
                xfbml: true,
                version: '<?=defined('FACEBOOKVERSION') && !empty(FACEBOOKVERSION) ? FACEBOOKVERSION : "v7.0"?>'
            });
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
         data-auto-logout-link="false" data-use-continue-as="true" data-width="" onlogin="loginUserFBBase();"></div>

    <style>
        .fb-login-button {
            float: right;
        }
    </style>
    <?php
}