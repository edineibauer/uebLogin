if (dados.id == getCookie("id")) {
    $.each(dados, function (i, e) {
        if(i !== "id_old" && i !== "db_action" && i !== "db_status") {
            if (typeof e === "object")
                e = JSON.stringify(e);

            setCookie(i, e);
            localStorage.setItem(i, e);
        }
    });
    dashboardSidebarInfo();
    menuDashboard();
}