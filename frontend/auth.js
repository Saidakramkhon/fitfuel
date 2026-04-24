const container = document.getElementById("container");
const registerBtn = document.querySelector(".register-btn");
const loginBtn = document.querySelector(".login-btn");

const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");

const loginMsg = document.getElementById("loginMsg");
const registerMsg = document.getElementById("registerMsg");

const API = {
  login: "http://localhost/fitfuel/backend/api/auth/login.php",
  register: "http://localhost/fitfuel/backend/api/auth/register.php",
  me: "http://localhost/fitfuel/backend/api/auth/me.php",
};

// UI toggle
registerBtn.addEventListener("click", () => container.classList.add("active"));
loginBtn.addEventListener("click", () => container.classList.remove("active"));

function showMsg(el, type, text) {
  el.classList.remove("hidden", "ok", "err");
  el.classList.add(type === "ok" ? "ok" : "err");
  el.textContent = text;
}

function clearMsg(el) {
  el.classList.add("hidden");
  el.textContent = "";
}

async function postJson(url, body) {
  const res = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include", // ✅ IMPORTANT for PHP sessions
    body: JSON.stringify(body),
  });

  const data = await res.json().catch(() => ({}));
  return { res, data };
}

// LOGIN submit
loginForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  clearMsg(loginMsg);

  const email = document.getElementById("loginEmail").value.trim();
  const password = document.getElementById("loginPassword").value;

  if (!email || !password) {
    showMsg(loginMsg, "err", "Please enter email and password.");
    return;
  }

  try {
    const { res, data } = await postJson(API.login, { email, password });

    if (!res.ok) {
      showMsg(loginMsg, "err", data.error || "Login failed.");
      return;
    }

    // After login, check role and redirect
    const meRes = await fetch(API.me, { credentials: "include" });
    const meData = await meRes.json().catch(() => ({}));

    const role = meData?.user?.role || "USER";

    showMsg(loginMsg, "ok", "Login successful! Redirecting...");
    setTimeout(() => {
      window.location.href = role === "ADMIN" ? "admin.html" : "index.html";
    }, 600);

  } catch (err) {
    showMsg(loginMsg, "err", "Network/server error.");
  }
});

// REGISTER submit
registerForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  clearMsg(registerMsg);

  const username = document.getElementById("regUsername").value.trim();
  const email = document.getElementById("regEmail").value.trim();
  const password = document.getElementById("regPassword").value;

  if (!username || !email || !password) {
    showMsg(registerMsg, "err", "Please fill all fields.");
    return;
  }

  if (password.length < 6) {
    showMsg(registerMsg, "err", "Password must be at least 6 characters.");
    return;
  }

  try {
    const { res, data } = await postJson(API.register, { username, email, password });

    if (!res.ok) {
      showMsg(registerMsg, "err", data.error || "Registration failed.");
      return;
    }

    showMsg(registerMsg, "ok", "Registered! Now log in.");
    // Switch to login view after a moment
    setTimeout(() => container.classList.remove("active"), 700);

  } catch (err) {
    showMsg(registerMsg, "err", "Network/server error.");
  }
});