<?php
if (empty($_SESSION['userlogin'])) {
    ob_start();
    ?>
    <div class='row font-large' style="max-width: 750px; margin: auto">
        <div class="clear"><br></div>
        <div class='container align-center upper panel font-light color-text-grey'>Cadastro de Usuário</div>
        <br>
        <div class="panel" id="cadatro-user"></div>

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
    $data['data'] = ob_get_contents();
    ob_end_clean();
} else {
    $data['response'] = 3;
    $data['data'] = HOME . "dashboard";
}