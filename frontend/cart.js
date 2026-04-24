const CART_KEY = "fitfuel_cart";

function readCart() {
  try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; }
  catch { return []; }
}

function writeCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
}

function money(n) {
  return "€" + Number(n).toFixed(2);
}

function renderCart() {
  const grid = document.getElementById("cartGrid");
  const empty = document.getElementById("emptyCart");
  const totalEl = document.getElementById("totalPrice");

  const cart = readCart();
  grid.innerHTML = "";

  if (cart.length === 0) {
    empty.classList.remove("hidden");
    totalEl.textContent = money(0);
    return;
  }
  empty.classList.add("hidden");

  let total = 0;

  cart.forEach((item, idx) => {
    total += item.price * item.qty;

    const imgSrc = item.image_url && item.image_url.trim() !== ""
      ? item.image_url
      : "images/placeholder.jpg";

    const card = document.createElement("div");
    card.className = "card";

    card.innerHTML = `
      <img class="pimg" src="${imgSrc}" alt="${item.name}"
           onerror="this.src='images/placeholder.jpg'">

      <h3>${item.name}</h3>

      <div class="row">
        <span class="price">${money(item.price)}</span>
        <span class="badge">Qty: <strong>${item.qty}</strong></span>
      </div>

      <div class="row" style="margin-top:12px;">
        <button class="btn btn-ghost" data-dec="${idx}">-</button>
        <button class="btn btn-ghost" data-inc="${idx}">+</button>
        <button class="btn btn-ghost" data-remove="${idx}">Remove</button>
      </div>
    `;

    grid.appendChild(card);

    card.querySelector(`[data-inc="${idx}"]`).addEventListener("click", () => {
      const c = readCart();
      c[idx].qty += 1;
      writeCart(c);
      renderCart();
    });

    card.querySelector(`[data-dec="${idx}"]`).addEventListener("click", () => {
      const c = readCart();
      c[idx].qty -= 1;
      if (c[idx].qty <= 0) c.splice(idx, 1);
      writeCart(c);
      renderCart();
    });

    card.querySelector(`[data-remove="${idx}"]`).addEventListener("click", () => {
      const c = readCart();
      c.splice(idx, 1);
      writeCart(c);
      renderCart();
    });
  });

  totalEl.textContent = money(total);
}

document.getElementById("clearBtn").addEventListener("click", () => {
  localStorage.removeItem(CART_KEY);
  renderCart();
});

document.getElementById("checkoutBtn").addEventListener("click", async () => {
    const cart = readCart();
    if (cart.length === 0) {
      alert("Cart is empty.");
      return;
    }
  
    const payload = {
      items: cart.map(i => ({
        product_id: i.id,
        qty: i.qty
      }))
    };
  
    try {
      const res = await fetch("http://localhost/fitfuel/backend/api/orders/create.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify(payload)
      });
  
      const data = await res.json();
  
      if (!res.ok) {
        alert(data.error || "Checkout failed");
        return;
      }
  
      // clear cart
      localStorage.removeItem(CART_KEY);
  
      // go to success page with order id
      window.location.href = `order_success.html?order_id=${data.order_id}`;
    } catch (e) {
      alert("Network error during checkout");
    }
  });

renderCart();