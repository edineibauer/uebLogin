if (dados.id == getCookie("id")) {
    dados.imagem = JSON.parse(dados.imagem)[0];
    delete dados.imagem.preview;
    setCookieUser(dados).then(() => {
        dashboardSidebarInfo();
    })
}