function goToDashboard(dados) {
    post("login", "login", {email: dados['dados.email'], pass: dados['dados.password']}, function (g) {
        if(!g)
            location.href = HOME + "dashboard";
        else
            toast(g, 4000, "toast-warning");
    });
}