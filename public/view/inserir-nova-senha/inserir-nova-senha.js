var novaSenha = true;

async function newPassword() {
    if (novaSenha) {
        novaSenha = false;
        let senha = $("#nova-senha").val();
        let senha2 = $("#nova-senha-confirm").val();

        if(senha.length < 3) {
            toast("senha deve ter no mínimo 3 caracteres", 4000, "toast-warning");
            novaSenha = true;

        } else if (senha !== senha2) {
            toast("senhas não são iguais", 3000, "toast-warning");
            novaSenha = true;

        } else {
            let g = await AJAX.post('setNewPassword', {senha: senha, code: PARAM[0]});

            if (g === "1") {
                toast('Salvo com sucesso', 1200, "toast-success");
                pageTransition("index");
            } else if(g !== "no-network"){
                toast("Erro! Tente solicitar o email novamente.", 6000, "toast-error");
            }

            novaSenha = true;
        }
    }
}