toast("Saindo...", 3000);
setCookieAnonimo().then(() => {
    pageTransition("login", "route", "fade", "#core-content", null, null, !1);
});