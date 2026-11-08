    <?php if ($showFooter ?? true): ?>
    <!-- Universal Footer -->
    <footer class="border-top py-4 mt-auto small" style="background-color: #FFF6F6; border-color: #D5C5B5 !important; color: #2B2B2B;">
        <div class="container-fluid px-lg-4 text-center text-md-start">
            <div class="row align-items-center gy-3">
                <div class="col-md-6">
                    <span class="fw-bold" style="color: #894E3F;">Raeden’s Bakery and Café</span> • Raeden's cafe POS with Online Reservation and QR Code
                    <div class="text-muted" style="font-size: 0.8rem;">Artisanal Bakery, Fresh Bakes &amp; Specialty Café • Powered by QR Code Pass &amp; POS Integration</div>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="badge border me-2" style="background-color: #EEE1BA; color: #2B2B2B;">Online Reservations</span>
                    <span class="badge border me-2" style="background-color: #EEE1BA; color: #2B2B2B;">POS &amp; QR Pass</span>
                    <span class="text-muted" style="font-size: 0.8rem;">&copy; <?= date('Y') ?> Raeden’s Bakery and Café</span>
                </div>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <!-- Bootstrap 5.3.3 Fallback Check -->
    <script>
    if (typeof bootstrap === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"><\/script>');
    }
    </script>

    <!-- QRCode.js CDN for Dynamic QR Pass rendering -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <!-- Global Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
        <div id="isatuToast" class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2" id="toastMessage">
                    <i class="bi bi-info-circle-fill text-warning"></i> <span>Notification</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Global Helper JS & Dynamic Router -->
    <script>
        // SweetAlert2 Toast Mixin (High-performance floating notifications)
        const isatuSwalToast = (typeof Swal !== 'undefined') ? Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        }) : null;

        /**
         * Global Unified Notification Helper (SweetAlert2 Toast with Native Bootstrap Fallback)
         */
        function showToast(message, type = 'info') {
            if (typeof Swal !== 'undefined' && isatuSwalToast) {
                let swalIcon = 'info';
                if (type === 'success') swalIcon = 'success';
                else if (type === 'danger' || type === 'error') swalIcon = 'error';
                else if (type === 'warning') swalIcon = 'warning';
                else if (type === 'question') swalIcon = 'question';

                isatuSwalToast.fire({
                    icon: swalIcon,
                    title: message
                });
                return;
            }

            // Fallback to Bootstrap native toast if SWAL CDN is unreachable
            const toastEl = document.getElementById('isatuToast');
            const toastMsg = document.getElementById('toastMessage');
            if (!toastEl || !toastMsg) return;

            toastEl.className = `toast align-items-center text-white bg-${type} border-0 shadow-lg rounded-3`;
            toastMsg.innerHTML = message;
            const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
            toast.show();
        }

        /**
         * Global Unified Confirmation Dialog Helper (SweetAlert2 Modal with Native confirm() Fallback)
         */
        async function showConfirm({
            title = 'Are you sure?',
            text = 'This action cannot be undone.',
            icon = 'warning',
            confirmButtonText = 'Yes, Proceed',
            cancelButtonText = 'Cancel',
            isDestructive = false
        } = {}) {
            if (typeof Swal === 'undefined') {
                return { isConfirmed: window.confirm(`${title}\n\n${text}`) };
            }

            const confirmBtnClass = isDestructive 
                ? 'btn btn-danger px-4 py-2 rounded-pill fw-bold' 
                : 'btn btn-navy px-4 py-2 rounded-pill fw-bold';

            return Swal.fire({
                title: title,
                html: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonText: confirmButtonText,
                cancelButtonText: cancelButtonText,
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-4 shadow-lg border-0 p-4',
                    title: 'fw-black text-navy fs-4',
                    htmlContainer: 'text-secondary small mt-2',
                    confirmButton: confirmBtnClass,
                    cancelButton: 'btn btn-outline-secondary px-4 py-2 rounded-pill fw-bold ms-2',
                    actions: 'mt-3 gap-2'
                }
            });
        }

        /**
         * Global Staff Sign Out Helper with SweetAlert2 Confirmation
         */
        async function globalStaffLogout() {
            const confirmRes = await showConfirm({
                title: 'Sign Out of Station?',
                text: 'Are you sure you want to end your current staff operational session?',
                icon: 'question',
                confirmButtonText: '<i class="bi bi-box-arrow-right me-1"></i> Yes, Sign Out',
                cancelButtonText: 'Stay Signed In'
            });

            if (!confirmRes.isConfirmed) return;

            try {
                const res = await fetch('api/auth.php?action=logout', { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    showToast('You have signed out successfully.', 'info');
                    setTimeout(() => {
                        window.location.href = 'login';
                    }, 500);
                } else {
                    window.location.href = 'login';
                }
            } catch (err) {
                console.warn('Logout network error, forcing page redirect:', err);
                window.location.href = 'login';
            }
        }

        // Format currency helper
        function formatPeso(val) {
            return '₱' + parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Universal HTML Escaper
        window.escapeHtml = window.escapeHtml || function(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
        };

        // Re-initialize Bootstrap Interactive Dropdowns
        function refreshBootstrapDropdowns() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                document.querySelectorAll('.dropdown-menu.show').forEach(el => el.classList.remove('show'));
                document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(el) {
                    try {
                        bootstrap.Dropdown.getOrCreateInstance(el);
                    } catch(e) {}
                });
            }
        }

        document.addEventListener('DOMContentLoaded', refreshBootstrapDropdowns);

        // Dynamic Single Page Loader (Native JS)
        async function loadPageDynamically(url, pushState = true) {
            const loadingBar = document.getElementById('app-loading-bar');
            const container = document.getElementById('app-content-container');
            if (!container) {
                window.location.href = url;
                return;
            }

            // If already on the requested page, smoothly scroll to top without re-fetching or re-evaluating
            const currentNav = container.getAttribute('data-current-page') || '';
            const isBookingTarget = (url === 'index.php?page=booking' || url === 'booking' || url === 'index.php');
            if (isBookingTarget && (currentNav === 'reservation' || currentNav === 'booking')) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
                const navCollapse = document.getElementById('raedensTopNav') || document.getElementById('isatuNav');
                if (navCollapse && navCollapse.classList.contains('show') && typeof bootstrap !== 'undefined') {
                    const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
                    if (bsCollapse) bsCollapse.hide();
                }
                return;
            }

            try {
                if (loadingBar) {
                    loadingBar.style.opacity = '1';
                    loadingBar.style.width = '35%';
                }

                // Dispatch pageUnload event so previous views can cleanly tear down timers/cameras
                window.dispatchEvent(new CustomEvent('pageUnload'));

                // Build ajax URL
                const fetchUrl = url + (url.includes('?') ? '&' : '?') + 'ajax=1';
                const res = await fetch(fetchUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!res.ok) {
                    window.location.href = url;
                    return;
                }

                if (loadingBar) loadingBar.style.width = '75%';

                const html = await res.text();
                const newTitle = res.headers.get('X-Page-Title') ? decodeURIComponent(res.headers.get('X-Page-Title')) : '';
                const newNav = res.headers.get('X-Active-Nav') || '';
                const newBodyClass = res.headers.get('X-Body-Class') || 'bg-light text-dark';

                // Update container content
                container.innerHTML = html;
                container.setAttribute('data-current-page', newNav);

                // Update title
                if (newTitle) {
                    document.title = newTitle + ' • Raeden’s Resto, Bakery, Cafe';
                    const pageTitleEl = document.getElementById('document-page-title');
                    if (pageTitleEl) pageTitleEl.textContent = document.title;
                }

                // Update body class
                if (newBodyClass) {
                    document.body.className = newBodyClass + ' d-flex flex-column min-vh-100';
                }

                // Update active nav styling
                document.querySelectorAll('.isatu-navbar .nav-link').forEach(navLink => {
                    const pageAttr = navLink.getAttribute('data-page');
                    const isActive = (newNav === 'reservation' && (pageAttr === 'booking' || pageAttr === 'reservation')) ||
                                     (newNav === pageAttr);
                    if (isActive) {
                        navLink.classList.add('active', 'fw-bold');
                    } else {
                        navLink.classList.remove('active', 'fw-bold');
                    }
                });

                // Close mobile navbar if open
                const navCollapse = document.getElementById('raedensTopNav') || document.getElementById('isatuNav');
                if (navCollapse && navCollapse.classList.contains('show') && typeof bootstrap !== 'undefined') {
                    const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
                    if (bsCollapse) bsCollapse.hide();
                }

                // Safely re-execute scripts within dynamic container so event listeners & initializers run cleanly
                const scripts = Array.from(container.querySelectorAll('script'));
                scripts.forEach(oldScript => {
                    try {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        if (oldScript.src) {
                            newScript.src = oldScript.src;
                            document.head.appendChild(newScript);
                        } else {
                            const code = oldScript.innerHTML.trim();
                            if (code) {
                                newScript.textContent = code;
                                document.body.appendChild(newScript);
                            }
                        }
                        oldScript.remove();
                    } catch (err) {
                        console.warn('Dynamic script execution caught:', err);
                    }
                });

                // Refresh Bootstrap Dropdowns after DOM mutation
                refreshBootstrapDropdowns();

                // Update URL state
                if (pushState) {
                    history.pushState({ url: url }, '', url);
                }

                // Scroll to top smoothly
                window.scrollTo({ top: 0, behavior: 'smooth' });

                if (loadingBar) {
                    loadingBar.style.width = '100%';
                    setTimeout(() => {
                        loadingBar.style.opacity = '0';
                        setTimeout(() => { loadingBar.style.width = '0%'; }, 200);
                    }, 150);
                }
            } catch (err) {
                console.error('Dynamic load error, falling back to full navigation:', err);
                window.location.href = url;
            }
        }

        // Global click interceptor for dynamic navigation
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;

            // Allow modifier keys or external targets
            if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || link.target === '_blank') return;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
            if (href.startsWith('http://') || href.startsWith('https://')) return;

            // Standalone dedicated operational stations & auth pages navigate natively
            if (
                href === 'admin' || href === 'admin.php' || href.startsWith('admin?') || href.startsWith('admin.php?') ||
                href === 'pos' || href === 'pos.php' || href.startsWith('pos?') || href.startsWith('pos.php?') ||
                href === 'kitchen' || href === 'kitchen.php' || href.startsWith('kitchen?') || href.startsWith('kitchen.php?') ||
                href === 'scanner' || href === 'scanner.php' || href.startsWith('scanner?') || href.startsWith('scanner.php?') ||
                href === 'login' || href === 'login.php' || href.startsWith('login?') || href.startsWith('login.php?')
            ) {
                return;
            }

            // Check if it's an internal application route for customer SPA flow
            let targetUrl = null;
            if (href.startsWith('index.php?page=')) {
                targetUrl = href;
            } else if (href === 'index.php' || href === 'booking' || href === 'booking.php') {
                targetUrl = 'index.php?page=booking';
            } else if (href.startsWith('pass?') || href.startsWith('pass.php?')) {
                targetUrl = 'index.php?page=pass&' + href.split('?')[1];
            } else if (href === 'pass' || href === 'pass.php') {
                targetUrl = 'index.php?page=pass';
            } else if (link.getAttribute('data-dynamic') === 'true') {
                targetUrl = href;
            }

            if (targetUrl) {
                e.preventDefault();
                loadPageDynamically(targetUrl);
            }
        });

        // Global handler for interactive .addon-card checkboxes
        document.addEventListener('change', function(e) {
            if (e.target && e.target.type === 'checkbox' && e.target.closest('.addon-card')) {
                const card = e.target.closest('.addon-card');
                if (card) {
                    card.classList.toggle('addon-card-checked', e.target.checked);
                }
            }
        });

        // Handle Browser Back / Forward buttons
        window.addEventListener('popstate', function(e) {
            if (e.state && e.state.url) {
                loadPageDynamically(e.state.url, false);
            } else {
                loadPageDynamically(window.location.href, false);
            }
        });
    </script>
</body>
</html>
