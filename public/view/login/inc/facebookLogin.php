<?php
if (defined("FACEBOOKAPLICATIONID") && !empty(FACEBOOKAPLICATIONID) && defined('FACEBOOKENTITY') && !empty(FACEBOOKENTITY)) {
    if (file_exists(PATH_HOME . "entity/cache/" . FACEBOOKENTITY . ".json")) {
        ?>
        <script>
            function exeLoginFacebook(email, senha, social, token) {
                if (loginFree) {
                    $("#login-card").loading();
                    loginFree = !1;
                    AJAX.post('login', {
                        email: email,
                        pass: senha,
                        social: social,
                        token: token
                    }).then(g => {
                        if (typeof g === "string") {
                            loginFree = !0;
                            navigator.vibrate(100);
                            if (g !== "no-network")
                                toast(g, 3000, "toast-error")
                        } else {
                            toast("Seja bem vindo, acessando...", 15000, "toast-success");
                            setCookieUser(g).then(() => {
                                let destino = "index";
                                if (!!localStorage.redirectOnLogin) {
                                    destino = localStorage.redirectOnLogin;
                                    localStorage.removeItem("redirectOnLogin");
                                }
                                location.href = destino;
                            })
                        }
                    });
                }
            }

            /**
             * Login with the user facebook
             */
            async function loginUserFBBase(token) {
                let profile = await getUserFB();
                profile.image = profile.picture.data.url;
                profile.token = token;
                delete (profile.picture);

                return AJAX.post("checkUserLoginSocial", Object.assign({"social": "facebook"}, profile)).then(result => {
                    if (isEmpty(result)) {
                        return exeLoginFacebook(profile.name, profile.id, "facebook", profile.token);
                    } else {
                        console.log(result);
                        toast("Erro ao cadastrar usuário! Verifique o console para mais detalhes", 5000, "toast-error");
                    }
                });
            }

            /**
             * Get the facebook perfil
             */
            async function getUserFB() {
                return new Promise(s => {
                    FB.api(
                        '/me',
                        'GET',
                        {"fields": "id,name,email,picture{url}"},
                        function (response) {
                            s(response);
                        }
                    );
                });
            }

            window.fbAsyncInit = function () {
                FB.init({
                    appId: '<?=FACEBOOKAPLICATIONID?>',
                    cookie: false,
                    xfbml: true,
                    version: '<?=defined('FACEBOOKVERSION') && !empty(FACEBOOKVERSION) ? FACEBOOKVERSION : "v7.0"?>'
                });

                $("#app").off("click", ".facebook-login").on("click", ".facebook-login", function () {
                    FB.getLoginStatus(function (response) {
                        if (response.status === 'connected' && response.authResponse) {
                            toast("acessando...", "toast-success");
                            loginUserFBBase(response.authResponse.accessToken);
                        } else {
                            FB.login(function (response) {
                                if (response.authResponse)
                                    loginUserFBBase(response.authResponse.accessToken);
                            }, {scope: 'email'});
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
                background-image: url("<?=SERVER . VENDOR?>login/public/assets/img/facebook.png");
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