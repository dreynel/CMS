/**
 * ISAT U Coffee Shop - QR Code Scanner & Reservation Validator
 */

let html5QrCodeScanner = null;
let currentScannedReservation = null;

document.addEventListener('DOMContentLoaded', () => {
    initQRScanner();
    initManualSearch();
    loadRecentScans();

    // State listener
    window.cafeStore.subscribe((type) => {
        if (type === 'RESERVATION_CREATED' || type === 'RESERVATION_UPDATED' || type === 'DATA_RESET') {
            loadRecentScans();
        }
    });
});

function initQRScanner() {
    const scannerElement = document.getElementById('qr-reader');
    if (!scannerElement) return;

    if (window.Html5Qrcode) {
        html5QrCodeScanner = new Html5Qrcode("qr-reader");
        
        // Start scanner button
        const startBtn = document.getElementById('btn-start-camera');
        const stopBtn = document.getElementById('btn-stop-camera');

        if (startBtn && stopBtn) {
            startBtn.addEventListener('click', () => {
                Html5Qrcode.getCameras().then(devices => {
                    if (devices && devices.length) {
                        const cameraId = devices[0].id;
                        html5QrCodeScanner.start(
                            cameraId,
                            {
                                fps: 10,
                                qrbox: { width: 250, height: 250 }
                            },
                            (decodedText) => {
                                handleQRCodeDetected(decodedText);
                            },
                            () => {}
                        ).then(() => {
                            startBtn.classList.add('hidden');
                            stopBtn.classList.remove('hidden');
                        }).catch(err => {
                            alert('Unable to access camera: ' + err);
                        });
                    }
                }).catch(() => {
                    alert('No camera found on this device. You can use manual code input or file upload below.');
                });
            });

            stopBtn.addEventListener('click', () => {
                if (html5QrCodeScanner) {
                    html5QrCodeScanner.stop().then(() => {
                        startBtn.classList.remove('hidden');
                        stopBtn.classList.add('hidden');
                    });
                }
            });
        }
    }

    // File input QR scan
    const fileInput = document.getElementById('qr-input-file');
    if (fileInput && window.Html5Qrcode) {
        fileInput.addEventListener('change', e => {
            if (e.target.files.length === 0) return;
            const imageFile = e.target.files[0];
            const html5QrCode = new Html5Qrcode("qr-reader-file-dummy");
            html5QrCode.scanFile(imageFile, true)
                .then(decodedText => {
                    handleQRCodeDetected(decodedText);
                })
                .catch(err => {
                    alert("Could not find a valid QR code in this image.");
                });
        });
    }
}

function initManualSearch() {
    const manualInput = document.getElementById('manual-qr-input');
    const manualBtn = document.getElementById('btn-manual-verify');

    if (manualBtn && manualInput) {
        manualBtn.addEventListener('click', () => {
            const val = manualInput.value.trim();
            if (val) {
                handleQRCodeDetected(val);
            } else {
                alert('Please enter a Reservation ID or QR string.');
            }
        });

        manualInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                manualBtn.click();
            }
        });
    }

    // Quick demo buttons
    const demoBtns = document.querySelectorAll('.demo-scan-btn');
    demoBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const code = btn.dataset.code;
            if (code) {
                if (manualInput) manualInput.value = code;
                handleQRCodeDetected(code);
            }
        });
    });
}

function handleQRCodeDetected(qrString) {
    const reservation = window.cafeStore.getReservationByQR(qrString);
    const resultCard = document.getElementById('scanned-result-card');
    const emptyNotice = document.getElementById('scanner-empty-notice');

    if (!reservation) {
        if (resultCard) resultCard.classList.add('hidden');
        if (emptyNotice) {
            emptyNotice.classList.remove('hidden');
            emptyNotice.innerHTML = `
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="alert-circle" class="w-8 h-8"></i>
                    </div>
                    <h4 class="text-base font-bold text-slate-800">Invalid QR Code / Ticket Not Found</h4>
                    <p class="text-xs text-slate-500 mt-1">Decoded text: <code class="bg-slate-100 px-1 py-0.5 rounded text-rose-700 font-mono">${qrString}</code></p>
                    <p class="text-xs text-slate-500 mt-2">Please ensure the guest is presenting an authentic ISAT U Reservation Pass.</p>
                </div>
            `;
            if (window.lucide) window.lucide.createIcons();
        }
        return;
    }

    currentScannedReservation = reservation;
    if (emptyNotice) emptyNotice.classList.add('hidden');
    if (resultCard) {
        resultCard.classList.remove('hidden');
        renderScannedTicketCard(reservation);
    }
}

function renderScannedTicketCard(res) {
    document.getElementById('scan-res-id').innerText = res.id;
    document.getElementById('scan-qr-code').innerText = res.qrCode;
    document.getElementById('scan-customer-name').innerText = res.customerName;
    document.getElementById('scan-customer-type').innerText = res.customerType;
    document.getElementById('scan-id-number').innerText = res.idNumber || 'N/A';
    document.getElementById('scan-table-name').innerText = res.tableName;
    document.getElementById('scan-time-slot').innerText = `${res.date} • ${res.timeSlot}`;
    document.getElementById('scan-guest-count').innerText = `${res.guests} Pax`;
    document.getElementById('scan-payment-status').innerText = res.paymentStatus;
    
    // Status styling
    const statusBadge = document.getElementById('scan-status-badge');
    statusBadge.innerText = res.status;
    statusBadge.className = `px-3 py-1 text-xs font-extrabold rounded-full ${
        res.status === 'Confirmed' ? 'bg-amber-100 text-amber-800 border border-amber-300' :
        res.status === 'Checked-In' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' :
        'bg-slate-100 text-slate-700 border border-slate-300'
    }`;

    // Render Items
    const itemsList = document.getElementById('scan-items-list');
    itemsList.innerHTML = '';

    if (res.preOrderItems && res.preOrderItems.length > 0) {
        res.preOrderItems.forEach(item => {
            const itemRow = document.createElement('div');
            itemRow.className = 'flex items-center justify-between text-xs py-1.5 border-b border-slate-100';
            itemRow.innerHTML = `
                <div>
                    <span class="font-bold text-slate-800">${item.qty}x ${item.name}</span>
                    <p class="text-[10px] text-slate-500">${item.size || 'Standard'} ${item.sugar ? '| Sugar: ' + item.sugar : ''}</p>
                </div>
                <span class="font-bold text-blue-900">₱${((item.price || 0) * item.qty).toFixed(2)}</span>
            `;
            itemsList.appendChild(itemRow);
        });
        document.getElementById('scan-total-amount').innerText = `₱${res.totalAmount.toFixed(2)}`;
    } else {
        itemsList.innerHTML = '<p class="text-xs text-slate-400 italic">No pre-ordered food or drinks. (Seat reservation only)</p>';
        document.getElementById('scan-total-amount').innerText = '₱0.00';
    }

    // Action button state
    const checkinBtn = document.getElementById('btn-checkin-order');
    if (checkinBtn) {
        if (res.status === 'Checked-In') {
            checkinBtn.innerText = '✓ Already Checked-In (Send to Kitchen Again)';
            checkinBtn.className = 'w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow transition-all flex items-center justify-center gap-2 text-sm';
        } else if (res.status === 'Completed') {
            checkinBtn.innerText = 'Reservation Completed';
            checkinBtn.className = 'w-full py-3 bg-slate-400 text-white font-bold rounded-xl cursor-not-allowed text-sm';
        } else {
            checkinBtn.innerText = '✓ Check-In Guest & Push Order to Kitchen';
            checkinBtn.className = 'w-full py-3 bg-blue-900 hover:bg-blue-950 text-amber-400 font-bold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 text-sm';
        }
        checkinBtn.onclick = () => performCheckIn(res);
    }

    if (window.lucide) window.lucide.createIcons();
}

function performCheckIn(res) {
    // 1. Update reservation status
    window.cafeStore.updateReservationStatus(res.id, 'Checked-In');

    // 2. If pre-ordered items exist, create an order in the kitchen/POS queue
    if (res.preOrderItems && res.preOrderItems.length > 0) {
        const orderPayload = {
            type: 'Reservation Pre-order',
            tableNumber: res.tableName,
            customerName: `${res.customerName} (${res.customerType})`,
            cashier: 'QR Auto Check-In',
            items: res.preOrderItems.map(i => ({
                id: i.productId || i.id,
                productId: i.productId || i.id,
                name: i.name,
                unitPrice: i.price,
                size: i.size || 'Standard',
                sugar: i.sugar || '-',
                ice: i.ice || '-',
                addons: i.addons || [],
                qty: i.qty || 1
            })),
            subtotal: res.totalAmount,
            discountType: res.customerType.includes('Student') ? 'Student (10%)' : 'None',
            discountAmount: 0,
            vatAmount: res.totalAmount * (0.12 / 1.12),
            netTotal: res.totalAmount,
            paymentMethod: res.paymentStatus.includes('Paid') ? 'Pre-Paid Online' : 'Pay at Counter',
            paymentRef: `QR Pass: ${res.qrCode}`
        };

        window.cafeStore.createOrder(orderPayload);
    }

    alert(`Success! Guest ${res.customerName} has been checked in for ${res.tableName}. The pre-ordered drinks have been routed to the Barista Kitchen Screen.`);
    handleQRCodeDetected(res.qrCode);
    loadRecentScans();
}

function loadRecentScans() {
    const list = document.getElementById('recent-scans-list');
    if (!list) return;

    const reservations = window.cafeStore.getReservations().slice(0, 5);
    list.innerHTML = '';

    reservations.forEach(r => {
        const item = document.createElement('div');
        item.className = 'p-3 bg-white rounded-xl border border-slate-200/80 shadow-xs hover:border-amber-400 cursor-pointer transition-all flex items-center justify-between';
        item.innerHTML = `
            <div>
                <span class="text-[10px] font-mono text-slate-400">${r.id}</span>
                <h6 class="text-xs font-bold text-slate-800">${r.customerName}</h6>
                <p class="text-[11px] text-slate-500">${r.tableName} • ${r.timeSlot}</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full ${
                    r.status === 'Confirmed' ? 'bg-amber-100 text-amber-800' :
                    r.status === 'Checked-In' ? 'bg-emerald-100 text-emerald-800' :
                    'bg-slate-100 text-slate-600'
                }">${r.status}</span>
                <div class="text-[11px] font-bold text-blue-900 mt-1">₱${r.totalAmount.toFixed(2)}</div>
            </div>
        `;

        item.addEventListener('click', () => {
            handleQRCodeDetected(r.qrCode);
        });

        list.appendChild(item);
    });
}
