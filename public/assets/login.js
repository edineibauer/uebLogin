var loginFree = !0;

function login() {
    if (loginFree) {
        $("#login-card").loading();
        loginFree = !1;
        var email = $("#emaillog").val();
        var pass = $("#passlog").val();
        var recaptcha = $("#g-recaptcha-response").val();
        post('login', 'login', {email: email, pass: pass, recaptcha: recaptcha}, function (g) {
            if (g) {
                if (g !== "no-network")
                    toast(g, 3000, "toast-warning")
            } else {
                toast("Entrando...", 2000, "toast-success");
                clearCache();
                setTimeout(function () {
                    window.location.href = HOME + 'dashboard'
                }, 1500)
            }
            loginFree = !0
        })
    }
}

$("#core-content").off("keyup", "#emaillog, #passlog").on("keyup", "#emaillog, #passlog", function (e) {
    if (e.which === 13)
        login()
})