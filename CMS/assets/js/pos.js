/**
 * ISAT U Coffee Shop - Cashier POS Terminal Controller
 */

let activeCategory = 'all';
let searchQuery = '';
let currentCart = [];
let activeDiscountType = 'none'; // 'none', 'student', 'pwd'
let activeCustomizingProduct = null;
let currentCustomization = {
    size: null,
    sizePrice: 0,
    sugar: '50%',
    ice: 'Normal Ice',
    addons: [],
    addonsPrice: 0,
    notes: ''
};

document.addEventListener('DOMContentLoaded', () => {
    initPOSCategories();
    renderPOSProducts();
    renderCart();
    initPOSListeners();

    // Subscribe to state updates
    window.cafeStore.subscribe((type) => {
        if (type === 'PRODUCT_UPDATED' || type === 'DATA_RESET') {
            renderPOSProducts();
        }
    });
});

function initPOSListeners() {
    // Search input
    const searchInput = document.getElementById('pos-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value.toLowerCase().trim();
            renderPOSProducts();
        });
    }

    // Discount radio buttons
    const discountBtns = document.querySelectorAll('.discount-pill');
    discountBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            discountBtns.forEach(b => {
                b.classList.remove('bg-blue-900', 'text-amber-400', 'active', 'border-blue-900');
                b.classList.add('bg-white', 'text-slate-600', 'border-slate-200');
            });
            btn.classList.add('bg-blue-900', 'text-amber-400', 'active', 'border-blue-900');
            btn.classList.remove('bg-white', 'text-slate-600', 'border-slate-200');

            activeDiscountType = btn.dataset.discount || 'none';
            renderCart();
        });
    });

    // Clear cart button
    const clearCartBtn = document.getElementById('btn-clear-cart');
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', () => {
            if (currentCart.length > 0 && confirm('Are you sure you want to clear the current order tray?')) {
                currentCart = [];
                renderCart();
            }
        });
    }

    // Checkout button
    const checkoutBtn = document.getElementById('btn-checkout');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', openPaymentModal);
    }
}

// ==================== CATEGORIES & PRODUCTS ====================
function initPOSCategories() {
    const container = document.getElementById('pos-categories-nav');
    if (!container) return;

    const categories = window.cafeStore.getCategories();
    container.innerHTML = '';

    categories.forEach(cat => {
        const btn = document.createElement('button');
        const isActive = activeCategory === cat.id;
        btn.className = `category-tab px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all flex items-center gap-2 border ${
            isActive 
                ? 'bg-blue-900 text-amber-400 border-blue-950 shadow-md ring-2 ring-amber-400/30' 
                : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'
        }`;
        btn.innerHTML = `<span>${cat.name}</span>`;
        btn.addEventListener('click', () => {
            activeCategory = cat.id;
            initPOSCategories();
            renderPOSProducts();
        });
        container.appendChild(btn);
    });
}

function renderPOSProducts() {
    const grid = document.getElementById('pos-product-grid');
    if (!grid) return;

    const products = window.cafeStore.getProducts();
    const filtered = products.filter(p => {
        const matchesCat = activeCategory === 'all' || p.category === activeCategory;
        const matchesSearch = !searchQuery || p.name.toLowerCase().includes(searchQuery) || p.description.toLowerCase().includes(searchQuery);
        return matchesCat && matchesSearch;
    });

    grid.innerHTML = '';

    if (filtered.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full py-16 text-center text-slate-400">
                <i data-lucide="coffee" class="w-12 h-12 mx-auto mb-2 text-slate-300"></i>
                <p class="font-medium">No items found matching criteria.</p>
            </div>
        `;
        if (window.lucide) window.lucide.createIcons();
        return;
    }

    filtered.forEach(product => {
        const isOutOfStock = product.stock <= 0;
        const card = document.createElement('div');
        card.className = `pos-item-card bg-white rounded-2xl border border-slate-200/80 p-3 flex flex-col justify-between cursor-pointer relative overflow-hidden group shadow-sm ${
            isOutOfStock ? 'opacity-50 pointer-events-none' : ''
        }`;

        card.innerHTML = `
            <div>
                <div class="relative h-32 w-full rounded-xl overflow-hidden mb-2.5 bg-slate-100">
                    <img src="${product.image}" alt="${product.name}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                    ${product.badge ? `<span class="absolute top-2 left-2 bg-blue-900/90 text-amber-400 text-[10px] font-extrabold px-2 py-0.5 rounded-md backdrop-blur-sm border border-amber-400/30">${product.badge}</span>` : ''}
                    <span class="absolute bottom-2 right-2 bg-slate-900/80 text-white text-[11px] font-bold px-2 py-0.5 rounded backdrop-blur-sm">
                        Stock: ${product.stock}
                    </span>
                </div>
                <h4 class="font-bold text-slate-800 text-sm leading-snug line-clamp-1">${product.name}</h4>
                <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">${product.description}</p>
            </div>

            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-400">Starts at</span>
                    <p class="text-base font-extrabold text-blue-900 font-heading">₱${product.price.toFixed(2)}</p>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl bg-amber-400 hover:bg-amber-500 text-blue-950 font-bold flex items-center justify-center shadow-sm transition-transform active:scale-95">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </button>
            </div>
        `;

        card.addEventListener('click', () => {
            openCustomizationModal(product);
        });

        grid.appendChild(card);
    });

    if (window.lucide) {
        window.lucide.createIcons();
    }
}

// ==================== CUSTOMIZATION MODAL ====================
function openCustomizationModal(product) {
    activeCustomizingProduct = product;
    
    // Set defaults
    const defaultSize = (product.sizes && product.sizes.length > 0) ? product.sizes[0] : { name: 'Standard', priceAdd: 0 };
    currentCustomization = {
        size: defaultSize.name,
        sizePrice: defaultSize.priceAdd,
        sugar: (product.sugarLevels && product.sugarLevels.length > 0) ? '50%' : 'N/A',
        ice: (product.iceLevels && product.iceLevels.length > 0) ? product.iceLevels[0] : 'N/A',
        addons: [],
        addonsPrice: 0,
        notes: ''
    };

    const modal = document.getElementById('pos-customize-modal');
    if (!modal) {
        // Fallback: direct add
        addItemToCartDirect(product, defaultSize.name, defaultSize.priceAdd);
        return;
    }

    document.getElementById('modal-prod-img').src = product.image;
    document.getElementById('modal-prod-name').innerText = product.name;
    document.getElementById('modal-prod-desc').innerText = product.description;
    
    // Render Sizes
    const sizesContainer = document.getElementById('modal-sizes-options');
    sizesContainer.innerHTML = '';
    if (product.sizes && product.sizes.length > 0) {
        document.getElementById('modal-sizes-section').classList.remove('hidden');
        product.sizes.forEach((s, idx) => {
            const btn = document.createElement('button');
            const isSelected = idx === 0;
            btn.type = 'button';
            btn.className = `size-option-btn p-2.5 rounded-xl border text-xs font-bold text-center flex-1 transition-all ${
                isSelected ? 'border-blue-900 bg-blue-50 text-blue-900 ring-2 ring-blue-900/20' : 'border-slate-200 text-slate-700 bg-white hover:bg-slate-50'
            }`;
            btn.innerHTML = `<div>${s.name}</div><div class="text-[11px] font-normal text-slate-500 mt-0.5">${s.priceAdd > 0 ? '+₱' + s.priceAdd.toFixed(2) : 'Regular'}</div>`;
            btn.addEventListener('click', () => {
                document.querySelectorAll('.size-option-btn').forEach(b => {
                    b.className = 'size-option-btn p-2.5 rounded-xl border text-xs font-bold text-center flex-1 transition-all border-slate-200 text-slate-700 bg-white hover:bg-slate-50';
                });
                btn.className = 'size-option-btn p-2.5 rounded-xl border text-xs font-bold text-center flex-1 transition-all border-blue-900 bg-blue-50 text-blue-900 ring-2 ring-blue-900/20';
                currentCustomization.size = s.name;
                currentCustomization.sizePrice = s.priceAdd;
                updateModalTotalPrice();
            });
            sizesContainer.appendChild(btn);
        });
    } else {
        document.getElementById('modal-sizes-section').classList.add('hidden');
    }

    // Render Sugar Levels
    const sugarContainer = document.getElementById('modal-sugar-options');
    sugarContainer.innerHTML = '';
    if (product.sugarLevels && product.sugarLevels.length > 0) {
        document.getElementById('modal-sugar-section').classList.remove('hidden');
        product.sugarLevels.forEach((sugar) => {
            const btn = document.createElement('button');
            const isSelected = sugar === '50%';
            btn.type = 'button';
            btn.className = `sugar-option-btn py-2 px-3 rounded-lg border text-xs font-semibold flex-1 text-center transition-all ${
                isSelected ? 'border-blue-900 bg-blue-900 text-amber-400' : 'border-slate-200 text-slate-700 bg-white hover:bg-slate-50'
            }`;
            btn.innerText = sugar;
            btn.addEventListener('click', () => {
                document.querySelectorAll('.sugar-option-btn').forEach(b => {
                    b.className = 'sugar-option-btn py-2 px-3 rounded-lg border text-xs font-semibold flex-1 text-center transition-all border-slate-200 text-slate-700 bg-white hover:bg-slate-50';
                });
                btn.className = 'sugar-option-btn py-2 px-3 rounded-lg border text-xs font-semibold flex-1 text-center transition-all border-blue-900 bg-blue-900 text-amber-400';
                currentCustomization.sugar = sugar;
            });
            sugarContainer.appendChild(btn);
        });
    } else {
        document.getElementById('modal-sugar-section').classList.add('hidden');
    }

    // Render Ice Levels
    const iceContainer = document.getElementById('modal-ice-options');
    iceContainer.innerHTML = '';
    if (product.iceLevels && product.iceLevels.length > 0) {
        document.getElementById('modal-ice-section').classList.remove('hidden');
        product.iceLevels.forEach((ice, idx) => {
            const btn = document.createElement('button');
            const isSelected = idx === 0 || ice === 'Normal Ice';
            btn.type = 'button';
            btn.className = `ice-option-btn py-2 px-3 rounded-lg border text-xs font-semibold flex-1 text-center transition-all ${
                isSelected ? 'border-blue-900 bg-blue-900 text-amber-400' : 'border-slate-200 text-slate-700 bg-white hover:bg-slate-50'
            }`;
            btn.innerText = ice;
            btn.addEventListener('click', () => {
                document.querySelectorAll('.ice-option-btn').forEach(b => {
                    b.className = 'ice-option-btn py-2 px-3 rounded-lg border text-xs font-semibold flex-1 text-center transition-all border-slate-200 text-slate-700 bg-white hover:bg-slate-50';
                });
                btn.className = 'ice-option-btn py-2 px-3 rounded-lg border text-xs font-semibold flex-1 text-center transition-all border-blue-900 bg-blue-900 text-amber-400';
                currentCustomization.ice = ice;
            });
            iceContainer.appendChild(btn);
        });
    } else {
        document.getElementById('modal-ice-section').classList.add('hidden');
    }

    // Render Addons
    const addonsContainer = document.getElementById('modal-addons-options');
    addonsContainer.innerHTML = '';
    if (product.addons && product.addons.length > 0) {
        document.getElementById('modal-addons-section').classList.remove('hidden');
        product.addons.forEach(addon => {
            const label = document.createElement('label');
            label.className = 'flex items-center justify-between p-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-white text-xs cursor-pointer select-none transition-all';
            label.innerHTML = `
                <div class="flex items-center gap-2">
                    <input type="checkbox" value="${addon.name}" data-price="${addon.price}" class="addon-checkbox rounded text-blue-900 focus:ring-blue-900">
                    <span class="font-medium text-slate-800">${addon.name}</span>
                </div>
                <span class="font-bold text-blue-900">+₱${addon.price.toFixed(2)}</span>
            `;

            label.querySelector('input').addEventListener('change', (e) => {
                const checked = e.target.checked;
                if (checked) {
                    currentCustomization.addons.push(addon.name);
                    currentCustomization.addonsPrice += addon.price;
                } else {
                    currentCustomization.addons = currentCustomization.addons.filter(a => a !== addon.name);
                    currentCustomization.addonsPrice -= addon.price;
                }
                updateModalTotalPrice();
            });

            addonsContainer.appendChild(label);
        });
    } else {
        document.getElementById('modal-addons-section').classList.add('hidden');
    }

    updateModalTotalPrice();
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function updateModalTotalPrice() {
    if (!activeCustomizingProduct) return;
    const basePrice = activeCustomizingProduct.price;
    const total = basePrice + currentCustomization.sizePrice + currentCustomization.addonsPrice;
    document.getElementById('modal-total-price-btn').innerText = `Add to Order Tray • ₱${total.toFixed(2)}`;
}

function confirmCustomizationAndAdd() {
    if (!activeCustomizingProduct) return;
    
    const unitPrice = activeCustomizingProduct.price + currentCustomization.sizePrice + currentCustomization.addonsPrice;
    const notes = document.getElementById('modal-item-notes')?.value.trim() || '';

    const cartItem = {
        cartItemId: 'ITEM-' + Date.now() + '-' + Math.random().toString(36).substring(2, 5),
        id: activeCustomizingProduct.id,
        productId: activeCustomizingProduct.id,
        name: activeCustomizingProduct.name,
        image: activeCustomizingProduct.image,
        basePrice: activeCustomizingProduct.price,
        unitPrice: unitPrice,
        size: currentCustomization.size || 'Regular',
        sugar: currentCustomization.sugar,
        ice: currentCustomization.ice,
        addons: [...currentCustomization.addons],
        notes: notes,
        qty: 1
    };

    currentCart.push(cartItem);
    closeCustomizationModal();
    renderCart();
}
window.confirmCustomizationAndAdd = confirmCustomizationAndAdd;

function closeCustomizationModal() {
    const modal = document.getElementById('pos-customize-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    activeCustomizingProduct = null;
}
window.closeCustomizationModal = closeCustomizationModal;

function addItemToCartDirect(product, sizeName = 'Standard', sizePrice = 0) {
    currentCart.push({
        cartItemId: 'ITEM-' + Date.now(),
        id: product.id,
        productId: product.id,
        name: product.name,
        image: product.image,
        basePrice: product.price,
        unitPrice: product.price + sizePrice,
        size: sizeName,
        sugar: '-',
        ice: '-',
        addons: [],
        notes: '',
        qty: 1
    });
    renderCart();
}

// ==================== CART / ORDER TRAY ====================
function renderCart() {
    const list = document.getElementById('pos-cart-items');
    const subtotalEl = document.getElementById('cart-subtotal');
    const discountEl = document.getElementById('cart-discount');
    const vatEl = document.getElementById('cart-vat');
    const netTotalEl = document.getElementById('cart-net-total');
    const countBadge = document.getElementById('cart-count-badge');
    const emptyNotice = document.getElementById('cart-empty-notice');

    if (!list) return;

    list.innerHTML = '';
    const totalItems = currentCart.reduce((sum, i) => sum + i.qty, 0);
    if (countBadge) countBadge.innerText = `${totalItems} items`;

    if (currentCart.length === 0) {
        if (emptyNotice) emptyNotice.classList.remove('hidden');
        if (subtotalEl) subtotalEl.innerText = '₱0.00';
        if (discountEl) discountEl.innerText = '-₱0.00';
        if (vatEl) vatEl.innerText = '₱0.00';
        if (netTotalEl) netTotalEl.innerText = '₱0.00';
        return;
    }

    if (emptyNotice) emptyNotice.classList.add('hidden');

    let subtotal = 0;

    currentCart.forEach((item, index) => {
        const itemLineTotal = item.unitPrice * item.qty;
        subtotal += itemLineTotal;

        const row = document.createElement('div');
        row.className = 'p-3 bg-white rounded-xl border border-slate-200/90 shadow-sm flex flex-col gap-2 relative';
        
        let addonString = item.addons.length > 0 ? `+ ${item.addons.join(', ')}` : '';
        let specsString = [item.size, item.sugar !== '-' ? `Sugar: ${item.sugar}` : '', item.ice !== '-' ? item.ice : '']
            .filter(Boolean)
            .join(' • ');

        row.innerHTML = `
            <div class="flex items-start justify-between">
                <div class="flex-1 pr-2">
                    <h5 class="text-xs font-bold text-slate-800">${item.name}</h5>
                    <p class="text-[11px] text-slate-500">${specsString}</p>
                    ${addonString ? `<p class="text-[10px] text-amber-700 font-medium">${addonString}</p>` : ''}
                    ${item.notes ? `<p class="text-[10px] italic text-blue-900 bg-blue-50 px-1.5 py-0.5 rounded mt-1 inline-block">"${item.notes}"</p>` : ''}
                </div>
                <div class="text-right">
                    <span class="text-xs font-extrabold text-blue-900">₱${itemLineTotal.toFixed(2)}</span>
                    <div class="text-[10px] text-slate-400">₱${item.unitPrice.toFixed(2)} ea</div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                <button type="button" class="text-rose-500 hover:text-rose-700 text-xs flex items-center gap-1 font-medium remove-cart-item" data-index="${index}">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Remove
                </button>

                <div class="flex items-center gap-2 bg-slate-100 rounded-lg p-0.5">
                    <button type="button" class="w-6 h-6 rounded bg-white hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center shadow-xs decrease-qty" data-index="${index}">-</button>
                    <span class="text-xs font-bold px-2 text-slate-800">${item.qty}</span>
                    <button type="button" class="w-6 h-6 rounded bg-white hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center shadow-xs increase-qty" data-index="${index}">+</button>
                </div>
            </div>
        `;

        row.querySelector('.remove-cart-item').addEventListener('click', () => {
            currentCart.splice(index, 1);
            renderCart();
        });

        row.querySelector('.decrease-qty').addEventListener('click', () => {
            if (item.qty > 1) {
                item.qty -= 1;
            } else {
                currentCart.splice(index, 1);
            }
            renderCart();
        });

        row.querySelector('.increase-qty').addEventListener('click', () => {
            item.qty += 1;
            renderCart();
        });

        list.appendChild(row);
    });

    // Discount calculations
    let discountRate = 0;
    if (activeDiscountType === 'student' || activeDiscountType === 'faculty') {
        discountRate = 0.10; // 10%
    } else if (activeDiscountType === 'pwd') {
        discountRate = 0.20; // 20%
    }

    const discountAmount = subtotal * discountRate;
    const discountedTotal = subtotal - discountAmount;
    const vatAmount = discountedTotal * (0.12 / 1.12); // Included 12% VAT in PH
    const netTotal = discountedTotal;

    if (subtotalEl) subtotalEl.innerText = `₱${subtotal.toFixed(2)}`;
    if (discountEl) discountEl.innerText = `-₱${discountAmount.toFixed(2)}`;
    if (vatEl) vatEl.innerText = `₱${vatAmount.toFixed(2)}`;
    if (netTotalEl) netTotalEl.innerText = `₱${netTotal.toFixed(2)}`;

    if (window.lucide) {
        window.lucide.createIcons();
    }
}

// ==================== CHECKOUT & PAYMENT MODAL ====================
function openPaymentModal() {
    if (currentCart.length === 0) {
        alert('Cart is empty. Please add items to proceed with checkout.');
        return;
    }

    const subtotal = currentCart.reduce((sum, i) => sum + (i.unitPrice * i.qty), 0);
    const discountRate = activeDiscountType === 'student' ? 0.10 : (activeDiscountType === 'pwd' ? 0.20 : 0);
    const discountAmount = subtotal * discountRate;
    const netTotal = subtotal - discountAmount;

    const modal = document.getElementById('pos-payment-modal');
    if (!modal) return;

    document.getElementById('pay-modal-amount').innerText = `₱${netTotal.toFixed(2)}`;
    document.getElementById('pay-cash-tendered').value = netTotal.toFixed(2);
    calculateChange(netTotal, netTotal);

    // Fast cash buttons
    const fastBtns = document.querySelectorAll('.fast-cash-btn');
    fastBtns.forEach(btn => {
        btn.onclick = () => {
            const val = parseFloat(btn.dataset.amount);
            document.getElementById('pay-cash-tendered').value = val.toFixed(2);
            calculateChange(val, netTotal);
        };
    });

    const cashInput = document.getElementById('pay-cash-tendered');
    cashInput.oninput = () => {
        const tendered = parseFloat(cashInput.value) || 0;
        calculateChange(tendered, netTotal);
    };

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function calculateChange(tendered, total) {
    const change = tendered - total;
    const changeEl = document.getElementById('pay-cash-change');
    if (changeEl) {
        changeEl.innerText = change >= 0 ? `₱${change.toFixed(2)}` : 'Insufficient Tender';
        changeEl.className = change >= 0 ? 'text-lg font-black text-emerald-600' : 'text-lg font-black text-rose-600';
    }
}

function closePaymentModal() {
    const modal = document.getElementById('pos-payment-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}
window.closePaymentModal = closePaymentModal;

function completeOrder(paymentMethod = 'Cash') {
    const subtotal = currentCart.reduce((sum, i) => sum + (i.unitPrice * i.qty), 0);
    const discountRate = activeDiscountType === 'student' ? 0.10 : (activeDiscountType === 'pwd' ? 0.20 : 0);
    const discountAmount = subtotal * discountRate;
    const netTotal = subtotal - discountAmount;
    const vatAmount = netTotal * (0.12 / 1.12);

    const orderType = document.getElementById('pos-order-type')?.value || 'Dine-In';
    const tableNumber = document.getElementById('pos-table-num')?.value || 'Table 1';
    const customerName = document.getElementById('pos-customer-name')?.value.trim() || 'Walk-in Customer';
    const tendered = parseFloat(document.getElementById('pay-cash-tendered')?.value) || netTotal;
    const change = Math.max(0, tendered - netTotal);

    const orderPayload = {
        type: orderType,
        tableNumber: orderType === 'Dine-In' ? tableNumber : 'Pickup Counter',
        customerName: customerName,
        cashier: 'Cashier - Station 1',
        items: [...currentCart],
        subtotal: subtotal,
        discountType: activeDiscountType === 'student' ? 'ISAT U Student/Faculty (10%)' : (activeDiscountType === 'pwd' ? 'Senior/PWD (20%)' : 'None'),
        discountAmount: discountAmount,
        vatAmount: vatAmount,
        netTotal: netTotal,
        paymentMethod: paymentMethod,
        paymentRef: paymentMethod === 'Cash' ? `Tendered: ₱${tendered.toFixed(2)} | Change: ₱${change.toFixed(2)}` : `${paymentMethod} Ref: #${Math.floor(10000000 + Math.random() * 90000000)}`
    };

    const newOrder = window.cafeStore.createOrder(orderPayload);

    closePaymentModal();
    currentCart = [];
    renderCart();

    // Show Printable Receipt Modal
    showReceiptModal(newOrder);
}
window.completeOrder = completeOrder;

// ==================== RECEIPT MODAL & PRINT ====================
function showReceiptModal(order) {
    const modal = document.getElementById('receipt-modal');
    if (!modal) return;

    document.getElementById('rcpt-order-id').innerText = order.id;
    document.getElementById('rcpt-date-time').innerText = `${new Date().toLocaleDateString()} ${order.time}`;
    document.getElementById('rcpt-cashier').innerText = order.cashier;
    document.getElementById('rcpt-customer').innerText = order.customerName;
    document.getElementById('rcpt-type').innerText = `${order.type} • ${order.tableNumber}`;

    const itemsContainer = document.getElementById('rcpt-items-list');
    itemsContainer.innerHTML = '';
    order.items.forEach(item => {
        const itemLineTotal = item.unitPrice * item.qty;
        const row = document.createElement('div');
        row.className = 'flex justify-between text-xs py-1 border-b border-dashed border-slate-200';
        row.innerHTML = `
            <div class="pr-2">
                <span class="font-bold text-slate-800">${item.qty}x ${item.name}</span>
                <div class="text-[10px] text-slate-500">${item.size} ${item.sugar !== '-' ? '| Sugar: ' + item.sugar : ''}</div>
                ${item.addons && item.addons.length ? `<div class="text-[9px] text-slate-600">+ ${item.addons.join(', ')}</div>` : ''}
            </div>
            <span class="font-bold text-slate-900">₱${itemLineTotal.toFixed(2)}</span>
        `;
        itemsContainer.appendChild(row);
    });

    document.getElementById('rcpt-subtotal').innerText = `₱${order.subtotal.toFixed(2)}`;
    document.getElementById('rcpt-discount').innerText = `-₱${order.discountAmount.toFixed(2)}`;
    document.getElementById('rcpt-vat').innerText = `₱${order.vatAmount.toFixed(2)}`;
    document.getElementById('rcpt-net-total').innerText = `₱${order.netTotal.toFixed(2)}`;
    document.getElementById('rcpt-payment-method').innerText = order.paymentMethod;
    document.getElementById('rcpt-payment-ref').innerText = order.paymentRef;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeReceiptModal() {
    const modal = document.getElementById('receipt-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}
window.closeReceiptModal = closeReceiptModal;

function printReceipt() {
    window.print();
}
window.printReceipt = printReceipt;
