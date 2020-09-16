var loginFree = !0;

function login() {
    if(loginFree)
        toast("Carregando...", 15000);

    exeLogin($("#emaillog").val(), $("#passlog").val(), null, null, $("#g-recaptcha-response").val());
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
    }).off("click", "#loginbtn").on("click", "#loginbtn", function () {
        login();
    });
});