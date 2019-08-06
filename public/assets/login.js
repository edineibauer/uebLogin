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
            clearToast();
            if (typeof g === "string") {
                navigator.vibrate(100);
                loginFree = !0;
                if (g !== "no-network")
                    toast(g, 3000, "toast-warning")
            } else {
                toast("Seja Bem-vindo!", 3000, "toast-success");

                setCookieUser(g).then(() => {
                    app.loadView(HOME + "dashboard");
                })
            }
        })
    }
}

$(function () {
    if (getCookie("token") !== "" && getCookie("token") !== "0")
        app.loadView(HOME + "dashboard");

    $("#core-content").off("keyup", "#emaillog, #passlog").on("keyup", "#emaillog, #passlog", function (e) {
        if (e.which === 13)
            login()
    })
});