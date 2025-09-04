// utils.js
function getLoggedUser() {
    const userStr = localStorage.getItem('user');
    if (!userStr) return null;
    try {
        return JSON.parse(userStr);
    } catch {
        return null;
    }
}

function isLoggedIn() {
    return !!getLoggedUser();
}

function isAdmin() {
    const user = getLoggedUser();
    return user && Number(user.admin) === 1;
}

function requireLogin() {
    if (!isLoggedIn()) {
        window.location.href = "login.html";
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        alert("Acceso denegado: requiere permisos de administrador.");
        window.location.href = "index.html";
    }
}
