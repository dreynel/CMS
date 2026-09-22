/**
 * ISAT U Coffee Shop - Admin & Management Dashboard Controller
 */

let salesChart = null;
let categoryChart = null;

document.addEventListener('DOMContentLoaded', () => {
    initAdminKPIs();
    initAdminCharts();
    renderReservationsTable();
    renderInventoryTable();
    initAdminListeners();

    // State sync listener
    window.cafeStore.subscribe(() => {
        initAdminKPIs();
        renderReservationsTable();
        renderInventoryTable();
        updateChartsData();
    });
});

function initAdminListeners() {
    // Reservation table filter
    const resFilter = document.getElementById('admin-res-filter');
    if (resFilter) {
        resFilter.addEventListener('change', () => {
            renderReservationsTable();
        });
    }

    // Reset data button
    const resetBtn = document.getElementById('btn-reset-demo-data');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            if (confirm('Reset all demo data back to factory defaults?')) {
                window.cafeStore.resetAllData();
                alert('Demo database reset to initial values.');
            }
        });
    }
}

function initAdminKPIs() {
    const analytics = window.cafeStore.getAnalytics();

    const revEl = document.getElementById('kpi-revenue');
    const ordersEl = document.getElementById('kpi-orders');
    const resEl = document.getElementById('kpi-reservations');
    const stockEl = document.getElementById('kpi-low-stock');

    if (revEl) revEl.innerText = `₱${analytics.totalRevenue.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
    if (ordersEl) ordersEl.innerText = analytics.totalOrders;
    if (resEl) resEl.innerText = analytics.activeReservations;
    if (stockEl) stockEl.innerText = analytics.lowStockCount;
}

function initAdminCharts() {
    const salesCanvas = document.getElementById('admin-sales-chart');
    const catCanvas = document.getElementById('admin-cat-chart');

    if (salesCanvas && window.Chart) {
        salesChart = new Chart(salesCanvas, {
            type: 'line',
            data: {
                labels: ['7 AM', '8 AM', '9 AM', '10 AM', '11 AM', '12 PM', '1 PM', '2 PM', '3 PM', '4 PM', '5 PM'],
                datasets: [{
                    label: "Today's Sales (₱)",
                    data: [150, 420, 890, 1640, 2450, 3890, 4200, 5100, 5600, 6100, 6800],
                    borderColor: '#002080',
                    backgroundColor: 'rgba(0, 32, 128, 0.08)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#F59E0B',
                    pointBorderColor: '#002080',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            callback: value => '₱' + value
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    if (catCanvas && window.Chart) {
        catChart = new Chart(catCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Signatures', 'Espresso', 'Cold Brew', 'Non-Coffee', 'Pastries', 'Meals'],
                datasets: [{
                    data: [35, 25, 15, 12, 8, 5],
                    backgroundColor: ['#002080', '#F59E0B', '#3B82F6', '#10B981', '#EC4899', '#8B5CF6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                }
            }
        });
    }
}

function updateChartsData() {
    if (salesChart) salesChart.update();
    if (categoryChart) categoryChart.update();
}

function renderReservationsTable() {
    const tbody = document.getElementById('admin-reservations-tbody');
    const filter = document.getElementById('admin-res-filter')?.value || 'all';
    if (!tbody) return;

    const reservations = window.cafeStore.getReservations();
    const filtered = filter === 'all' 
        ? reservations 
        : reservations.filter(r => r.status.toLowerCase() === filter.toLowerCase());

    tbody.innerHTML = '';

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-slate-400 text-xs">No reservation records found.</td></tr>`;
        return;
    }

    filtered.forEach(res => {
        const tr = document.createElement('tr');
        tr.className = 'border-b border-slate-100 hover:bg-slate-50 text-xs text-slate-700';

        tr.innerHTML = `
            <td class="py-3 px-4 font-mono font-bold text-blue-900">${res.id}</td>
            <td class="py-3 px-4">
                <div class="font-bold text-slate-900">${res.customerName}</div>
                <div class="text-[11px] text-slate-500">${res.customerType} • ${res.phone}</div>
            </td>
            <td class="py-3 px-4">
                <div class="font-medium">${res.tableName}</div>
                <div class="text-[11px] text-slate-400">${res.timeSlot}</div>
            </td>
            <td class="py-3 px-4">
                <span class="font-bold text-slate-800">₱${res.totalAmount.toFixed(2)}</span>
                <div class="text-[10px] text-slate-500">${res.paymentStatus}</div>
            </td>
            <td class="py-3 px-4 font-mono text-[11px] text-slate-500">${res.qrCode}</td>
            <td class="py-3 px-4">
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold ${
                    res.status === 'Confirmed' ? 'bg-amber-100 text-amber-800' :
                    res.status === 'Checked-In' ? 'bg-emerald-100 text-emerald-800' :
                    'bg-slate-100 text-slate-700'
                }">${res.status}</span>
            </td>
            <td class="py-3 px-4 text-right">
                <div class="flex items-center justify-end gap-1">
                    ${res.status === 'Confirmed' ? `
                        <button class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-bold" onclick="adminCheckIn('${res.id}')">Check-In</button>
                    ` : ''}
                    ${res.status !== 'Cancelled' ? `
                        <button class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded text-[11px] font-bold" onclick="adminCancelRes('${res.id}')">Cancel</button>
                    ` : ''}
                </div>
            </td>
        `;

        tbody.appendChild(tr);
    });
}

function adminCheckIn(resId) {
    window.cafeStore.updateReservationStatus(resId, 'Checked-In');
    renderReservationsTable();
}
window.adminCheckIn = adminCheckIn;

function adminCancelRes(resId) {
    if (confirm('Cancel this table reservation?')) {
        window.cafeStore.updateReservationStatus(resId, 'Cancelled');
        renderReservationsTable();
    }
}
window.adminCancelRes = adminCancelRes;

function renderInventoryTable() {
    const tbody = document.getElementById('admin-inventory-tbody');
    if (!tbody) return;

    const products = window.cafeStore.getProducts();
    tbody.innerHTML = '';

    products.forEach(p => {
        const isLow = p.stock <= 15;
        const tr = document.createElement('tr');
        tr.className = `border-b border-slate-100 hover:bg-slate-50 text-xs ${isLow ? 'bg-rose-50/40' : ''}`;

        tr.innerHTML = `
            <td class="py-3 px-4 font-mono font-bold text-slate-600">${p.id}</td>
            <td class="py-3 px-4">
                <div class="flex items-center gap-2.5">
                    <img src="${p.image}" class="w-8 h-8 rounded-lg object-cover" />
                    <div>
                        <div class="font-bold text-slate-900">${p.name}</div>
                        <div class="text-[10px] text-slate-400 capitalize">${p.category}</div>
                    </div>
                </div>
            </td>
            <td class="py-3 px-4 font-bold text-blue-900">₱${p.price.toFixed(2)}</td>
            <td class="py-3 px-4 text-slate-600">₱${(p.cost || 0).toFixed(2)}</td>
            <td class="py-3 px-4">
                <span class="font-extrabold ${isLow ? 'text-rose-600' : 'text-slate-800'}">${p.stock} units</span>
                ${isLow ? '<span class="ml-1 text-[10px] bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded font-bold">Low Stock</span>' : ''}
            </td>
            <td class="py-3 px-4 text-right">
                <div class="flex items-center justify-end gap-1">
                    <button class="w-6 h-6 rounded bg-slate-200 hover:bg-slate-300 font-bold flex items-center justify-center text-xs" onclick="adjustProductStock('${p.id}', -5)">-5</button>
                    <button class="w-6 h-6 rounded bg-slate-200 hover:bg-slate-300 font-bold flex items-center justify-center text-xs" onclick="adjustProductStock('${p.id}', -1)">-1</button>
                    <button class="w-6 h-6 rounded bg-blue-900 hover:bg-blue-950 text-amber-400 font-bold flex items-center justify-center text-xs" onclick="adjustProductStock('${p.id}', 1)">+1</button>
                    <button class="w-6 h-6 rounded bg-blue-900 hover:bg-blue-950 text-amber-400 font-bold flex items-center justify-center text-xs" onclick="adjustProductStock('${p.id}', 10)">+10</button>
                </div>
            </td>
        `;

        tbody.appendChild(tr);
    });
}

function adjustProductStock(productId, delta) {
    const prod = window.cafeStore.getProductById(productId);
    if (prod) {
        const newStock = Math.max(0, prod.stock + delta);
        window.cafeStore.updateProductStock(productId, newStock);
        renderInventoryTable();
    }
}
window.adjustProductStock = adjustProductStock;
