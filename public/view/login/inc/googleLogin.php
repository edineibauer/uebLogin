<?php
if (defined("GOOGLELOGINCLIENTID") && !empty(GOOGLELOGINCLIENTID) && defined('GOOGLEENTITY') && !empty(GOOGLEENTITY)) {
    if (file_exists(PATH_HOME . "entity/cache/" . GOOGLEENTITY . ".json")) {
        ?>
        <div id="googleBtnLogin" onclick="appStart()">google</div>
        <script src="https://apis.google.com/js/platform.js" defer async></script>
        <script>
            var auth2; // The Sign-In object.

            /**
             * Calls startAuth after Sign in V2 finishes setting up.
             */
            var appStart = function () {
                gapi.load('auth2', initSigninV2);
            };

            /**
             * Initializes Signin v2 and sets up listeners.
             */
            var initSigninV2 = function () {
                auth2 = gapi.auth2.init({
                    client_id: '<?=GOOGLELOGINCLIENTID?>',
                    scope: 'profile'
                });

                // Listen for sign-in state changes.
                auth2.isSignedIn.listen(signinChanged);

                // Listen for changes to current user.
                auth2.currentUser.listen(updateGoogleUser);

                // Sign in the user if they are currently signed in.
                if (auth2.isSignedIn.get() === !0)
                    auth2.signIn();

                // Start with the current live values.
                refreshValues();
            };


            /**
             * Listener method for sign-out live value.
             *
             * @param {boolean} val the updated signed out state.
             */
            var signinChanged = function (val) {
                console.log('Signin state changed to ', val);
            };

            /**
             * Updates the properties in the Google User table using the current user.
             */
            var updateGoogleUser = function (googleUser) {
                if (googleUser && typeof googleUser.Qt !== "undefined") {
                    let profile = {
                        id: googleUser.Qt.VU,
                        name: googleUser.Qt.Bd,
                        email: googleUser.Qt.Au,
                        image: googleUser.Qt.cL,
                        token: googleUser.getAuthResponse().access_token
                    };

                    return AJAX.post("checkUserLoginSocial", Object.assign({"social": "google"}, profile)).then(result => {
                        if (isEmpty(result)) {
                            return exeLoginGoogle(profile.email, profile.id, "google", googleUser.getAuthResponse().id_token);
                        } else {
                            console.log(result);
                            toast("Erro ao cadastrar usuário! Verifique o console para mais detalhes", 5000, "toast-error");
                        }
                    });
                }
            };

            function exeLoginGoogle(email, senha, social, token) {
                if (loginFree) {
                    $("#login-card").loading();
                    loginFree = !1;
                    AJAX.post('login', {
                        user: email,
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
             * Retrieves the current user and signed in states from the GoogleAuth
             * object.
             */
            var refreshValues = function () {
                if (auth2) {
                    toast("conectando...", 10000, "toast-infor");
                    updateGoogleUser(auth2.currentUser.get());
                }
            }
        </script>
        <style>
            #googleBtnLogin {
                height: 40px !important;
                padding: 8px 5px;
                text-align: center;
                width: 125px !important;
                border-radius: 4px;
                font-size: 13px !important;
                float: left;
                margin-bottom: 10px;
                color: #666666;
                border: solid 1px #eeeeee;
                box-shadow: 3px 5px 7px -6px #b7b7b7;
            }
        </style>
        <?php
    } else {
        echo '<script>toast("google login: Entidade `' . GOOGLEENTITY . '` não existe", 4000, "toast-error");</script>';
    }
}