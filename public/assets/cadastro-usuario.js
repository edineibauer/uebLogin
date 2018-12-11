function goToDashboard(dados) {
    post("session-control", "login", {email: dados['dados.email'], pass: dados['dados.password']}, function (g) {
        if(!g)
            location.href = HOME + "dashboard";
        else
            toast(g, 4000, "toast-warning");
    });
}