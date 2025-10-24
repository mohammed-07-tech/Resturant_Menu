// ADMIN INTERACTIONS (dashboard modals, validation, previews)
// Make functions available globally so inline onclick="" works.

(function () {
  /* open/close modal */
  function openModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.add("show");
    document.body.style.overflow = "hidden";
    setTimeout(() => {
      const el = m.querySelector("input,select,textarea");
      el && el.focus();
    }, 120);
  }
  function closeModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.remove("show");
    document.body.style.overflow = "";
    const f = m.querySelector("form");
    if (!f) return;
    f.reset();
    f.querySelectorAll(".form-group").forEach((g) =>
      g.classList.remove("has-error")
    );
    const prev = f.querySelector(".image-preview");
    prev && prev.remove();
  }

  /* field validation */
  function validateField(input) {
    const group = input.closest(".form-group");
    const val = (input.value || "").trim();
    let ok = true,
      msg = "";
    if (input.hasAttribute("required") && !val) {
      ok = false;
      msg = "Ce champ est obligatoire";
    }
    if (
      ok &&
      input.type === "email" &&
      val &&
      !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)
    ) {
      ok = false;
      msg = "Email invalide";
    }
    if (ok && input.type === "number") {
      const n = Number(val);
      if (isNaN(n) || n < 0) {
        ok = false;
        msg = "Valeur ≥ 0 requise";
      }
    }
    group.classList.toggle("has-error", !ok);
    let err = group.querySelector(".error-text");
    if (!ok) {
      if (!err) {
        err = document.createElement("div");
        err.className = "error-text";
        group.appendChild(err);
      }
      err.textContent = msg;
      input.style.borderColor = "#dc3545";
    } else {
      err && err.remove();
      input.style.borderColor = "var(--border)";
    }
    return ok;
  }

  function hookValidation(form) {
    form.addEventListener("submit", (e) => {
      const fields = form.querySelectorAll("input,select,textarea");
      let ok = true;
      fields.forEach((f) => {
        if (!validateField(f)) ok = false;
      });
      if (!ok) {
        e.preventDefault();
        const first = form.querySelector(
          ".has-error input, .has-error select, .has-error textarea"
        );
        first && first.focus();
      }
    });
    form.querySelectorAll("input,select,textarea").forEach((f) => {
      f.addEventListener("blur", () => validateField(f));
      f.addEventListener("input", () => {
        const g = f.closest(".form-group");
        g && g.classList.remove("has-error");
        f.style.borderColor = "var(--border)";
      });
    });
  }

  /* image preview from URL */
  function previewImage(input, previewId) {
    const url = (input.value || "").trim();
    const old = document.getElementById(previewId);
    old && old.remove();
    if (!url) return;
    const wrap = document.createElement("div");
    wrap.id = previewId;
    wrap.className = "image-preview";
    const img = document.createElement("img");
    img.alt = "Aperçu";
    img.src = url;
    img.onerror = () =>
      (wrap.innerHTML =
        '<p style="color:var(--danger)">Image non disponible</p>');
    wrap.appendChild(img);
    input.parentNode.appendChild(wrap);
  }

  /* confirm delete */
  function confirmDelete(message, onConfirm) {
    const modal = document.createElement("div");
    modal.className = "modal show";
    modal.innerHTML = `
      <div class="modal-content" style="max-width:420px">
        <div class="modal-header">
          <h2><i class="fas fa-exclamation-triangle"></i> Confirmation</h2>
          <button class="close" onclick="this.closest('.modal').remove();document.body.style.overflow=''">&times;</button>
        </div>
        <div class="modal-body">
          <p style="margin-bottom:20px">${message}</p>
          <div style="display:flex;gap:10px;justify-content:flex-end">
            <button class="btn btn-secondary" onclick="this.closest('.modal').remove();document.body.style.overflow=''">Annuler</button>
            <button class="btn btn-danger" onclick="__confirm()">Supprimer</button>
          </div>
        </div>
      </div>`;
    document.body.appendChild(modal);
    window.__confirm = function () {
      modal.remove();
      document.body.style.overflow = "";
      onConfirm && onConfirm();
      delete window.__confirm;
    };
  }

  /* page bootstrap */
  function initAdmin() {
    document.querySelectorAll("form.needs-validate").forEach(hookValidation);

    // live previews
    const addUrl = document.getElementById("add_image_url");
    addUrl &&
      addUrl.addEventListener("input", () =>
        previewImage(addUrl, "add_preview")
      );
    const editUrl = document.getElementById("edit_image_url");
    editUrl &&
      editUrl.addEventListener("input", () =>
        previewImage(editUrl, "edit_preview")
      );

    // fallback: any element with data-open-* opens the modal
    document
      .querySelectorAll("[data-open-add-modal]")
      .forEach((b) => b.addEventListener("click", () => openModal("addModal")));
    document
      .querySelectorAll("[data-open-edit-modal]")
      .forEach((b) =>
        b.addEventListener("click", () => openModal("editModal"))
      );
  }

  // expose to global (fixes "button not working" when inline onclick is used)
  window.openModal = openModal;
  window.closeModal = closeModal;
  window.confirmDelete = confirmDelete;
  window.previewImage = previewImage;
  window.initAdmin = initAdmin;

  // auto-init when DOM is ready
  document.addEventListener("DOMContentLoaded", initAdmin);
})();