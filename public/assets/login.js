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
                loginFree = !0;
                if (g !== "no-network")
                    toast(g, 3000, "toast-warning")
            } else {
                toast("Seja Bem-vindo!", 3000, "toast-success");

                var xhttp = new XMLHttpRequest();
                xhttp.open("POST", HOME + "set");
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.onreadystatechange = function () {
                    if (this.readyState === 4 && this.status === 200) {
                        let data = JSON.parse(this.responseText);
                        if (typeof data.data === "object" && data.response === 1) {
                            setCookieUser(data.data).then(() => {
                                window.location.href = HOME + "dashboard"
                            });
                        } else {
                            setCookieAnonimo().then(() => {
                                window.location.href = HOME
                            });
                        }
                    }
                };
                xhttp.send("lib=route&file=sessao");
            }
        })
    }
}

$(function () {
    if (getCookie("token") !== "" && getCookie("token") !== "0")
        window.location.href = HOME + "dashboard";

    $("#core-content").off("keyup", "#emaillog, #passlog").on("keyup", "#emaillog, #passlog", function (e) {
        if (e.which === 13)
            login()
    })
});