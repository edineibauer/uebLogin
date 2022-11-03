<div class='row container font-large' style="z-index:100;position:relative;max-width: 470px; margin: auto">
    <a href="<?= HOME ?>" class="container align-center upper panel color-text-grey padding-64 s-padding-0 padding-bottom"
       id="logoLogin">
        <img src='<?= HOME ?>assetsPublic/img/favicon-256.png?v=<?= VERSION ?>' height='60'
             style='height: 60px;float: initial;margin:initial'>
    </a>
    <div class='container align-center upper panel mode-text-colorText'>acesso <?= SITENAME ?></div>
    <div class="col-12 mb-3 z-depth-2" id="login-card">
        <div class="panel">
            <div class="panel pl-4 pr-4">
                <label class="row">
                    <span>Email</span>
                    <input id="emaillog" type="email" class="font-light font-large">
                </label>
                <label class="row">
                    <span>Senha</span>
                    <input id="passlog" type="password" class="font-light font-large">
                </label>

                <?php if (defined("RECAPTCHASITE")) { ?>
                    <div class="container">
                        <div class="g-recaptcha" data-sitekey="<?= RECAPTCHASITE ?>"></div>
                        <br>
                    </div>
                    <script type="text/javascript" src="https://www.google.com/recaptcha/api.js"></script>

                <?php } else { ?>

                <input type="hidden" id="g-recaptcha-response"/>
                <?php } ?>
            </div>
            <button id="loginbtn" class="s-font-large upper margin-bottom theme-d2 hover-opacity-off opacity">
                Entrar
            </button>
        </div>
    </div>

    <div class="col-12 p-0">
        <?php \Login\Social::googleLogin(); ?>
        <?php \Login\Social::facebookLogin(); ?>
    </div>

    <div class="col-12 p-0 upper color-text-grey">
        <a href="<?= defined('HOME') ? HOME : "" ?>esqueci-a-senha"
           class="right btn radius hover-opacity-off opacity"
           style="text-decoration: none; margin-right:0;font-size:12px">
            esqueci a senha
        </a>
    </div>
    <div class="clear"><br><br><br></div>
</div>