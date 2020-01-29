if (dados.id == USER.id) {
    if(!isEmpty(dados.imagem) && dados.imagem !== "null") {
        dados.imagem = JSON.parse(dados.imagem)[0];
        delete dados.imagem.preview;

        if (typeof dados.imagem === "object")
            dados.imagem = JSON.stringify(dados.imagem);

        USER.imagem = dados.imagem;
    } else {
        USER.imagem = "";
    }

    USER.nome = dados.nome;

    menuHeader().then(() => {
        dashboardSidebarInfo();
    })
}