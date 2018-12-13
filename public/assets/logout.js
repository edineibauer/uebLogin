clearCache().then(d => {
    toast("Saindo...", "toast-warning");
    post('login', 'logout', function(g) {
        if(g == 1) {
            setTimeout(function () {
                location.href = HOME + "login";
            },700);
        } else {
            toast("Erro", "toast-error");
        }
    });
});