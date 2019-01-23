post('route', 'internet', {}, function (g) {
    if(g === 1) {
        toast("Saindo...", 3000, "toast-warning");
        clearCache().then(() => {
            post('login', 'logout', function(g) {
                location.href = HOME + "login";
            });
        });
    } else {
        setCookieAnonimo();
        window.location = HOME;
    }
})