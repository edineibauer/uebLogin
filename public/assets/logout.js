toast("Saindo...", 3000);

setCookieAnonimo().then(() => {
    location.href = HOME + "login"
});