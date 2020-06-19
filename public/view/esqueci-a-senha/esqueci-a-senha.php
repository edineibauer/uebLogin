<?php
if (empty($_SESSION['userlogin'])) {
    ?>
    <div class='row font-large' style="z-index:100; position: relative; max-width: 450px; margin: auto">
        <div class="clear"><br></div>
        <a href="<?=HOME?>" class="container align-center upper panel color-text-grey padding-64 padding-bottom" id="logoLogin">
            <img src='<?= HOME ?>assetsPublic/img/favicon-256.png?v=<?= VERSION ?>' height='60'
                 style='height: 60px;float: initial;margin:initial'>
        </a>
        <div class='container align-center upper panel font-light theme-text-aux'>Recuperar senha</div>
        <div class="row z-depth-2 radius color-white padding-medium margin-medium padding-4">
            <div class="panel">
                <label class="font-small" for="recovery-email">Digite seu Email</label>
                <input id="recovery-email" type="email">
            </div>
            <div class="row padding-medium margin-bottom padding-4" style="margin-top: 3px;">
                <button class="col radius s-font-medium theme-d2 hover-opacity-off opacity hover-shadow"
                        id="send-email-recover"
                        style="float:initial!important; padding: 10px 50px" onclick="recoveryEmail();">
                    Enviar email de recuperação
                </button>
            </div>
        </div>

        <div class="row upper color-text-grey font-small padding-medium padding-4">
            <a href="<?= defined('HOME') ? HOME : "" ?>login"
               class="left btn color-white color-text-grey radius hover-opacity-off opacity" style="text-decoration: none">
                fazer login
            </a>
        </div>
        <div class="row clear"><br><br><br><br></div>
    </div>

    <!--Waves Container-->
    <svg class="waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
         viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
        <defs>
            <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
        </defs>
        <g class="parallax">
            <use xlink:href="#gentle-wave" x="48" y="0" fill="rgba(255,255,255,0.7" />
            <use xlink:href="#gentle-wave" x="48" y="3" fill="rgba(255,255,255,0.5)" />
            <use xlink:href="#gentle-wave" x="48" y="5" fill="rgba(255,255,255,0.3)" />
            <use xlink:href="#gentle-wave" x="48" y="7" fill="#fff" />
        </g>
    </svg>
    <!--Waves end-->
    <?php
}