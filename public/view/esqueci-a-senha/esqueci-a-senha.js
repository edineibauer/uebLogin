var recoveryFree = true;

function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

function recoveryEmail() {
    if (recoveryFree) {
        recoveryFree = false;
        let email = $("#recovery-email").val();
        if(validateEmail(email)) {
            AJAX.post('recoveryEmail', {email: email}).then(g => {
                if (g == 1) {
                    toast('Link de Recuperação enviada ao email', 4000, "toast-success");
                    $("#recovery-email").val("");
                }

                recoveryFree = true;
            });
        } else {
            toast("Informe um email válido");
            recoveryFree = true;
        }
    }
}