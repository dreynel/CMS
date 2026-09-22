/**
 * ISAT U Coffee Shop - Kitchen Display System (KDS) Controller
 */

let activeKitchenFilter = 'all';

document.addEventListener('DOMContentLoaded', () => {
    renderKitchenOrders();
    initKitchenControls();

    // Re-render elapsed time every 30 seconds
    setInterval(renderKitchenOrders, 30000);

    // State sync listener
    window.cafeStore.subscribe((type, payload) => {
        if (type === 'ORDER_CREATED' || type === 'ORDER_STATUS_CHANGED' || type === 'DATA_RESET') {
            renderKitchenOrders();
            if (type === 'ORDER_CREATED') {
                playKitchenAlertBeep();
            }
        }
    });
});

function initKitchenControls() {
    const filterBtns = document.querySelectorAll('.kds-filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => {
                b.classList.remove('bg-blue-900', 'text-amber-400', 'active');
                b.classList.add('bg-white', 'text-slate-600');
            });
            btn.classList.add('bg-blue-900', 'text-amber-400', 'active');
            btn.classList.remove('bg-white', 'text-slate-600');

            activeKitchenFilter = btn.dataset.filter || 'all';
            renderKitchenOrders();
        });
    });
}

function playKitchenAlertBeep() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, audioCtx.currentTime); // A5 note
        gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.5);
    } catch (e) {
        console.log('Audio alert suppressed by browser autoplay policy.');
    }
}

function renderKitchenOrders() {
    const orders = window.cafeStore.getOrders();

    const pendingCol = document.getElementById('kds-pending-col');
    const brewingCol = document.getElementById('kds-brewing-col');
    const readyCol = document.getElementById('kds-ready-col');
    const completedCol = document.getElementById('kds-completed-col');

    if (!pendingCol || !brewingCol || !readyCol) return;

    pendingCol.innerHTML = '';
    brewingCol.innerHTML = '';
    readyCol.innerHTML = '';
    if (completedCol) completedCol.innerHTML = '';

    const filtered = orders.filter(o => {
        if (activeKitchenFilter === 'all') return true;
        if (activeKitchenFilter === 'dinein') return o.type.toLowerCase().includes('dine');
        if (activeKitchenFilter === 'takeout') return o.type.toLowerCase().includes('takeout');
        if (activeKitchenFilter === 'reservation') return o.type.toLowerCase().includes('reservation');
        return true;
    });

    let counts = { pending: 0, brewing: 0, ready: 0, completed: 0 };

    filtered.forEach(order => {
        const card = createKDSOrderCard(order);

        if (order.status === 'Pending') {
            counts.pending++;
            pendingCol.appendChild(card);
        } else if (order.status === 'Brewing') {
            counts.brewing++;
            brewingCol.appendChild(card);
        } else if (order.status === 'Ready') {
            counts.ready++;
            readyCol.appendChild(card);
        } else if (order.status === 'Completed' && completedCol) {
            counts.completed++;
            completedCol.appendChild(card);
        }
    });

    // Update column counters
    updateKDSCounts(counts);

    // If empty states
    if (counts.pending === 0) pendingCol.innerHTML = '<div class="p-6 text-center text-xs text-slate-400 font-medium">No pending tickets</div>';
    if (counts.brewing === 0) brewingCol.innerHTML = '<div class="p-6 text-center text-xs text-slate-400 font-medium">No drinks brewing</div>';
    if (counts.ready === 0) readyCol.innerHTML = '<div class="p-6 text-center text-xs text-slate-400 font-medium">No orders ready for pickup</div>';

    if (window.lucide) window.lucide.createIcons();
}

function updateKDSCounts(counts) {
    const pBadge = document.getElementById('count-pending');
    const bBadge = document.getElementById('count-brewing');
    const rBadge = document.getElementById('count-ready');
    const cBadge = document.getElementById('count-completed');

    if (pBadge) pBadge.innerText = counts.pending;
    if (bBadge) bBadge.innerText = counts.brewing;
    if (rBadge) rBadge.innerText = counts.ready;
    if (cBadge) cBadge.innerText = counts.completed;
}

function createKDSOrderCard(order) {
    const card = document.createElement('div');
    const now = Date.now();
    const elapsedMinutes = Math.floor((now - (order.timestamp || now)) / 60000);
    const isUrgent = elapsedMinutes >= 10 && order.status !== 'Completed';

    card.className = `p-4 rounded-2xl bg-white border ${
        isUrgent ? 'border-rose-400 ring-2 ring-rose-400/20 shadow-md' : 'border-slate-200 shadow-sm'
    } flex flex-col justify-between mb-3 transition-all hover:shadow-md`;

    let itemsHtml = '';
    order.items.forEach(item => {
        const specs = [item.size, item.sugar !== '-' ? 'Sugar: ' + item.sugar : '', item.ice !== '-' ? item.ice : '']
            .filter(Boolean)
            .join(' • ');

        itemsHtml += `
            <div class="py-2 border-b border-slate-100 last:border-b-0">
                <div class="flex items-start justify-between">
                    <span class="font-extrabold text-sm text-slate-800">${item.qty}x ${item.name}</span>
                </div>
                <p class="text-xs text-slate-600 font-medium">${specs}</p>
                ${item.addons && item.addons.length ? `<p class="text-[11px] text-amber-700 font-semibold mt-0.5">+ ${item.addons.join(', ')}</p>` : ''}
                ${item.notes ? `<p class="text-[11px] italic text-rose-600 bg-rose-50 px-2 py-0.5 rounded mt-1 font-semibold">"${item.notes}"</p>` : ''}
            </div>
        `;
    });

    let actionBtnHtml = '';
    if (order.status === 'Pending') {
        actionBtnHtml = `
            <button class="w-full py-2.5 bg-blue-900 hover:bg-blue-950 text-amber-400 font-bold rounded-xl text-xs shadow transition-all flex items-center justify-center gap-1" onclick="advanceOrderStatus('${order.id}', 'Brewing')">
                <i data-lucide="play" class="w-3.5 h-3.5"></i> Start Brewing
            </button>
        `;
    } else if (order.status === 'Brewing') {
        actionBtnHtml = `
            <button class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold rounded-xl text-xs shadow transition-all flex items-center justify-center gap-1" onclick="advanceOrderStatus('${order.id}', 'Ready')">
                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Mark as Ready
            </button>
        `;
    } else if (order.status === 'Ready') {
        actionBtnHtml = `
            <button class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow transition-all flex items-center justify-center gap-1" onclick="advanceOrderStatus('${order.id}', 'Completed')">
                <i data-lucide="check-check" class="w-3.5 h-3.5"></i> Complete / Served
            </button>
        `;
    } else {
        actionBtnHtml = `<span class="text-xs text-slate-400 font-bold text-center block py-1">Order Fulfilled</span>`;
    }

    card.innerHTML = `
        <div>
            <div class="flex items-start justify-between pb-2 border-b border-slate-100">
                <div>
                    <span class="text-[10px] font-mono font-bold text-blue-900 bg-blue-50 px-2 py-0.5 rounded">${order.id}</span>
                    <h5 class="text-sm font-black text-slate-900 font-heading mt-1">${order.customerName}</h5>
                    <span class="text-xs font-bold text-amber-600">${order.type} • ${order.tableNumber}</span>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold text-slate-700">${order.time}</span>
                    <div class="text-[11px] ${isUrgent ? 'text-rose-600 font-bold animate-pulse' : 'text-slate-400'} mt-0.5">
                        ${elapsedMinutes}m ago
                    </div>
                </div>
            </div>

            <div class="my-2 max-h-52 overflow-y-auto pr-1">
                ${itemsHtml}
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100">
            ${actionBtnHtml}
        </div>
    `;

    return card;
}

function advanceOrderStatus(orderId, nextStatus) {
    window.cafeStore.updateOrderStatus(orderId, nextStatus);
    renderKitchenOrders();
}
window.advanceOrderStatus = advanceOrderStatus;
