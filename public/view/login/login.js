var loginFree = !0;

function login() {
    if(loginFree)
        toast("Carregando...", 15000);

    exeLogin($("#emaillog").val(), $("#passlog").val(), null, null, $("#g-recaptcha-response").val());
}

/**
 * login Social work with the user data to
 * create a new user or login
 */
function loginSocial(profile, social) {
    return AJAX.post("checkUserLoginSocial", Object.assign({"social": social}, profile)).then(result => {
        if(isEmpty(result)) {
            return exeLogin(profile.name, profile.id, social, profile.token);
        } else {
            console.log(result);
            toast("Erro ao cadastrar usuário! Verifique o console para mais detalhes", 5000, "toast-error");
        }
    });
}

function loginFacebook(profile) {
    loginSocial(profile, 'facebook');
}

function loginGoogle(profile) {
    loginSocial(profile, 'google');
}

function exeLogin(email, senha, social, token, recaptcha) {
    if (loginFree) {
        $("#login-card").loading();
        loginFree = !1;
        AJAX.post('login', {email: email, pass: senha, social: social, token: token, recaptcha: recaptcha}).then(g => {
            if (typeof g === "string") {
                loginFree = !0;
                navigator.vibrate(100);
                if (g !== "no-network")
                    toast(g, 3000, "toast-error")
            } else {
                toast("Seja bem vindo, acessando...", 15000, "toast-success");
                setCookieUser(g).then(() => {
                    let destino = "dashboard";
                    if (!!localStorage.redirectOnLogin) {
                        destino = localStorage.redirectOnLogin;
                        localStorage.removeItem("redirectOnLogin");
                    }
                    location.href = destino;
                })
            }
        });
    }
}

$(function () {
    if(!$("svg.waves").length) {
        getTemplates().then(tpl => {
            $("#core-content").after(Mustache.render(tpl.wavesBottom));
        });
    }

    $("#app").off("keyup", "#emaillog, #passlog").on("keyup", "#emaillog, #passlog", function (e) {
        if (e.which === 13)
            login();
    });
});