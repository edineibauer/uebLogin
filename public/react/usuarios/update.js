if (dados.id == getCookie("id")) {
    setCookieUser(dados).then(() => {
        dashboardSidebarInfo();
    })
}