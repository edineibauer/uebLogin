var loginFree = !0;

function login() {
    exeLogin($("#email").val(), $("#senha").val(), $("#g-recaptcha-response").val());
}

function exeLogin(email, senha, recaptcha) {
    if (loginFree) {
        $("#login-card").loading();
        loginFree = !1;
        toast("Acessando...", 15000);
        post('login', 'login', {email: email, pass: senha, recaptcha: recaptcha}, function (g) {
            if (typeof g === "string") {
                loginFree = !0;
                navigator.vibrate(100);
                if (g !== "no-network")
                    toast(g, 3000, "toast-warning")
            } else {
                toast("Bem-vindo", 1500, "toast-success");
                setCookieUser(g).then(() => {
                    let destino = "dashboard";
                    if (getCookie("redirectOnLogin") !== "") {
                        destino = getCookie("redirectOnLogin");
                        setCookie("redirectOnLogin", 1, -1);
                    }
                    pageTransition(destino, "route", "forward", "#core-content");
                })
            }
        });
    }
}

function onSignIn(googleUser) {
    var profile = googleUser.getBasicProfile();
    getJSON(HOME + "app/find/clientes/email/" + profile.getEmail()).then(r => {
        if(!isEmpty(r)) {
            let user = r.clientes[0];
            exeLogin(user.email, profile.getId());
        } else {
            db.exeCreate("clientes", {
                nome: profile.getName(),
                email: profile.getEmail(),
                imagem_url: profile.getImageUrl(),
                senha: profile.getId(),
                ativo: 1
            }).then(result => {
                loginFree = !0;
                if(result.db_errorback === 0)
                    exeLogin(result.email, profile.getId());
            });
        }
    });
}

$(function () {
    if (getCookie("token") !== "" && getCookie("token") !== "0")
        location.href = "dashboard";

    $("#app").off("keyup", "#emaillog, #passlog").on("keyup", "#emaillog, #passlog", function (e) {
        if (e.which === 13)
            login()
    })
});