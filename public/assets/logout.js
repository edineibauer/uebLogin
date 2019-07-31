setCookieAnonimo()
    .then(() => {
        loadUserViews()
    }).then(() => {
    location.href = HOME + "login"
});