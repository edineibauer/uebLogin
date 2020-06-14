<?php

if (defined("GOOGLELOGINCLIENTID") && !empty(GOOGLELOGINCLIENTID)) { ?>
    <div class="g-signin2" data-onsuccess="onSignIn"></div>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <meta name="google-signin-client_id" content="<?=GOOGLELOGINCLIENTID?>">
    <script>
        var googleLogin = 0;

        function onSignIn(googleUser) {
            if (googleLogin === 0) {
                gapi.auth2.getAuthInstance().signOut();

            } else {
                let profile = googleUser.getBasicProfile();
                toast("Implemente a função `onSignIn`", 5000, "toast-warning");

                console.log(profile);

                /*//Verifica se este usuário já existe
                getJSON(HOME + "app/find/clientes/email/" + profile.getEmail()).then(r => {
                    if (!isEmpty(r.clientes)) {
                        //se já existe, tenta logar com email e código
                        exeLogin(profile.getEmail(), profile.getId())
                    } else {

                        //se não existe, cria novo
                        db.exeCreate("clientes", {
                            nome: profile.getName(),
                            email: profile.getEmail(),
                            imagem_url: profile.getImageUrl(),
                            senha: profile.getId(),
                            ativo: 1
                        }).then(result => {

                            //depois de criar, tenta logar
                            if (result.db_errorback === 0)
                                exeLogin(result.email, profile.getId())
                        })
                    }
                });*/
            }
        }

        $("#app").on("click", ".abcRioButtonContentWrapper", function () {
            googleLogin = 1;
        });
    </script>
    <?php
}