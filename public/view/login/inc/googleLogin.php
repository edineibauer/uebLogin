<?php
if (defined("GOOGLELOGINCLIENTID") && !empty(GOOGLELOGINCLIENTID)) { ?>
    <div class="g-signin2" data-onsuccess="loginUserGoogleBase"></div>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <meta name="google-signin-client_id" content="<?=GOOGLELOGINCLIENTID?>">
    <script>
        function loginUserGoogleBase(googleUser) {
            let profile = googleUser.getBasicProfile();
            if (typeof loginGoogle === "function") {
                loginGoogle(googleUser.getBasicProfile());
            } else {
                toast("Admin! Implemente a função `loginGoogle(profile)` em seu código para fazer algo com os dados retornados!", 10000, "toast-warning")
                console.log("Admin! Implemente a função `loginGoogle(profile)` em seu código para fazer algo com os dados retornados!", profile);
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
            width: 120px;
            border-radius: 3px;
        }

        .g-signin2 {
            float: left;
            margin-bottom: 10px
        }
    </style>
    <?php
}