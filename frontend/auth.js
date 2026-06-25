const container = document.getElementById("container");
const registerBtn = document.querySelector(".register-btn");
const loginBtn = document.querySelector(".login-btn");

const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");

const loginMsg = document.getElementById("loginMsg");
const registerMsg = document.getElementById("registerMsg");

const otpBox = document.getElementById("otpBox");
const otpCode = document.getElementById("otpCode");
const verifyOtpBtn = document.getElementById("verifyOtpBtn");
const mainLoginBtn = document.getElementById("loginBtn");
const phraseBox = document.getElementById("phraseBox");
const securityPhraseLogin = document.getElementById("securityPhraseLogin");
const verifyPhraseBtn = document.getElementById("verifyPhraseBtn");

const forgotPasswordLink = document.getElementById("forgotPasswordLink");
const forgotBox = document.getElementById("forgotBox");
const backToLoginBtn = document.getElementById("backToLoginBtn");
const forgotEmail = document.getElementById("forgotEmail");
const requestResetBtn = document.getElementById("requestResetBtn");
const resetToken = document.getElementById("resetToken");
const newPassword = document.getElementById("newPassword");
const resetPasswordBtn = document.getElementById("resetPasswordBtn");

const API = {
  login: "http://localhost/fitfuel-3fa/backend/api/auth/login.php",
  register: "http://localhost/fitfuel-3fa/backend/api/auth/register.php",
  me: "http://localhost/fitfuel-3fa/backend/api/auth/me.php",
  verifyOtp: "http://localhost/fitfuel-3fa/backend/api/auth/verify_otp.php",
  verifyPhrase: "http://localhost/fitfuel-3fa/backend/api/auth/verify_security_phrase.php",
  forgotPassword: "http://localhost/fitfuel-3fa/backend/api/auth/forgot_password.php",
  resetPassword: "http://localhost/fitfuel-3fa/backend/api/auth/reset_password.php",
};

registerBtn.addEventListener("click", () => {
  container.classList.add("active");
  resetLoginScreen();
});

loginBtn.addEventListener("click", () => {
  container.classList.remove("active");
  resetLoginScreen();
});

function showMsg(el, type, text) {
  el.classList.remove("hidden", "ok", "err");
  el.classList.add(type === "ok" ? "ok" : "err");
  el.textContent = text;
}

function clearMsg(el) {
  el.classList.add("hidden");
  el.textContent = "";
}

function resetLoginScreen() {
  loginForm.classList.remove("login-reset-mode");
  forgotBox.classList.add("hidden");
  otpBox.classList.add("hidden");
  phraseBox.classList.add("hidden");
  mainLoginBtn.classList.remove("hidden");

  otpCode.value = "";
  securityPhraseLogin.value = "";
  resetToken.value = "";
  newPassword.value = "";
  forgotEmail.value = "";

  clearMsg(loginMsg);
}

async function postJson(url, body) {
  const res = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    body: JSON.stringify(body),
  });

  const data = await res.json().catch(() => ({}));
  return { res, data };
}

async function redirectByRole() {
  const meRes = await fetch(API.me, { credentials: "include" });
  const meData = await meRes.json().catch(() => ({}));

  const role = meData?.user?.role || "USER";

  setTimeout(() => {
    window.location.href = role === "ADMIN" ? "admin.html" : "index.html";
  }, 600);
}

/* LOGIN STEP 1: PASSWORD */
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

    if (data.otp_required) {
      loginForm.classList.remove("login-reset-mode");
      forgotBox.classList.add("hidden");
      otpBox.classList.remove("hidden");
      mainLoginBtn.classList.add("hidden");

      showMsg(loginMsg, "ok", "Password correct. OTP sent to your email.");
      return;
    }

    showMsg(loginMsg, "ok", "Login successful! Redirecting...");
    redirectByRole();

  } catch (err) {
    showMsg(loginMsg, "err", "Network/server error.");
  }
});

/* LOGIN STEP 2: OTP */
verifyOtpBtn.addEventListener("click", async () => {
  clearMsg(loginMsg);

  const otp = otpCode.value.trim();

  if (!otp) {
    showMsg(loginMsg, "err", "Please enter OTP code.");
    return;
  }

  try {
    const { res, data } = await postJson(API.verifyOtp, { otp });

    if (!res.ok) {
      showMsg(loginMsg, "err", data.error || "OTP verification failed.");
      return;
    }

    phraseBox.classList.remove("hidden");
    otpBox.classList.add("hidden");
    
    showMsg(loginMsg, "ok", "OTP verified. Enter your security phrase.");

  } catch (err) {
    showMsg(loginMsg, "err", "Network/server error.");
  }
});

/* FORGOT PASSWORD MODE */
forgotPasswordLink.addEventListener("click", (e) => {
  e.preventDefault();

  loginForm.classList.add("login-reset-mode");
  forgotBox.classList.remove("hidden");
  otpBox.classList.add("hidden");
  mainLoginBtn.classList.add("hidden");

  otpCode.value = "";
  clearMsg(loginMsg);
});

/* BACK TO LOGIN */
backToLoginBtn.addEventListener("click", () => {
  resetLoginScreen();
});

/* REQUEST RESET TOKEN BY EMAIL */
requestResetBtn.addEventListener("click", async () => {
  clearMsg(loginMsg);

  const email = forgotEmail.value.trim();

  if (!email) {
    showMsg(loginMsg, "err", "Please enter your email.");
    return;
  }

  try {
    const { res, data } = await postJson(API.forgotPassword, { email });

    if (!res.ok) {
      showMsg(loginMsg, "err", data.error || "Reset request failed.");
      return;
    }

    showMsg(loginMsg, "ok", data.message || "Password reset email sent. Please check your inbox.");

  } catch (err) {
    showMsg(loginMsg, "err", "Network/server error.");
  }
});

/* RESET PASSWORD */
resetPasswordBtn.addEventListener("click", async () => {
  clearMsg(loginMsg);

  const token = resetToken.value.trim();
  const password = newPassword.value;

  if (!token || !password) {
    showMsg(loginMsg, "err", "Please enter token and new password.");
    return;
  }

  if (password.length < 6) {
    showMsg(loginMsg, "err", "Password must be at least 6 characters.");
    return;
  }

  try {
    const { res, data } = await postJson(API.resetPassword, {
      token,
      new_password: password
    });

    if (!res.ok) {
      showMsg(loginMsg, "err", data.error || "Password reset failed.");
      return;
    }

    resetLoginScreen();
    showMsg(loginMsg, "ok", "Password reset successful. You can log in now.");

  } catch (err) {
    showMsg(loginMsg, "err", "Network/server error.");
  }
});

/* REGISTER */
registerForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  clearMsg(registerMsg);

  const username = document.getElementById("regUsername").value.trim();
  const email = document.getElementById("regEmail").value.trim();
  const password = document.getElementById("regPassword").value;
  const securityPhrase = document.getElementById("regSecurityPhrase").value.trim();

  if (!username || !email || !password || !securityPhrase) {
    showMsg(registerMsg, "err", "Please fill all fields.");
    return;
  }

  if (password.length < 6) {
    showMsg(registerMsg, "err", "Password must be at least 6 characters.");
    return;
  }

  try {
    const { res, data } = await postJson(API.register, {
      username,
      email,
      password,
      security_phrase: securityPhrase
    });

    if (!res.ok) {
      showMsg(registerMsg, "err", data.error || "Registration failed.");
      return;
    }

    showMsg(registerMsg, "ok", "Registered! Now log in.");
    setTimeout(() => {
      container.classList.remove("active");
      resetLoginScreen();
    }, 700);

  } catch (err) {
    showMsg(registerMsg, "err", "Network/server error.");
  }
});
verifyPhraseBtn.addEventListener("click", async () => {
  clearMsg(loginMsg);

  const security_phrase = securityPhraseLogin.value.trim();

  if (!security_phrase) {
    showMsg(loginMsg, "err", "Please enter your security phrase.");
    return;
  }

  try {
    const { res, data } = await postJson(API.verifyPhrase, {
      security_phrase
    });

    if (!res.ok) {
      showMsg(loginMsg, "err", data.error || "Security phrase verification failed.");
      return;
    }

    showMsg(loginMsg, "ok", "Security phrase verified. Redirecting...");
    redirectByRole();

  } catch (err) {
    showMsg(loginMsg, "err", "Network/server error.");
  }
});