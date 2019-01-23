clearCache().then(() => {
    setCookieAnonimo();
    toast("Saindo...", 3000, "toast-warning");

    post('login', 'logout', function(g) {
        location.href = HOME + "login";
    });
});