if (dados.id == getCookie("id")) {
    if(dados.imagem !== "" && dados.imagem !== "null") {
        dados.imagem = JSON.parse(dados.imagem)[0];
        delete dados.imagem.preview;
    }
    setCookieUser(dados).then(() => {
        dashboardSidebarInfo();
    })
}