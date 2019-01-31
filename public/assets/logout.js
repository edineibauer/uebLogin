post('route', 'internet', {}, function (g) {
    if(g === 1) {
        toast("Saindo...", 3000, "toast-warning");
        post('login', 'logout', function(g) {
            clearCacheLogin().then(() => {
                location.href = HOME + "login";
            });
        });
    } else {
        setCookieAnonimo();
        window.location = HOME;
    }
})