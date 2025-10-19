// MENU & NAV INTERACTIONS
// - toggles mobile nav
// - loads dishes from get_dishes.php (JSON)
// - filters by category with smooth reveal

document.addEventListener("DOMContentLoaded", () => {
  const hamburger = document.querySelector(".hamburger");
  const navMenu = document.querySelector(".nav-menu");

  if (hamburger && navMenu) {
    hamburger.addEventListener("click", () =>
      navMenu.classList.toggle("active")
    );
    document
      .querySelectorAll(".nav-link")
      .forEach((a) =>
        a.addEventListener("click", () => navMenu.classList.remove("active"))
      );
  }

  if (document.getElementById("menu-container")) {
    loadMenu().then(setupFilters);
  }
});

// fetch dishes from server; fallback to demo data if error
async function loadMenu() {
  const container = document.getElementById("menu-container");
  try {
    const res = await fetch("get_dishes.php", {
      headers: { Accept: "application/json" },
    });
    if (!res.ok) throw new Error("HTTP " + res.status);
    const dishes = await res.json();
    renderMenu(Array.isArray(dishes) && dishes.length ? dishes : demoData());
  } catch (e) {
    console.warn("DB unreachable, using demo data:", e);
    renderMenu(demoData());
  }
}

// render items grid
function renderMenu(dishes) {
  const container = document.getElementById("menu-container");
  container.innerHTML = dishes
    .map(
      (d, i) => `
    <article class="menu-item" data-category="${
      d.categorie || ""
    }" style="animation-delay:${i * 0.08}s">
      <img class="menu-item-image" src="${d.image_url || ""}" alt="${escapeHtml(
        d.nom || "Plat"
      )}"
           onerror="this.src='https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&w=900&q=80'">
      <div class="menu-item-content">
        <span class="menu-item-category">${d.categorie || ""}</span>
        <div class="menu-item-header">
          <h3 class="menu-item-name">${escapeHtml(d.nom || "")}</h3>
          <div class="menu-item-price">${Number(d.prix || 0).toFixed(2)}€</div>
        </div>
        <p class="menu-item-description">${escapeHtml(d.description || "")}</p>
      </div>
    </article>
  `
    )
    .join("");
}

// filters
function setupFilters() {
  document.querySelectorAll(".filter-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      document
        .querySelectorAll(".filter-btn")
        .forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      filterMenu(btn.dataset.filter);
    });
  });
}
function filterMenu(category) {
  document.querySelectorAll(".menu-item").forEach((item, i) => {
    const show = category === "all" || item.dataset.category === category;
    item.classList.toggle("hidden", !show);
    if (show) {
      item.style.opacity = "1";
      item.style.transform = "translateY(0)";
      item.style.animationDelay = `${i * 0.05}s`;
    }
  });
}

// small utils
function escapeHtml(s) {
  return String(s).replace(
    /[&<>"']/g,
    (m) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[
        m
      ])
  );
}

// demo data if DB empty/offline
function demoData() {
  return [
    {
      id: 1,
      nom: "Foie Gras Poêlé",
      description: "Foie gras, chutney de figues, brioche",
      prix: "28.50",
      categorie: "Entrée",
      image_url:
        "https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&w=900&q=80",
    },
    {
      id: 2,
      nom: "Bœuf de Charolais",
      description: "Côte de bœuf grillée, légumes de saison",
      prix: "32.00",
      categorie: "Plat",
      image_url:
        "https://images.unsplash.com/photo-1558030006-450675393462?auto=format&fit=crop&w=900&q=80",
    },
    {
      id: 3,
      nom: "Tarte Tatin",
      description: "Aux pommes, glace vanille",
      prix: "12.00",
      categorie: "Dessert",
      image_url:
        "https://images.unsplash.com/photo-1551024506-0bccd828d307?auto=format&fit=crop&w=900&q=80",
    },
    {
      id: 4,
      nom: "Champagne Brut",
      description: "Cuvée spéciale",
      prix: "12.00",
      categorie: "Boisson",
      image_url:
        "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=900&q=80",
    },
  ];
}
