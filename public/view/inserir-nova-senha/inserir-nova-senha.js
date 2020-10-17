var novaSenha = true;

function newPassword() {
    if (novaSenha) {
        novaSenha = false;
        if ($("#nova-senha").val() === $("#nova-senha-confirm").val()) {
            AJAX.post('setNewPassword', {senha: $("#nova-senha").val(), code: URL[0]}).then(g => {
                if (g === "1") {
                    toast('Salvo com sucesso', 1200, "toast-success");
                    pageTransition("index");
                } else if(g !== "no-network"){
                    toast("Erro! Tente solicitar o email novamente.", 6000, "toast-error");
                }
                novaSenha = true;
            });
        } else {
            toast("senhas não são iguais", 3000, "toast-warning");
            novaSenha = true;
        }
    }
}