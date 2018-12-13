clearCache().then(d => {
    toast("Saindo...", "toast-warning");
    setTimeout(function () {
        location.href = HOME + "login";
    },1000);
});