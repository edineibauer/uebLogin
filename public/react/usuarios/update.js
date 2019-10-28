if (dados.id == getCookie("id")) {
    if(dados.imagem !== "" && dados.imagem !== "null") {
        dados.imagem = JSON.parse(dados.imagem)[0];
        delete dados.imagem.preview;
    }

    if(dados.setor === "" || dados.setor === "null")
        dados.setor = "admin";

    setCookieUser(dados).then(() => {
        dashboardSidebarInfo();
    })
}