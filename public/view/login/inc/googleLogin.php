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
            }
        }

        $("#app").on("click", ".abcRioButtonContentWrapper", function () {
            googleLogin = 1;
        });
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