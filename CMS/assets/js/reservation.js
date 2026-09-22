/**
 * ISAT U Coffee Shop - Customer Online Reservation & Pre-Ordering Controller
 */

let selectedTable = null;
let selectedTimeSlot = '10:00 AM - 12:00 PM';
let reservationCart = [];
let activeZoneFilter = 'all';

document.addEventListener('DOMContentLoaded', () => {
    initTableGrid();
    initPreOrderMenu();
    initDateControls();
    initEventListeners();

    // Listen for storage updates
    window.cafeStore.subscribe((type) => {
        if (type === 'TABLE_UPDATED' || type === 'RESERVATION_CREATED' || type === 'DATA_RESET') {
            renderTables();
        }
    });
});

function initDateControls() {
    const dateInput = document.getElementById('res-date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        dateInput.value = today;
    }
}

function initEventListeners() {
    // Zone Filter buttons
    const zonePills = document.querySelectorAll('.zone-pill');
    zonePills.forEach(pill => {
        pill.addEventListener('click', (e) => {
            zonePills.forEach(p => p.classList.remove('bg-blue-900', 'text-amber-400', 'active'));
            zonePills.forEach(p => p.classList.add('bg-white', 'text-slate-600'));
            
            pill.classList.remove('bg-white', 'text-slate-600');
            pill.classList.add('bg-blue-900', 'text-amber-400', 'active');
            
            activeZoneFilter = pill.dataset.zone || 'all';
            renderTables();
        });
    });

    // Time slot buttons
    const timeButtons = document.querySelectorAll('.time-slot-btn');
    timeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            timeButtons.forEach(b => {
                b.classList.remove('border-blue-900', 'bg-blue-50', 'text-blue-900', 'font-bold');
                b.classList.add('border-slate-200', 'text-slate-700');
            });
            btn.classList.add('border-blue-900', 'bg-blue-50', 'text-blue-900', 'font-bold');
            btn.classList.remove('border-slate-200');
            selectedTimeSlot = btn.dataset.time || btn.innerText;
        });
    });

    // Customer Type ID trigger
    const customerTypeSelect = document.getElementById('res-customer-type');
    const idInputWrapper = document.getElementById('res-id-number-wrapper');
    if (customerTypeSelect && idInputWrapper) {
        customerTypeSelect.addEventListener('change', () => {
            if (customerTypeSelect.value === 'Student' || customerTypeSelect.value === 'Faculty') {
                idInputWrapper.classList.remove('hidden');
            } else {
                idInputWrapper.classList.add('hidden');
            }
        });
    }

    // Reservation Form Submission
    const reservationForm = document.getElementById('reservation-form');
    if (reservationForm) {
        reservationForm.addEventListener('submit', handleReservationSubmit);
    }
}

// ==================== TABLE SELECTION ====================
function initTableGrid() {
    renderTables();
}

function renderTables() {
    const tableGrid = document.getElementById('tables-grid');
    if (!tableGrid) return;

    const tables = window.cafeStore.getTables();
    const filtered = activeZoneFilter === 'all' 
        ? tables 
        : tables.filter(t => t.zone.toLowerCase().includes(activeZoneFilter.toLowerCase()));

    tableGrid.innerHTML = '';

    filtered.forEach(table => {
        const isSelected = selectedTable && selectedTable.id === table.id;
        const isAvailable = table.status === 'available';
        const isReserved = table.status === 'reserved';
        const isOccupied = table.status === 'occupied';

        let statusClass = 'table-available';
        let statusBadge = '<span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Available</span>';
        
        if (isReserved) {
            statusClass = 'table-reserved';
            statusBadge = '<span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Reserved</span>';
        } else if (isOccupied) {
            statusClass = 'table-occupied';
            statusBadge = '<span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-200 text-slate-700">Occupied</span>';
        } else if (isSelected) {
            statusClass = 'table-selected';
            statusBadge = '<span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-900 text-amber-400">Selected</span>';
        }

        const card = document.createElement('div');
        card.className = `table-node relative p-4 rounded-xl shadow-sm border ${statusClass} flex flex-col justify-between`;
        card.innerHTML = `
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">${table.zone}</span>
                    <h4 class="text-lg font-bold text-slate-800 font-heading mt-0.5">${table.name}</h4>
                </div>
                ${statusBadge}
            </div>
            
            <p class="text-xs text-slate-600 mt-2 line-clamp-2">${table.description}</p>

            <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs text-slate-600">
                <span class="flex items-center gap-1 font-medium">
                    <i data-lucide="users" class="w-3.5 h-3.5 text-blue-900"></i> Up to ${table.capacity} Pax
                </span>
                <span class="font-semibold ${isSelected ? 'text-blue-900' : 'text-slate-500'}">
                    ${isAvailable ? (isSelected ? '✓ Chosen' : 'Click to Select') : 'Unavailable'}
                </span>
            </div>
        `;

        if (isAvailable) {
            card.addEventListener('click', () => {
                selectedTable = table;
                renderTables();
                updateSelectedTableSummary();
            });
        }

        tableGrid.appendChild(card);
    });

    if (window.lucide) {
        window.lucide.createIcons();
    }
}

function updateSelectedTableSummary() {
    const banner = document.getElementById('selected-table-banner');
    const nameEl = document.getElementById('selected-table-name');
    const capEl = document.getElementById('selected-table-cap');
    const zoneEl = document.getElementById('selected-table-zone');

    if (banner && selectedTable) {
        banner.classList.remove('hidden');
        nameEl.innerText = selectedTable.name;
        capEl.innerText = `${selectedTable.capacity} Guests Max`;
        zoneEl.innerText = selectedTable.zone;
    }
}

// ==================== PRE-ORDER MENU ====================
function initPreOrderMenu() {
    renderPreOrderCatalog();
    renderPreOrderCart();
}

function renderPreOrderCatalog() {
    const container = document.getElementById('preorder-products');
    if (!container) return;

    const products = window.cafeStore.getProducts().slice(0, 6);
    container.innerHTML = '';

    products.forEach(p => {
        const item = document.createElement('div');
        item.className = 'flex items-center gap-3 p-3 bg-white rounded-xl border border-slate-200 hover:border-amber-400 transition-all shadow-sm';
        item.innerHTML = `
            <img src="${p.image}" alt="${p.name}" class="w-16 h-16 object-cover rounded-lg flex-shrink-0" />
            <div class="flex-1 min-w-0">
                <h5 class="text-sm font-bold text-slate-800 truncate">${p.name}</h5>
                <p class="text-xs text-slate-500">₱${p.price.toFixed(2)}</p>
                <span class="inline-block mt-1 text-[10px] px-2 py-0.5 bg-amber-50 text-amber-800 rounded font-medium">${p.badge || 'Available'}</span>
            </div>
            <button type="button" class="add-preorder-btn px-3 py-1.5 bg-blue-900 hover:bg-blue-950 text-amber-400 text-xs font-bold rounded-lg shadow transition-all flex items-center gap-1" data-id="${p.id}">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add
            </button>
        `;

        item.querySelector('.add-preorder-btn').addEventListener('click', () => {
            addToPreOrderCart(p);
        });

        container.appendChild(item);
    });

    if (window.lucide) {
        window.lucide.createIcons();
    }
}

function addToPreOrderCart(product) {
    const existing = reservationCart.find(i => i.id === product.id);
    if (existing) {
        existing.qty += 1;
    } else {
        reservationCart.push({
            id: product.id,
            productId: product.id,
            name: product.name,
            price: product.price,
            size: product.hasCustomizations && product.sizes.length ? product.sizes[0].name : 'Standard',
            sugar: '50%',
            ice: 'Normal Ice',
            addons: [],
            qty: 1
        });
    }
    renderPreOrderCart();
}

function renderPreOrderCart() {
    const list = document.getElementById('preorder-cart-list');
    const totalEl = document.getElementById('preorder-cart-total');
    const emptyEl = document.getElementById('preorder-cart-empty');
    if (!list || !totalEl) return;

    list.innerHTML = '';
    
    if (reservationCart.length === 0) {
        if (emptyEl) emptyEl.classList.remove('hidden');
        totalEl.innerText = '₱0.00';
        return;
    }

    if (emptyEl) emptyEl.classList.add('hidden');

    let total = 0;
    reservationCart.forEach((item, index) => {
        const itemTotal = item.price * item.qty;
        total += itemTotal;

        const row = document.createElement('div');
        row.className = 'flex items-center justify-between p-2 text-xs bg-slate-50 rounded-lg border border-slate-200';
        row.innerHTML = `
            <div class="flex-1 pr-2">
                <p class="font-bold text-slate-800">${item.name}</p>
                <p class="text-[11px] text-slate-500">${item.size} • Qty: ${item.qty}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-bold text-blue-900">₱${itemTotal.toFixed(2)}</span>
                <button type="button" class="text-rose-500 hover:text-rose-700 p-1" data-index="${index}">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        `;

        row.querySelector('button').addEventListener('click', () => {
            reservationCart.splice(index, 1);
            renderPreOrderCart();
        });

        list.appendChild(row);
    });

    totalEl.innerText = `₱${total.toFixed(2)}`;

    if (window.lucide) {
        window.lucide.createIcons();
    }
}

// ==================== SUBMISSION & QR GENERATION ====================
function handleReservationSubmit(e) {
    e.preventDefault();

    if (!selectedTable) {
        alert('Please select an available table or study pod on the floor plan.');
        document.getElementById('tables-grid').scrollIntoView({ behavior: 'smooth' });
        return;
    }

    const name = document.getElementById('res-name').value.trim();
    const phone = document.getElementById('res-phone').value.trim();
    const email = document.getElementById('res-email').value.trim();
    const customerType = document.getElementById('res-customer-type').value;
    const idNumber = document.getElementById('res-id-number')?.value.trim() || 'N/A';
    const date = document.getElementById('res-date').value;
    const guests = parseInt(document.getElementById('res-guests').value) || 1;
    const notes = document.getElementById('res-notes')?.value.trim() || '';

    if (!name || !phone) {
        alert('Please provide your name and contact phone number.');
        return;
    }

    const totalAmount = reservationCart.reduce((sum, i) => sum + (i.price * i.qty), 0);

    const reservationPayload = {
        customerName: name,
        customerType: customerType,
        idNumber: idNumber,
        phone: phone,
        email: email || 'student@isatu.edu.ph',
        tableId: selectedTable.id,
        tableName: selectedTable.name,
        date: date,
        timeSlot: selectedTimeSlot,
        guests: guests,
        notes: notes,
        preOrderItems: [...reservationCart],
        totalAmount: totalAmount,
        paymentStatus: totalAmount > 0 ? 'Pre-order (Pay at Counter / QR)' : 'Table Reserved'
    };

    const newRes = window.cafeStore.createReservation(reservationPayload);

    // Show QR Ticket Modal
    showQRTicketModal(newRes);

    // Reset Form
    e.target.reset();
    selectedTable = null;
    reservationCart = [];
    renderTables();
    renderPreOrderCart();
}

function showQRTicketModal(reservation) {
    const modal = document.getElementById('qr-pass-modal');
    if (!modal) return;

    document.getElementById('ticket-res-id').innerText = reservation.id;
    document.getElementById('ticket-qr-code').innerText = reservation.qrCode;
    document.getElementById('ticket-cust-name').innerText = reservation.customerName;
    document.getElementById('ticket-type').innerText = reservation.customerType;
    document.getElementById('ticket-table').innerText = `${reservation.tableName} (${reservation.timeSlot})`;
    document.getElementById('ticket-date').innerText = `${reservation.date}`;
    document.getElementById('ticket-total').innerText = `₱${reservation.totalAmount.toFixed(2)}`;

    // Render QR Code inside #qr-target
    const qrContainer = document.getElementById('qr-canvas-container');
    qrContainer.innerHTML = '';
    
    // Check if QRCode library exists
    if (window.QRCode) {
        new QRCode(qrContainer, {
            text: reservation.qrCode,
            width: 180,
            height: 180,
            colorDark: "#001861",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    } else {
        qrContainer.innerHTML = `<div class="p-4 bg-slate-100 rounded text-center text-xs font-mono break-all">${reservation.qrCode}</div>`;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeQRTicketModal() {
    const modal = document.getElementById('qr-pass-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}
window.closeQRTicketModal = closeQRTicketModal;
