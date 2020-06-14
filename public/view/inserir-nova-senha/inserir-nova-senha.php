<?php
$code = explode('get/inserir-nova-senha/', $_SERVER['REQUEST_URI']);
if(!empty($code[1])){
    $code = $code[1];
    ?>
        <div class='row font-large' style="max-width: 450px; margin: auto">
            <div class="clear"><br><br><br></div>
            <div class='container align-center upper panel font-light color-text-grey'>Redefinir Senha</div>
            <div class="row z-depth-2 color-white">
                <div class="panel">
                    <label class="font-small" for="nova-senha">Digite Nova Senha</label>
                    <input id="nova-senha" type="password">
                </div>
                <div class="panel">
                    <label class="font-small" for="nova-senha-confirm">Digite Novamente</label>
                    <input id="nova-senha-confirm" type="password">
                </div>
                <input type="hidden" id="code" value="<?= $code ?>"/>
            </div>
            <div class="card" style="margin-top: 3px;">
                <button class="col btn-large theme-d1 opacity hover-shadow hover-opacity-off"
                        style="float:initial!important;"
                        onclick="newPassword();">
                    Confirmar Nova Senha
                </button>
            </div>

            <div class="row clear"><br></div>
            <div class="row upper color-text-grey font-small">
                <a href="<?= defined('HOME') ? HOME : "" ?>login"
                   class="left btn color-white color-text-grey hover-opacity-off opacity" style="text-decoration: none">
                    fazer login
                </a>
            </div>
            <div class="row clear"><br><br><br><br></div>
        </div>
    <?php
} else {
    ?>
    <div class='row font-large' style="max-width: 450px; margin: auto">
        <div class="clear"><br><br><br></div>
        <div class='container align-center upper panel font-light color-text-grey'>ID de redefinição inválido!</div>

        <div class="row clear"><br></div>
        <div class="row upper color-text-grey font-small">
            <a href="<?= defined('HOME') ? HOME : "" ?>esqueci-a-senha"
               class="left btn color-white color-text-grey hover-opacity-off opacity" style="text-decoration: none">
                redefinir senha de usuário
            </a>
        </div>
        <div class="row clear"><br><br><br><br></div>
    </div>
    <?php
}