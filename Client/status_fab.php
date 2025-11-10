<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

if (empty($_SESSION['user_id'])) {
	return;
}

$widgetCssPath = '../Assets/CSS/status-widget.css';
$widgetCssVersion = file_exists(__DIR__ . '/../Assets/CSS/status-widget.css')
	? (string) filemtime(__DIR__ . '/../Assets/CSS/status-widget.css')
	: (string) time();

$widgetCssHref = $widgetCssPath . '?v=' . rawurlencode($widgetCssVersion);
?>
<div class="status-fab-overlay" data-status-fab-overlay></div>
<div class="status-fab-root" data-status-fab-root
	data-status-css="<?= htmlspecialchars($widgetCssHref, ENT_QUOTES, 'UTF-8'); ?>">
	<button type="button" class="status-fab-button" data-status-fab-button aria-expanded="false"
		aria-controls="statusFabPanel" aria-label="View booking status">
		<span class="status-fab-icon"><i class="fas fa-route" aria-hidden="true"></i></span>
		<span class="status-fab-label" aria-hidden="true">My booking status</span>
		<span class="status-fab-pulse" aria-hidden="true"></span>
	</button>

	<aside class="status-fab-panel" id="statusFabPanel" role="dialog" aria-modal="false" aria-live="polite"
		data-status-fab-panel>
		<div class="status-fab-panel-header">
			<h3>Booking tracker</h3>
			<button type="button" class="status-fab-close" data-status-fab-close aria-label="Close booking tracker">
				<i class="fas fa-times"></i>
			</button>
		</div>
		<div class="status-fab-counts" data-status-fab-counts hidden></div>
		<div class="status-fab-content" data-status-fab-content>
			<div class="status-fab-empty" data-status-fab-empty hidden>
				<i class="fas fa-ghost" aria-hidden="true"></i>
				<span>No bookings yet. Start your DriveXpert journey!</span>
			</div>
			<div class="status-fab-list" data-status-fab-list hidden></div>
			<div class="status-fab-error" data-status-fab-error hidden>
				<strong>We hit a speed bump.</strong>
				<span>Refresh the page or try again in a moment.</span>
			</div>
		</div>
		<div class="status-fab-footer">
			<i class="far fa-clock" aria-hidden="true"></i>
			<span data-status-fab-footer>Last updated just now</span>
		</div>
		<div class="status-fab-footer">
			<i class="fas fa-external-link-alt" aria-hidden="true"></i>
			<a href="client_booking.php">Open full booking dashboard</a>
		</div>
	</aside>
</div>

<script>
	(() => {
		const root = document.querySelector('[data-status-fab-root]');
		if (!root) {
			return;
		}

		const ensureStyles = () => {
			const href = root.dataset.statusCss;
			if (!href) {
				return;
			}
			const alreadyLoaded = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
				.some((link) => link.href.includes('status-widget.css'));
			if (alreadyLoaded) {
				return;
			}
			const linkEl = document.createElement('link');
			linkEl.rel = 'stylesheet';
			linkEl.href = href;
			document.head.appendChild(linkEl);
		};

		ensureStyles();

		const overlay = document.querySelector('[data-status-fab-overlay]');
		const button = root.querySelector('[data-status-fab-button]');
		const panel = root.querySelector('[data-status-fab-panel]');
		const closeBtn = root.querySelector('[data-status-fab-close]');
		const countsEl = root.querySelector('[data-status-fab-counts]');
		const listEl = root.querySelector('[data-status-fab-list]');
		const emptyState = root.querySelector('[data-status-fab-empty]');
		const errorEl = root.querySelector('[data-status-fab-error]');
		const footerTime = root.querySelector('[data-status-fab-footer]');

		let hasLoaded = false;
		let isLoading = false;

		const setLoadingState = (loading) => {
			isLoading = loading;
			button.disabled = loading;
			button.classList.toggle('is-loading', loading);
			if (loading) {
				button.setAttribute('aria-busy', 'true');
			} else {
				button.removeAttribute('aria-busy');
			}
		};

		const formatTimestamp = (isoString) => {
			if (!isoString) {
				return 'Just now';
			}
			const date = new Date(isoString);
			if (Number.isNaN(date.getTime())) {
				return 'Just now';
			}
			return `Updated ${date.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' })}`;
		};

		const renderCounts = (totals = {}) => {
			const parts = [];
			['pending', 'confirmed', 'completed'].forEach((key) => {
				if (typeof totals[key] === 'number') {
					parts.push(`<span class="status-${key}">${key} ${totals[key]}</span>`);
				}
			});
			if (parts.length === 0) {
				countsEl.hidden = true;
				countsEl.innerHTML = '';
				return;
			}
			countsEl.hidden = false;
			countsEl.innerHTML = parts.join('');
		};

		const renderBookings = (bookings = []) => {
			listEl.innerHTML = '';
			if (!bookings.length) {
				listEl.hidden = true;
				emptyState.hidden = false;
				return;
			}
			const fragments = bookings.map((booking) => `
			<article class="status-fab-card">
				<div class="status-pill ${booking.pillClass ?? ''}">${booking.statusLabel ?? 'Status'}</div>
				<h4>${booking.carLabel ?? 'DriveXpert Vehicle'}</h4>
				<div class="status-fab-dates">Pickup ${booking.pickupDate ?? '-'} &bull; Return ${booking.returnDate ?? '-'}</div>
				<time datetime="${booking.createdAt ?? ''}">Reservation #${booking.rentalId ?? ''}</time>
			</article>
		`);
			listEl.hidden = false;
			listEl.innerHTML = fragments.join('');
			emptyState.hidden = true;
		};

		const showError = () => {
			errorEl.hidden = false;
			listEl.hidden = true;
			emptyState.hidden = true;
		};

		const hideError = () => {
			errorEl.hidden = true;
		};

		const openPanel = () => {
			root.classList.add('is-open');
			panel.setAttribute('aria-hidden', 'false');
			button.setAttribute('aria-expanded', 'true');
			overlay?.classList.add('is-active');
		};

		const closePanel = () => {
			root.classList.remove('is-open');
			panel.setAttribute('aria-hidden', 'true');
			button.setAttribute('aria-expanded', 'false');
			overlay?.classList.remove('is-active');
		};

		const togglePanel = () => {
			if (root.classList.contains('is-open')) {
				closePanel();
				return;
			}
			openPanel();
			if (!hasLoaded && !isLoading) {
				fetchData();
			}
		};

		const fetchData = () => {
			setLoadingState(true);
			hideError();
			fetch('booking_status_api.php', {
				credentials: 'same-origin',
				headers: {
					'Accept': 'application/json',
				},
			})
				.then((response) => {
					if (!response.ok) {
						throw new Error(`Request failed with ${response.status}`);
					}
					return response.json();
				})
				.then((data) => {
					hasLoaded = true;
					if (!data || data.authorized === false) {
						showError();
						return;
					}
					renderCounts(data.totals || {});
					renderBookings(data.bookings || []);
					if (footerTime) {
						footerTime.textContent = formatTimestamp(data.lastUpdated || null);
					}
				})
				.catch(() => {
					showError();
				})
				.finally(() => {
					setLoadingState(false);
				});
		};

		button?.addEventListener('click', togglePanel);
		closeBtn?.addEventListener('click', closePanel);
		overlay?.addEventListener('click', closePanel);

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && root.classList.contains('is-open')) {
				closePanel();
			}
		});
	})();
</script>