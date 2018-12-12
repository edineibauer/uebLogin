var loginFree = true;

function login() {
    if (loginFree) {
        $("#login-card").loading();
        loginFree = false;
        var email = $("#emaillog").val();
        var pass = $("#passlog").val();
        var recaptcha = $("#g-recaptcha-response").val();
        post('login', 'login', {
            email: email,
            pass: pass,
            recaptcha: recaptcha
        }, function (g) {
            if(g) {
                if(g !== "no-network")
                    toast(g, 3000, "toast-warning");
            } else {
                toast("Logando...", 2000, "toast-success");

                setTimeout(function () {
                    window.location.href = HOME + 'dashboard';
                }, 1500);
            }
            loginFree = true;
        });

    }
}

$("#core-content").off("keyup", "#emaillog, #passlog").on("keyup", "#emaillog, #passlog", function (e) {
    if(e.which === 13)
        login();
});