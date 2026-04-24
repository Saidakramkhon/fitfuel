const API_ME = "http://localhost/fitfuel/backend/api/auth/me.php";
const API_LOGOUT = "http://localhost/fitfuel/backend/api/auth/logout.php";

const adminLink = document.getElementById("adminLink");
const loginLink = document.getElementById("loginLink");
const logoutLink = document.getElementById("logoutLink");

async function checkAuth() {
  try {
    const res = await fetch(API_ME, { credentials: "include" });
    const data = await res.json();

    if (!res.ok) throw new Error();

    // user is logged in
    loginLink.style.display = "none";
    logoutLink.style.display = "block";

    if (data.user.role === "ADMIN") {
      adminLink.style.display = "block";
    } else {
      adminLink.style.display = "none";
    }

  } catch {
    // not logged in
    loginLink.style.display = "block";
    logoutLink.style.display = "none";
    adminLink.style.display = "none";
  }
}

async function logout() {
  await fetch(API_LOGOUT, { credentials: "include" });
  window.location.href = "auth.html";
}

checkAuth();