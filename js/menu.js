// MENU & NAV INTERACTIONS + CARROUSEL + ANIMATIONS
// - toggles mobile nav
// - loads dishes from get_dishes.php (JSON)
// - filters by category with smooth reveal
// - carrousel d'images automatique
// - animations au scroll

document.addEventListener("DOMContentLoaded", () => {
  // ============================================
  // 1. NAVIGATION MOBILE
  // ============================================
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

  // ============================================
  // 2. CHARGER LE MENU (si on est sur la page menu)
  // ============================================
  if (document.getElementById("menu-container")) {
    loadMenu().then(setupFilters);
  }

  // ============================================
  // 3. CARROUSEL D'IMAGES
  // ============================================
  initCarousel();

  // ============================================
  // 4. ANIMATIONS AU SCROLL
  // ============================================
  initScrollAnimations();

  // ============================================
  // 5. NAVBAR EFFET SCROLL
  // ============================================
  initNavbarScroll();
});

// ============================================
// FONCTION: Charger les plats depuis le serveur
// ============================================
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

// ============================================
// FONCTION: Afficher les plats
// ============================================
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

// ============================================
// FONCTION: Filtres de catégories
// ============================================
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

// ============================================
// FONCTION: Utilitaires
// ============================================
function escapeHtml(s) {
  return String(s).replace(
    /[&<>"']/g,
    (m) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[
        m
      ])
  );
}

// ============================================
// FONCTION: Données de démo
// ============================================
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

// ============================================
// CARROUSEL D'IMAGES AUTOMATIQUE
// ============================================
function initCarousel() {
  const carousel = document.querySelector('.menu-header-carousel');
  if (!carousel) return;

  const slides = carousel.querySelectorAll('.carousel-slide');
  const indicators = carousel.querySelectorAll('.carousel-indicator');
  
  if (slides.length === 0) return;

  let currentSlide = 0;
  let autoplayInterval = null;
  const intervalTime = 2500; 

  // Fonction pour afficher un slide
  function showSlide(index) {
    // Retirer active de tous
    slides.forEach(s => s.classList.remove('active'));
    indicators.forEach(i => i.classList.remove('active'));

    // Ajouter active au slide courant
    slides[index].classList.add('active');
    indicators[index].classList.add('active');
    
    currentSlide = index;
  }

  // Fonction pour passer au slide suivant
  function nextSlide() {
    const next = (currentSlide + 1) % slides.length;
    showSlide(next);
  }

  // Démarrer l'autoplay
  function startAutoplay() {
    stopAutoplay();
    if (slides.length > 1) {
      autoplayInterval = setInterval(nextSlide, intervalTime);
    }
  }

  // Arrêter l'autoplay
  function stopAutoplay() {
    if (autoplayInterval) {
      clearInterval(autoplayInterval);
      autoplayInterval = null;
    }
  }

  // Event listeners sur les indicateurs
  indicators.forEach((indicator, index) => {
    indicator.addEventListener('click', () => {
      stopAutoplay();
      showSlide(index);
      startAutoplay();
    });
  });

  // Pause au hover
  carousel.addEventListener('mouseenter', stopAutoplay);
  carousel.addEventListener('mouseleave', startAutoplay);

  // Pause si l'onglet n'est pas visible
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      stopAutoplay();
    } else {
      startAutoplay();
    }
  });

  // Démarrer
  showSlide(0);
  startAutoplay();

  console.log('🎠 Carrousel initialisé');
}

// ============================================
// ANIMATIONS AU SCROLL (Intersection Observer)
// ============================================
function initScrollAnimations() {
  const observerOptions = {
    threshold: 0.15,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, observerOptions);

  // Observer tous les éléments avec classes d'animation
  document.querySelectorAll('.animate-on-scroll, .fade-left, .fade-right, .zoom-in').forEach(el => {
    observer.observe(el);
  });

  console.log('✨ Animations au scroll initialisées');
}

// ============================================
// NAVBAR - EFFET AU SCROLL
// ============================================
function initNavbarScroll() {
  const navbar = document.querySelector('.navbar');
  if (!navbar) return;

  window.addEventListener('scroll', () => {
    if (window.pageYOffset > 80) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  });
}