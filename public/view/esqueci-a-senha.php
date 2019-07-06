<?php
ob_start();
?>
    <div class='row font-large' style="max-width: 450px; margin: auto">
        <div class="clear"><br><br><br></div>
        <div class='container align-center upper panel font-light color-text-grey'>Recuperação de Senha</div>
        <div class="row z-depth-2 color-white padding-medium margin-medium padding-4">
            <div class="panel">
                <label class="font-small" for="recovery-email">Digite seu Email</label>
                <input id="recovery-email" type="email">
            </div>
        </div>
        <div class="row padding-medium padding-4" style="margin-top: 3px;">
            <button class="col btn-large s-font-medium theme-d2 hover-opacity-off opacity hover-shadow"
                    id="send-email-recover"
                    style="float:initial!important;" onclick="recoveryEmail();">
                Enviar Email de Recuperação
            </button>
        </div>

        <div class="row clear"><br></div>
        <div class="row upper color-text-grey font-small padding-medium padding-4">
            <a href="<?= defined('HOME') ? HOME : "" ?>login"
               class="left btn color-white color-text-grey hover-opacity-off opacity" style="text-decoration: none">
                fazer login
            </a>
        </div>
        <div class="row clear"><br><br><br><br></div>
    </div>

<?php
$data['data'] = ob_get_contents();
ob_end_clean();