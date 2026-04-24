const API = {
    list: "http://localhost/fitfuel/backend/api/products/list.php",
    create: "http://localhost/fitfuel/backend/api/admin/product_create.php",
    update: "http://localhost/fitfuel/backend/api/admin/product_update.php",
    del: "http://localhost/fitfuel/backend/api/admin/product_delete.php",
  };
  
  const els = {
    body: document.getElementById("tableBody"),
    msg: document.getElementById("msg"),
    refresh: document.getElementById("refreshBtn"),
    search: document.getElementById("search"),
    form: document.getElementById("productForm"),
    formTitle: document.getElementById("formTitle"),
    submitBtn: document.getElementById("submitBtn"),
    cancelEditBtn: document.getElementById("cancelEditBtn"),
    productId: document.getElementById("productId"),
    name: document.getElementById("name"),
    description: document.getElementById("description"),
    image_url: document.getElementById("image_url"),
    price: document.getElementById("price"),
    calories: document.getElementById("calories"),
    protein: document.getElementById("protein"),
    category: document.getElementById("category"),
  };
  
  let allProducts = [];
  
  function showMsg(type, text) {
    els.msg.classList.remove("hidden", "ok", "err");
    els.msg.classList.add(type === "ok" ? "ok" : "err");
    els.msg.textContent = text;
  }
  
  function clearMsg() {
    els.msg.classList.add("hidden");
    els.msg.textContent = "";
  }
  
  function toNumber(v) {
    const n = Number(v);
    return Number.isFinite(n) ? n : null;
  }
  
  function renderTable(products) {
    if (!products.length) {
      els.body.innerHTML = `<tr><td colspan="6" class="muted">No products yet.</td></tr>`;
      return;
    }
  
    els.body.innerHTML = products.map(p => `
      <tr>
        <td>${escapeHtml(p.name)}</td>
        <td>${escapeHtml(p.category)}</td>
        <td class="num">€${Number(p.price).toFixed(2)}</td>
        <td class="num">${p.calories}</td>
        <td class="num">${p.protein}g</td>
        <td class="num">
          <div class="row-actions">
            <button class="btn btn-ghost" data-action="edit" data-id="${p.id}">Edit</button>
            <button class="btn btn-danger" data-action="delete" data-id="${p.id}">Delete</button>
          </div>
        </td>
      </tr>
    `).join("");
  }
  
  function escapeHtml(str) {
    return String(str)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }
  
  async function loadProducts() {
    clearMsg();
    els.body.innerHTML = `<tr><td colspan="6" class="muted">Loading…</td></tr>`;
  
    try {
      const res = await fetch(API.list, { credentials: "include" });
      const data = await res.json();
      allProducts = Array.isArray(data) ? data : [];
      applySearch();
    } catch (e) {
      showMsg("err", "Failed to load products. Check console and API URL.");
      els.body.innerHTML = `<tr><td colspan="6" class="muted">Error loading.</td></tr>`;
    }
  }
  
  function applySearch() {
    const q = els.search.value.trim().toLowerCase();
    const filtered = !q
      ? allProducts
      : allProducts.filter(p =>
          String(p.name).toLowerCase().includes(q) ||
          String(p.category).toLowerCase().includes(q)
        );
  
    renderTable(filtered);
  }
  
  function setEditMode(product) {
    els.formTitle.textContent = `Edit product #${product.id}`;
    els.submitBtn.textContent = "Update";
    els.cancelEditBtn.classList.remove("hidden");
  
    els.productId.value = product.id;
    els.name.value = product.name ?? "";
    els.description.value = product.description ?? "";
    els.image_url.value = product.image_url ?? "";
    els.price.value = product.price ?? "";
    els.calories.value = product.calories ?? "";
    els.protein.value = product.protein ?? "";
    els.category.value = product.category ?? "";
  }
  
  function resetForm() {
    els.formTitle.textContent = "Add product";
    els.submitBtn.textContent = "Create";
    els.cancelEditBtn.classList.add("hidden");
  
    els.productId.value = "";
    els.form.reset();
  }
  
  function getFormPayload() {
    const payload = {
      name: els.name.value.trim(),
      description: els.description.value.trim(),
      image_url: els.image_url.value.trim(),
      price: toNumber(els.price.value),
      calories: toNumber(els.calories.value),
      protein: toNumber(els.protein.value),
      category: els.category.value,
    };
  
    // basic validation
    if (!payload.name || payload.price === null || payload.calories === null || payload.protein === null || !payload.category) {
      return { error: "Please fill name, category, price, calories, protein." };
    }
  
    if (payload.price < 0 || payload.calories < 0 || payload.protein < 0) {
      return { error: "Numbers must be non-negative." };
    }
  
    return { payload };
  }
  
  async function createProduct(payload) {
    const res = await fetch(API.create, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || "Create failed");
    return data;
  }
  
  async function updateProduct(id, payload) {
    const res = await fetch(API.update, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ id: Number(id), ...payload }),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || "Update failed");
    return data;
  }
  
  async function deleteProduct(id) {
    const res = await fetch(API.del, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ id: Number(id) }),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || "Delete failed");
    return data;
  }
  
  // Events
  els.refresh.addEventListener("click", loadProducts);
  els.search.addEventListener("input", applySearch);
  
  els.cancelEditBtn.addEventListener("click", () => {
    resetForm();
    clearMsg();
  });
  
  els.form.addEventListener("submit", async (e) => {
    e.preventDefault();
    clearMsg();
  
    const { payload, error } = getFormPayload();
    if (error) {
      showMsg("err", error);
      return;
    }
    console.log("SENDING PAYLOAD:", payload);
  
    const id = els.productId.value;
  
    try {
      if (id) {
        await updateProduct(id, payload);
        showMsg("ok", "Product updated.");
      } else {
        await createProduct(payload);
        showMsg("ok", "Product created.");
      }
  
      resetForm();
      await loadProducts();
    } catch (err) {
      showMsg("err", err.message);
    }
  });
  
  document.addEventListener("click", async (e) => {
    const btn = e.target.closest("button[data-action]");
    if (!btn) return;
  
    const action = btn.dataset.action;
    const id = btn.dataset.id;
  
    if (action === "edit") {
      const product = allProducts.find(p => String(p.id) === String(id));
      if (product) setEditMode(product);
    }
  
    if (action === "delete") {
      const product = allProducts.find(p => String(p.id) === String(id));
      const ok = confirm(`Delete "${product?.name ?? "this product"}"?`);
      if (!ok) return;
  
      try {
        await deleteProduct(id);
        showMsg("ok", "Product deleted.");
        await loadProducts();
      } catch (err) {
        showMsg("err", err.message);
      }
    }
  });
  
  // Init
  loadProducts();