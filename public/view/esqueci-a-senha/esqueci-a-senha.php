<div class='row font-large' style="z-index:100; position: relative; max-width: 450px; margin: auto">
    <div class="clear"><br></div>
    <a href="<?=HOME?>" class="container align-center upper panel color-text-grey padding-64 padding-bottom" id="logoLogin">
        <img src='<?= HOME ?>assetsPublic/img/favicon-256.png?v=<?= VERSION ?>' height='60'
             style='height: 60px;float: initial;margin:initial'>
    </a>
    <div class='container align-center upper panel font-light'>Recuperar senha</div>
    <div class="col-12 z-depth-2 radius padding-medium margin-medium padding-4" style="background: var(--colorBox)">
        <div class="panel">
            <label class="font-small" for="recovery-email">Digite seu Email</label>
            <input id="recovery-email" type="email" style="color:var(--colorText)">
        </div>
        <div class="row padding-medium margin-bottom padding-4" style="margin-top: 3px;">
            <button class="col radius s-font-medium theme-d2 hover-opacity-off opacity hover-shadow"
                    id="send-email-recover"
                    style="float:initial!important; padding: 10px 50px" onclick="recoveryEmail();">
                Enviar email de recuperação
            </button>
        </div>
    </div>

    <div class="col-12 upper color-text-grey padding-medium padding-4">
        <a href="<?= defined('HOME') ? HOME : "" ?>login"
           class="left btn radius hover-opacity-off opacity" style="font-size:12px;text-decoration: none">
            fazer login
        </a>
    </div>
    <div class="row clear"><br><br><br><br></div>
</div>