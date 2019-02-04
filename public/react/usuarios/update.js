setCookie("nome", dados.nome);
setCookie("imagem", typeof dados.imagem === "object" && typeof dados.imagem[0] !== "undefined" ? dados.imagem[0].image : "");
setCookie("nome_usuario", slug(dados.nome));
setCookie("email", dados.email);
setCookie("setor", dados.setor);
setCookie("status", dados.status);
setCookie("nivel", dados.nivel);
dashboardSidebarInfo();
setSidebarInfo();