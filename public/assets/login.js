var loginFree = !0;

function login() {
    if (loginFree) {
        $("#login-card").loading();
        loginFree = !1;
        var email = $("#emaillog").val();
        var pass = $("#passlog").val();
        var recaptcha = $("#g-recaptcha-response").val();
        toast("Validando dados!", 15000);
        post('login', 'login', {email: email, pass: pass, recaptcha: recaptcha}, function (g) {
            if (typeof g === "string") {
                navigator.vibrate(100);
                loginFree = !0;
                if (g !== "no-network")
                    toast(g, 3000, "toast-warning")
            } else {
                toast("Seja Bem-vindo!", 1800, "toast-success");

                setCookieUser(g).then(() => {
                    pageTransition("dashboard", "route", "forward", "#core-content", null, null, !1);
                })
            }
        })
    }
}

$(function () {
    if (getCookie("token") !== "" && getCookie("token") !== "0")
        pageTransition("dashboard", "route", "fade", "#core-content", null, null, !1);

    $("#app").off("keyup", "#emaillog, #passlog").on("keyup", "#emaillog, #passlog", function (e) {
        if (e.which === 13)
            login()
    })
});