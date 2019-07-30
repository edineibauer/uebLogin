var loginFree = !0;

function login() {
    if (loginFree) {
        $("#login-card").loading();
        loginFree = !1;
        var email = $("#emaillog").val();
        var pass = $("#passlog").val();
        var recaptcha = $("#g-recaptcha-response").val();
        post('login', 'login', {email: email, pass: pass, recaptcha: recaptcha}, function (g) {
            if (typeof g === "string") {
                loginFree = !0;
                if (g !== "no-network")
                    toast(g, 3000, "toast-warning")
            } else {
                toast("Seja Bem-vindo!", 3000, "toast-success");

                setCookieUser(g).then(() => {
                    location.href = HOME + "dashboard"
                });
            }
        })
    }
}

$(function () {
    if (getCookie("token") !== "" && getCookie("token") !== "0")
        location.href = HOME + "dashboard";

    $("#core-content").off("keyup", "#emaillog, #passlog").on("keyup", "#emaillog, #passlog", function (e) {
        if (e.which === 13)
            login()
    })
});