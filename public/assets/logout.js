toast("Saindo...", 1500);
setCookieAnonimo().then(() => {
    pageTransition("login", "route", "fade", "#core-content", null, null, !1);
});