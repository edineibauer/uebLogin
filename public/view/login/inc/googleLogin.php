<?php
if (defined("GOOGLELOGINCLIENTID") && !empty(GOOGLELOGINCLIENTID)) { ?>
    <div class="g-signin2" data-onsuccess="loginUserGoogleBase"></div>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <meta name="google-signin-client_id" content="<?=GOOGLELOGINCLIENTID?>">
    <script>
        function loginUserGoogleBase(googleUser) {
            let profile = googleUser.getBasicProfile();
            let user = {
                id: profile.getId(),
                name: profile.getName(),
                email: profile.getEmail(),
                image: profile.getImageUrl(),
                token: googleUser.wc.id_token
            }
            if (typeof loginGoogle === "function") {
                loginGoogle(user);
            } else {
                toast("Admin! Implemente a função `loginGoogle(profile)` em seu código para fazer algo com os dados retornados!", 10000, "toast-warning")
                console.log("Admin! Implemente a função `loginGoogle(profile)` em seu código para fazer algo com os dados retornados!", user);
            }
            /**
             * after work with the data user, logout
             */
            gapi.auth2.getAuthInstance().signOut();
        }
    </script>
    <style>
        .g-signin2 > .abcRioButton {
            height: 40px!important;
            padding: 3px 5px;
            width: 160px!important;
            border-radius: 3px;
        }

        .g-signin2 {
            float: left;
            margin-bottom: 10px
        }
    </style>
    <?php
}