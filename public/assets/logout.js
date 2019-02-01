post('route', 'internet', {}, function (g) {
    toast("Saindo...", 3000, "toast-warning");
    if(g === 1) {
        post('login', 'logout', function(g) {
            clearCacheLogin().then(() => {
                location.href = HOME + "login";
            });
        });
    } else {
        clearCacheLogin().then(() => {
            setCookieAnonimo();
            window.location = HOME;
        });
    }
});