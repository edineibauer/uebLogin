clearToast();
toast("Saindo...", 3000);
setCookieAnonimo().then(() => {
    app.loadView(HOME + "login");
});