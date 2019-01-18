clearCache().then(d => {
    toast("Saindo...", "toast-warning");
    setCookie("token", 0 , -1);
    setCookie("id", 0 , -1);
    setCookie("nome", 0 , -1);
    setCookie("nome_usuario", 0 , -1);
    setCookie("email", 0 , -1);
    setCookie("setor", 0 , -1);
    setCookie("nivel", 0 , -1);
    localStorage.removeItem("limitGrid");
    dbLocal.exeDelete('db_historic', 1);
    dbLocal.exeDelete('db_content', 1);
    $.each(dicionarios, function (entity, meta) {
        dbLocal.clear(entity);
        dbLocal.clear('sync_' + entity);
    });

    post('login', 'logout', function (g) {
        location.href = HOME + "login"
    })
});