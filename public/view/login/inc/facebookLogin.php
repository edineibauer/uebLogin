<?php
if (defined("FACEBOOKAPLICATIONID") && !empty(FACEBOOKAPLICATIONID)) {
    if (defined('FACEBOOKENTITY') && !empty(FACEBOOKENTITY) && file_exists(PATH_HOME . "entity/cache/" . FACEBOOKENTITY)) {
        ?>
        <script>
            /**
             * Login with the user facebook
             */
            async function loginUserFBBase(token) {
                let user = await getUserFB();
                user.image = user.picture.data.url;
                user.token = token;
                delete (user.picture);

                if (typeof loginFacebook === "function") {
                    loginFacebook(user);
                } else {
                    toast("Admin! Implemente a função `loginFacebook(profile)` em seu código para fazer algo com os dados retornados!", 10000, "toast-warning");
                    console.log("Admin! Implemente a função `loginFacebook(profile)` em seu código para fazer algo com os dados retornados!", user);
                }
            }

            /**
             * Get the facebook perfil
             */
            async function getUserFB() {
                return new Promise(s => {
                    FB.api(
                        '/me',
                        'GET',
                        {"fields": "id,name,picture{url},email"},
                        function (response) {
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

                $(".facebook-login").off("click").on("click", function () {
                    FB.getLoginStatus(function (response) {
                        if (response.status === 'connected' && response.authResponse) {
                            toast("acessando...", "toast-success");
                            loginUserFBBase(response.authResponse.accessToken);
                        } else {
                            FB.login(function (response) {
                                if (response.authResponse)
                                    loginUserFBBase(response.authResponse.accessToken);
                            });
                        }
                    });
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

        <button class="facebook-login">
            facebook login
        </button>

        <style>
            .facebook-login {
                background-image: url("<?=HOME . VENDOR?>login/public/assets/img/facebook.png");
                background-size: contain;
                background-repeat: no-repeat;
                background-position: left center;
                background-color: #1877f2;
                height: 40px;
                color: #ffffff;
                box-shadow: 0 2px 4px 0 rgba(0, 0, 0, .25);
                border: none;
                border-radius: 4px !important;
                font-size: 13px !important;
                width: 160px;
                float: right;
                padding-left: 30px !important;
            }
        </style>
        <?php
    } else {
        echo '<script>toast("facebook login: Entidade `' . FACEBOOKENTITY . '` não existe", 4000, "toast-error");</script>';
    }
}