document.addEventListener("DOMContentLoaded", () => {
  const grid = document.querySelector(".grid-products.row");
  if (!grid) return;

  const queryVars = grid.dataset.query || "{}";
  const perPage = parseInt(grid.dataset.perPage || "24", 10);

  let page = 1;
  let loading = false;
  let hasMore = true;
  let tStart = null; // tiempo cuando el loader se hace visible

  // Loader
  const loader = document.createElement("div");
  loader.className = "infinite-loader";
  loader.innerHTML = `
    <div id="spinner" class="spinner" aria-hidden="true"></div>
    <span class="visually-hidden">Cargando más productos...</span>
  `;

  // Sentinel (cuando aparece en viewport, cargamos más)
  const sentinel = document.createElement("div");
  sentinel.className = "infinite-sentinel";

  grid.after(loader);
  loader.after(sentinel);

  // show/hide loader con delay para evitar parpadeos
  let loaderTimer = null;
  function showLoader(delay = 180) {
    if (loader.classList.contains("active") || loaderTimer) return;
    loaderTimer = setTimeout(() => {
      loader.classList.add("active");
      // capturar el tiempo justo antes de que sea visible el loader
      tStart = performance.now();
      // accesibilidad: indicamos que hay actividad
      loader.setAttribute("role", "status");
      loader.setAttribute("aria-hidden", "false");
      loader.setAttribute("aria-busy", "true");
      loaderTimer = null;
    }, delay);
  }
  function hideLoader() {
    if (loaderTimer) {
      clearTimeout(loaderTimer);
      loaderTimer = null;
      return;
    }
    loader.classList.remove("active");
    loader.removeAttribute("role");
    loader.setAttribute("aria-hidden", "true");
    loader.setAttribute("aria-busy", "false");

    // si el loader ha sido visible, calcular el tiempo que ha estado visible
    if (tStart !== null) {
      const tEnd = performance.now();
      const durationMs = Math.round(tEnd - tStart);
      // console.log(`Loader visible for ${durationMs}ms`);

      // enviar evento a GTM mediante dataLayer
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: "infinite_scroll_loader",
        event_category: "engagement",
        event_label: "Infinite Scroll Loader Duration",
        duration_ms: durationMs,
        duration_seconds: (durationMs / 1000).toFixed(2),
      });

      tStart = null; // reset
    }
  }

  async function loadMore() {
    if (loading || !hasMore) return;
    loading = true;

    // mostramos el loader con delay
    showLoader();

    try {
      const body = new URLSearchParams();
      body.set("action", "infinite_scroll");
      body.set("query_vars", queryVars);
      body.set("page", String(page + 1));
      body.set("per_page", String(perPage));

      const res = await fetch(LOUE.ajax, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        body: body.toString(),
        credentials: "same-origin",
      });
      if (!res.ok) throw new Error(res.statusText);

      const json = await res.json();
      if (!json?.success) throw new Error("AJAX error");

      const wrap = document.createElement("div");
      wrap.innerHTML = json.data?.html || "";

      // selecciona tus columnas y solo añade las que contengan .product
      const cols = wrap.querySelectorAll(".col");
      const frag = document.createDocumentFragment();
      cols.forEach((col) => {
        if (col.querySelector(".product")) {
          frag.appendChild(col);
        }
      });

      if (frag.childNodes.length) {
        grid.appendChild(frag);
        page += 1;
      } else {
        hasMore = false;
      }

      // respeta el flag del backend
      if (!json.data?.has_more) hasMore = false;
      if (!hasMore && io) io.disconnect();
    } catch (e) {
      console.error("Infinite:", e);
      hasMore = false;
      if (io) io.disconnect();
    } finally {
      hideLoader();
      loading = false;
    }
  }

  // IO
  let io = null;
  if ("IntersectionObserver" in window) {
    io = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting) loadMore();
      },
      { rootMargin: "800px 0px" }
    );
    io.observe(sentinel);
  }
});
