if (dados.id == getCookie("id")) {
    if(!isEmpty(dados.imagem) && dados.imagem !== "null") {
        dados.imagem = JSON.parse(dados.imagem)[0];
        delete dados.imagem.preview;

        if (typeof dados.imagem === "object")
            dados.imagem = JSON.stringify(dados.imagem);

        setCookie("imagem", dados.imagem);
        localStorage.setItem("imagem", dados.imagem);
    } else {
        setCookie("imagem", "");
        localStorage.setItem("imagem", "");
    }

    setCookie("nome", dados.nome);
    localStorage.setItem("nome", dados.nome);

    menuHeader().then(() => {
        dashboardSidebarInfo();
    })
}