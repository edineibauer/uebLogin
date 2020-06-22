var loginFree = !0;

function login() {
    if(loginFree)
        toast("Carregando...", 15000);

    exeLogin($("#emaillog").val(), $("#passlog").val(), $("#g-recaptcha-response").val());
}

/**
 * login Social work with the user data to
 * create a new user or login
 */
function loginSocial(profile, social) {
    return AJAX.post("checkUserLoginSocial", Object.assign({"social": social}, profile)).then(result => {
        if(isEmpty(result)) {
            return exeLogin(profile.name, profile.id);
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

function exeLogin(email, senha, recaptcha) {
    if (loginFree) {
        $("#login-card").loading();
        loginFree = !1;
        post('login', 'login', {email: email, pass: senha, recaptcha: recaptcha}, function (g) {
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

var googleLogin = 0;
function onSignIn(googleUser) {
    if(googleLogin === 0) {
        gapi.auth2.getAuthInstance().signOut();

    } else {
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
    }
}

$(function () {

    if(!$("svg.waves").length) {
        getTemplates().then(tpl => {
            $("#core-content").after(Mustache.render(tpl.wavesBottom));
        });
    }

    if (!!localStorage.token && localStorage.token !== "0")
        location.href = "dashboard";

    $("#app").off("keyup", "#emaillog, #passlog").on("keyup", "#emaillog, #passlog", function (e) {
        if (e.which === 13)
            login();

    }).on("click", ".abcRioButtonContentWrapper", function() {
        googleLogin = 1;
    });
});