(() => {
  if (window.__rentalDetailModalInitialized) {
    return;
  }
  window.__rentalDetailModalInitialized = true;

  const modal = document.createElement("div");
  modal.className = "rental-modal rental-modal--hidden";
  modal.innerHTML = [
    '<div class="rental-modal__backdrop" data-modal-action="close"></div>',
    '<div class="rental-modal__panel" role="dialog" aria-modal="true" aria-labelledby="rental-modal-title">',
    '  <button type="button" class="rental-modal__close" aria-label="Close" data-modal-action="close">&times;</button>',
    '  <header class="rental-modal__header">',
    '    <div class="rental-modal__heading">',
    '      <p class="rental-modal__eyebrow">Reservation</p>',
    '      <h2 id="rental-modal-title" class="rental-modal__title"></h2>',
    "    </div>",
    '    <span class="rental-modal__status"></span>',
    "  </header>",
    '  <div class="rental-modal__body"></div>',
    "</div>",
  ].join("");

  document.body.appendChild(modal);

  const panel = modal.querySelector(".rental-modal__panel");
  const body = modal.querySelector(".rental-modal__body");
  const titleEl = modal.querySelector(".rental-modal__title");
  const statusEl = modal.querySelector(".rental-modal__status");

  function escapeHtml(value) {
    if (value === null || value === undefined) {
      return "";
    }
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function formatDate(value) {
    if (!value) {
      return "—";
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return escapeHtml(value);
    }
    return date.toLocaleDateString(undefined, {
      year: "numeric",
      month: "short",
      day: "2-digit",
    });
  }

  function formatCurrency(amount, currency) {
    if (amount === null || amount === undefined) {
      return "—";
    }
    const numeric = Number(amount);
    if (!Number.isFinite(numeric)) {
      return escapeHtml(amount);
    }
    const symbol = currency?.symbol || "$";
    if (currency?.code) {
      try {
        return new Intl.NumberFormat(undefined, {
          style: "currency",
          currency: currency.code,
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        }).format(numeric);
      } catch (err) {
        // Fallback to symbol formatting
      }
    }
    return `${symbol}${numeric.toFixed(2)}`;
  }

  function closeModal() {
    modal.classList.add("rental-modal--hidden");
    panel.setAttribute("aria-hidden", "true");
    body.innerHTML = "";
  }

  function renderList(label, value) {
    return [
      '<div class="rental-modal__item">',
      `  <span class="rental-modal__item-label">${escapeHtml(label)}</span>`,
      `  <span class="rental-modal__item-value">${value}</span>`,
      "</div>",
    ].join("");
  }

  function renderPricing(pricing, currency) {
    const rows = [];
    if (
      pricing.baseRate !== null &&
      pricing.baseRate !== undefined &&
      pricing.baseCost !== null &&
      pricing.baseCost !== undefined
    ) {
      rows.push({
        label: "Base Rate",
        value: `${formatCurrency(pricing.baseRate, currency)} / day`,
      });
      rows.push({
        label: "Base Total",
        value: formatCurrency(pricing.baseCost, currency),
      });
    }

    if (Array.isArray(pricing.extras) && pricing.extras.length > 0) {
      pricing.extras.forEach((extra) => {
        rows.push({
          label: `Add-on · ${extra.name}`,
          value: formatCurrency(extra.totalCost ?? 0, currency),
        });
      });
      rows.push({
        label: "Add-ons Total",
        value: formatCurrency(pricing.extrasTotal ?? 0, currency),
      });
    }

    if (pricing.insurance) {
      rows.push({
        label: `Insurance · ${pricing.insurance.name}`,
        value: formatCurrency(
          pricing.insurance.cost ?? pricing.insuranceTotal ?? 0,
          currency
        ),
      });
    }

    if (Array.isArray(pricing.fines) && pricing.fines.length > 0) {
      pricing.fines.forEach((fine) => {
        rows.push({
          label: `Fine · ${fine.reason}`,
          value: formatCurrency(fine.amount ?? 0, currency),
        });
      });
      rows.push({
        label: "Fines Total",
        value: formatCurrency(pricing.finesTotal ?? 0, currency),
      });
    }

    if (pricing.recordedTotal !== null && pricing.recordedTotal !== undefined) {
      rows.push({
        label: "Recorded Total",
        value: formatCurrency(pricing.recordedTotal, currency),
      });
    }

    if (
      pricing.grandTotal !== null &&
      pricing.grandTotal !== undefined &&
      pricing.grandTotal !== pricing.recordedTotal
    ) {
      rows.push({
        label: "Computed Total",
        value: formatCurrency(pricing.grandTotal, currency),
      });
    }

    return rows.map((entry) => renderList(entry.label, entry.value)).join("");
  }

  function renderModal(data) {
    titleEl.textContent = data?.rentalId
      ? `Reservation #${data.rentalId}`
      : "Reservation Details";

    statusEl.textContent = data?.statusLabel || "";
    statusEl.className = "rental-modal__status";
    if (data?.statusClass) {
      statusEl.classList.add(
        ...String(data.statusClass).split(" ").filter(Boolean)
      );
    }

    const currency = data?.currency || { symbol: "$" };

    const tripSection = [
      '<section class="rental-modal__section">',
      "  <h3>Trip Overview</h3>",
      renderList("Pickup", formatDate(data?.schedule?.from)),
      renderList("Return", formatDate(data?.schedule?.to)),
      renderList(
        "Duration",
        data?.schedule?.durationDays
          ? `${data.schedule.durationDays} day(s)`
          : "—"
      ),
      renderList(
        "Requested",
        data?.requestedAt ? formatDate(data.requestedAt) : "—"
      ),
      "</section>",
    ].join("");

    const customer = data?.customer || {};
    const customerSection = [
      '<section class="rental-modal__section">',
      "  <h3>Customer</h3>",
      renderList("Name", escapeHtml(customer.name || "—")),
      renderList("Email", escapeHtml(customer.email || "—")),
      renderList("Phone", escapeHtml(customer.phone || "—")),
      renderList("NIC", escapeHtml(customer.nic || "—")),
      "</section>",
    ].join("");

    const car = data?.car || {};
    const vehicleSection = [
      '<section class="rental-modal__section">',
      "  <h3>Vehicle</h3>",
      renderList(
        "Model",
        escapeHtml(
          car.label || [car.brand, car.model].filter(Boolean).join(" ") || "—"
        )
      ),
      renderList("Seats", car.seatCount ?? "—"),
      renderList(
        "Max speed",
        car.maxSpeed ? `${escapeHtml(car.maxSpeed)} km/h` : "—"
      ),
      renderList(
        "Efficiency",
        car.efficiency ? `${escapeHtml(car.efficiency)} km/l` : "—"
      ),
      renderList(
        "Daily rate",
        car.rentPerDay !== undefined && car.rentPerDay !== null
          ? formatCurrency(car.rentPerDay, currency)
          : "—"
      ),
      "</section>",
    ].join("");

    const pricingSection = [
      '<section class="rental-modal__section">',
      "  <h3>Pricing</h3>",
      '<div class="rental-modal__pricing">',
      renderPricing(data?.pricing || {}, currency),
      "</div>",
      "</section>",
    ].join("");

    body.innerHTML =
      tripSection + customerSection + vehicleSection + pricingSection;

    modal.classList.remove("rental-modal--hidden");
    panel.setAttribute("aria-hidden", "false");
    panel.focus();
  }

  modal.addEventListener("click", (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) {
      return;
    }
    if (target.dataset.modalAction === "close") {
      closeModal();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (
      event.key === "Escape" &&
      !modal.classList.contains("rental-modal--hidden")
    ) {
      closeModal();
    }
  });

  document.addEventListener("click", (event) => {
    const trigger =
      event.target instanceof HTMLElement
        ? event.target.closest("[data-rental-details]")
        : null;
    if (!trigger) {
      return;
    }
    const payload = trigger.getAttribute("data-rental-details");
    if (!payload) {
      return;
    }
    try {
      const data = JSON.parse(payload);
      renderModal(data);
    } catch (error) {
      console.error("Unable to display rental details modal", error);
    }
  });
})();
