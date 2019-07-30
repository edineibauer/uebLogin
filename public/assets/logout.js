toast("Saindo...", 3000, "toast-warning");

setCookieAnonimo().then(() => {
    location.href = HOME + "login"
});