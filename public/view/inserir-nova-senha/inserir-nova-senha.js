var novaSenha = true;

function newPassword() {
    if (novaSenha) {
        novaSenha = false;
        if ($("#nova-senha").val() === $("#nova-senha-confirm").val()) {
            post('login', 'setNewPassword', {senha: $("#nova-senha").val(), code: $("#code").val()}, function (g) {
                if (g === "1") {
                    toast('Senha Modificada, redirecionando...', 1200);
                    setTimeout(function () {
                        window.location.href = HOME + "login";
                    },1500);
                } else if(g !== "no-network"){
                    toast("Token Inválido! Tente recuperar senha novamente.", 6000, "toast-warning");
                }
                novaSenha = true;
            });
        } else {
            toast("senhas não correspondem", 3000, "toast-warning");
            novaSenha = true;
        }
    }
}