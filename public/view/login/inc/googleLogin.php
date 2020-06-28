<?php
if (defined("GOOGLELOGINCLIENTID") && !empty(GOOGLELOGINCLIENTID) && defined('GOOGLEENTITY') && !empty(GOOGLEENTITY)) {
    if (file_exists(PATH_HOME . "entity/cache/" . GOOGLEENTITY . ".json")) {
        ?>
        <div class="g-signin2" data-onsuccess="loginUserGoogleBase"></div>
        <script src="https://apis.google.com/js/platform.js" async defer></script>
        <meta name="google-signin-client_id" content="<?= GOOGLELOGINCLIENTID ?>">
        <script>
            function exeLoginGoogle(email, senha, social, token) {
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
                                let destino = "dashboard";
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

            function loginUserGoogleBase(googleUser) {
                let profile = googleUser.getBasicProfile();

                profile = {
                    id: profile.getId(),
                    name: profile.getName(),
                    email: profile.getEmail(),
                    image: profile.getImageUrl(),
                    token: googleUser.wc.id_token
                }

                return AJAX.post("checkUserLoginSocial", Object.assign({"social": "google"}, profile)).then(result => {
                    if (isEmpty(result)) {
                        return exeLoginGoogle(profile.name, profile.id, "google", profile.token);
                    } else {
                        console.log(result);
                        toast("Erro ao cadastrar usuário! Verifique o console para mais detalhes", 5000, "toast-error");
                    }
                });

                /**
                 * after work with the data user, logout
                 */
                gapi.auth2.getAuthInstance().signOut();
            }
        </script>
        <style>
            .g-signin2 > .abcRioButton {
                height: 40px !important;
                padding: 3px 5px;
                width: 160px !important;
                border-radius: 3px;
            }

            .g-signin2 {
                float: left;
                margin-bottom: 10px
            }
        </style>
        <?php
    } else {
        echo '<script>toast("google login: Entidade `' . GOOGLEENTITY . '` não existe", 4000, "toast-error");</script>';
    }
}