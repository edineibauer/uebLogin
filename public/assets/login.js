var loginFree = !0;

function login() {
    if(loginFree)
        toast("Carregando...", 15000);

    exeLogin($("#emaillog").val(), $("#passlog").val(), $("#g-recaptcha-response").val());
}

function exeLogin(email, senha, recaptcha) {
    if (loginFree) {
        $("#login-card").loading();
        loginFree = !1;
        post('login', 'login', {email: email, pass: senha, recaptcha: recaptcha}, function (g) {
            if (typeof g === "string") {
                loginFree = !0;
                navigator.vibrate(100);
                if (g !== "no-network")
                    toast(g, 3000, "toast-warning")
            } else {
                toast("Entrando...", 15000, "toast-success");
                setCookieUser(g).then(() => {
                    let destino = "dashboard";
                    if (getCookie("redirectOnLogin") !== "") {
                        destino = getCookie("redirectOnLogin");
                        setCookie("redirectOnLogin", 1, -1);
                    }
                    location.href = destino;
                })
            }
        });
    }
}

var loadUserGoogle = 0;
function onSignIn(googleUser) {
    if(loadUserGoogle > 0) {
        if(loginFree)
            toast("Carregando...", 15000, "toast-success");
        var profile = googleUser.getBasicProfile();
        getJSON(HOME + "app/find/clientes/email/" + profile.getEmail()).then(r => {
            if (!isEmpty(r.clientes)) {
                exeLogin(profile.getEmail(), profile.getId())
            } else {
                db.exeCreate("clientes", {
                    nome: profile.getName(),
                    email: profile.getEmail(),
                    imagem_url: profile.getImageUrl(),
                    senha: profile.getId(),
                    ativo: 1
                }).then(result => {
                    if (result.db_errorback === 0)
                        exeLogin(result.email, profile.getId())
                })
            }
        });
    } else {
        if(typeof gapi !== "undefined")
            gapi.auth2.getAuthInstance().signOut();
    }
    loadUserGoogle++;
}

$(function () {
    if (getCookie("token") !== "" && getCookie("token") !== "0")
        location.href = "dashboard";

    $("#app").off("keyup", "#emaillog, #passlog").on("keyup", "#emaillog, #passlog", function (e) {
        if (e.which === 13)
            login()
    })
});