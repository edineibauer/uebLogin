if (dados.id == getCookie("id")) {
    setCookie("nome", dados.nome);
    setCookie("imagem", typeof dados.imagem === "object" && typeof dados.imagem[0] !== "undefined" ? dados.imagem[0].image : "");
    setCookie("nome_usuario", slug(dados.nome));
    setCookie("email", dados.email);
    dashboardSidebarInfo();
}