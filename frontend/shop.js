const API = {
    list: "http://localhost/fitfuel/backend/api/products/list.php",
    me: "http://localhost/fitfuel/backend/api/auth/me.php",
    logout: "http://localhost/fitfuel/backend/api/auth/logout.php",
  };
  
  const grid = document.getElementById("productGrid");
  const emptyMsg = document.getElementById("emptyMsg");
  const search = document.getElementById("search");
  const welcomeText = document.getElementById("welcomeText");
  const adminBtn = document.getElementById("adminBtn");
  const logoutBtn = document.getElementById("logoutBtn");
  const filtersEl = document.getElementById("filters");
  let selectedCategory = "";
  const sort = document.getElementById("sort");
  let sortMode = "";
  
  let allProducts = [];
  
  /* ---------------- CART (localStorage) ---------------- */
  const CART_KEY = "fitfuel_cart";
  
  function readCart() {
    try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; }
    catch { return []; }
  }
  
  function writeCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
  }
  
  function updateCartCount() {
    const cart = readCart();
    const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
    const el = document.getElementById("cartCount");
    if (el) el.textContent = totalQty;
  }
  
  function addToCart(product) {
    const cart = readCart();
    const existing = cart.find(i => i.id === product.id);
  
    if (existing) {
      existing.qty += 1;
    } else {
      cart.push({
        id: product.id,
        name: product.name,
        price: Number(product.price),
        image_url: product.image_url || "",
        qty: 1
      });
    }
  
    writeCart(cart);
    updateCartCount();
    alert("Added to cart ✅");
  }
  /* ----------------------------------------------------- */
  
  function escapeHtml(str) {
    return String(str ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }
  
  function render(products) {
    grid.innerHTML = "";
  
    if (!products.length) {
      emptyMsg.classList.remove("hidden");
      updateCartCount();
      return;
    }
    emptyMsg.classList.add("hidden");
  
    products.forEach(p => {
      const card = document.createElement("div");
      card.className = "card";
  
      const imgSrc = (p.image_url && p.image_url.trim() !== "")
        ? escapeHtml(p.image_url)
        : "images/placeholder.jpg";
  
      card.innerHTML = `
        <img class="pimg" src="${imgSrc}" alt="${escapeHtml(p.name)}"
             onerror="this.src='images/placeholder.jpg'">
  
        <h3>${escapeHtml(p.name)}</h3>
        <p class="muted">${escapeHtml(p.description)}</p>
        <span class="badge">${escapeHtml(p.category)} • ${p.calories} kcal • ${p.protein}g protein</span>
  
        <div class="row">
          <span class="price">€${Number(p.price).toFixed(2)}</span>
          <button class="btn btn-primary" data-add="${p.id}">Add to cart</button>
        </div>
      `;
  
      grid.appendChild(card);
  
      /* ✅ attach click handler */
      const btn = card.querySelector("[data-add]");
      btn.addEventListener("click", () => addToCart(p));
    });
  
    updateCartCount(); // ✅ keep count correct after render
  }
  
  function applySearch() {
    const q = search.value.trim().toLowerCase();
  
    let filtered = allProducts.filter(p => {
      const matchesText =
        !q ||
        String(p.name).toLowerCase().includes(q) ||
        String(p.category).toLowerCase().includes(q);
  
      const matchesCategory =
        !selectedCategory || String(p.category).toLowerCase() === selectedCategory;
  
      return matchesText && matchesCategory;
    });
  
    // ✅ sorting
    if (sortMode === "price_asc") {
      filtered.sort((a, b) => Number(a.price) - Number(b.price));
    } else if (sortMode === "price_desc") {
      filtered.sort((a, b) => Number(b.price) - Number(a.price));
    }
  
    render(filtered);
  }
  
  async function loadProducts() {
    grid.innerHTML = `<p class="muted">Loading…</p>`;
    try {
      const res = await fetch(API.list, { credentials: "include" });
      const data = await res.json();
      allProducts = Array.isArray(data) ? data : [];
      applySearch();
    } catch {
      grid.innerHTML = `<p class="muted">Error loading products.</p>`;
    }
  }
  
  async function loadMe() {
    try {
      const res = await fetch(API.me, { credentials: "include" });
      const data = await res.json();
  
      if (!res.ok) throw new Error();
  
      welcomeText.textContent = `Welcome, ${data.user.username} (${data.user.role})`;
  
      if (data.user.role === "ADMIN") {
        adminBtn.style.display = "inline-flex";
      }
    } catch {
      window.location.href = "auth.html";
    }
  }
  
  logoutBtn.addEventListener("click", async () => {
    await fetch(API.logout, { credentials: "include" });
    window.location.href = "auth.html";
  });
  
  search.addEventListener("input", applySearch);
  if (filtersEl) {
    filtersEl.addEventListener("click", (e) => {
      const btn = e.target.closest("button[data-cat]");
      if (!btn) return;
  
      selectedCategory = btn.dataset.cat; // "" means All
      filtersEl.querySelectorAll(".fbtn").forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
  
      applySearch();
    });
  }
  if (sort) {
    sort.addEventListener("change", () => {
      sortMode = sort.value;
      applySearch();
    });
  }
  
  updateCartCount(); // ✅ show cart count immediately
  loadMe();
  loadProducts();