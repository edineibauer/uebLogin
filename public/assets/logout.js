post('route', 'internet', {}, function (g) {
    toast("Saindo...", 3000, "toast-warning");
    if (g === 1) {
        post('login', 'logout', function (g) {
            setCookieAnonimo().then(() => {
                location.href = HOME + "login"
            })
        })
    } else {
        setCookieAnonimo().then(() => {
            window.location = HOME
        });
    }
})