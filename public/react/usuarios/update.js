if (dados.id == getCookie("id")) {
    $.each(dados, function (i, e) {
        if(typeof e === "object")
            e = JSON.stringify(e);

        setCookie(i, e);
        localStorage.setItem(i, e);
    });
    dashboardSidebarInfo();
    menuDashboard();
}