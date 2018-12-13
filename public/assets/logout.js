clearCache().then(d => {
    toast("Saindo...", "toast-warning");
    post('login', 'logout', function(g) {
        setTimeout(function () {
            location.href = HOME + "login";
        },700);
    });
});