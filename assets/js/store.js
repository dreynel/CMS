/**
 * ISAT U Coffee Shop - Shared State & Storage Service
 * Synchronizes POS, Reservations, QR Scanner, Kitchen KDS, and Admin Analytics
 */

const STORAGE_KEYS = {
    PRODUCTS: 'isatu_cafe_products',
    CATEGORIES: 'isatu_cafe_categories',
    TABLES: 'isatu_cafe_tables',
    RESERVATIONS: 'isatu_cafe_reservations',
    ORDERS: 'isatu_cafe_orders',
    SETTINGS: 'isatu_cafe_settings'
};

class Store {
    constructor() {
        this.channel = null;
        try {
            this.channel = new BroadcastChannel('isatu_cafe_sync');
            this.channel.onmessage = (event) => {
                this.notifySubscribers(event.data.type, event.data.payload);
            };
        } catch (e) {
            console.warn('BroadcastChannel not supported in this browser:', e);
        }

        this.subscribers = [];
        this.init();
    }

    init() {
        if (!localStorage.getItem(STORAGE_KEYS.PRODUCTS)) {
            localStorage.setItem(STORAGE_KEYS.PRODUCTS, JSON.stringify(INITIAL_PRODUCTS));
        }
        if (!localStorage.getItem(STORAGE_KEYS.CATEGORIES)) {
            localStorage.setItem(STORAGE_KEYS.CATEGORIES, JSON.stringify(INITIAL_CATEGORIES));
        }
        if (!localStorage.getItem(STORAGE_KEYS.TABLES)) {
            localStorage.setItem(STORAGE_KEYS.TABLES, JSON.stringify(INITIAL_TABLES));
        }
        if (!localStorage.getItem(STORAGE_KEYS.RESERVATIONS)) {
            localStorage.setItem(STORAGE_KEYS.RESERVATIONS, JSON.stringify(INITIAL_RESERVATIONS));
        }
        if (!localStorage.getItem(STORAGE_KEYS.ORDERS)) {
            localStorage.setItem(STORAGE_KEYS.ORDERS, JSON.stringify(INITIAL_ORDERS));
        }
        if (!localStorage.getItem(STORAGE_KEYS.SETTINGS)) {
            localStorage.setItem(STORAGE_KEYS.SETTINGS, JSON.stringify({
                shopName: 'ISAT U TechBrew Café',
                campus: 'Iloilo City Main Campus - Burgos St., La Paz',
                taxRate: 0.12, // 12% VAT
                studentDiscountRate: 0.10, // 10%
                facultyDiscountRate: 0.10,
                pwdSeniorDiscountRate: 0.20, // 20%
                currency: '₱'
            }));
        }
    }

    subscribe(callback) {
        this.subscribers.push(callback);
        return () => {
            this.subscribers = this.subscribers.filter(cb => cb !== callback);
        };
    }

    notifySubscribers(type, payload) {
        this.subscribers.forEach(cb => {
            try {
                cb(type, payload);
            } catch (err) {
                console.error('Subscriber callback error:', err);
            }
        });
    }

    broadcast(type, payload) {
        this.notifySubscribers(type, payload);
        if (this.channel) {
            this.channel.postMessage({ type, payload });
        }
    }

    // ==================== PRODUCTS ====================
    getProducts() {
        return JSON.parse(localStorage.getItem(STORAGE_KEYS.PRODUCTS) || '[]');
    }

    getProductById(id) {
        return this.getProducts().find(p => p.id === id);
    }

    updateProductStock(id, newStock) {
        const products = this.getProducts();
        const idx = products.findIndex(p => p.id === id);
        if (idx !== -1) {
            products[idx].stock = Math.max(0, parseInt(newStock));
            localStorage.setItem(STORAGE_KEYS.PRODUCTS, JSON.stringify(products));
            this.broadcast('PRODUCT_UPDATED', products[idx]);
        }
    }

    saveProduct(product) {
        const products = this.getProducts();
        const idx = products.findIndex(p => p.id === product.id);
        if (idx !== -1) {
            products[idx] = { ...products[idx], ...product };
        } else {
            product.id = 'PROD-' + String(products.length + 1).padStart(3, '0');
            products.push(product);
        }
        localStorage.setItem(STORAGE_KEYS.PRODUCTS, JSON.stringify(products));
        this.broadcast('PRODUCT_UPDATED', product);
        return product;
    }

    getCategories() {
        return JSON.parse(localStorage.getItem(STORAGE_KEYS.CATEGORIES) || '[]');
    }

    // ==================== TABLES ====================
    getTables() {
        return JSON.parse(localStorage.getItem(STORAGE_KEYS.TABLES) || '[]');
    }

    getTableById(id) {
        return this.getTables().find(t => t.id === id);
    }

    updateTableStatus(id, status) {
        const tables = this.getTables();
        const idx = tables.findIndex(t => t.id === id);
        if (idx !== -1) {
            tables[idx].status = status;
            localStorage.setItem(STORAGE_KEYS.TABLES, JSON.stringify(tables));
            this.broadcast('TABLE_UPDATED', tables[idx]);
        }
    }

    // ==================== RESERVATIONS ====================
    getReservations() {
        return JSON.parse(localStorage.getItem(STORAGE_KEYS.RESERVATIONS) || '[]');
    }

    getReservationById(id) {
        return this.getReservations().find(r => r.id === id);
    }

    getReservationByQR(qrCode) {
        const clean = (qrCode || '').trim();
        return this.getReservations().find(r => 
            r.qrCode === clean || 
            r.id === clean || 
            (r.qrCode && r.qrCode.toLowerCase() === clean.toLowerCase())
        );
    }

    createReservation(reservationData) {
        const reservations = this.getReservations();
        const now = new Date();
        const dateCode = now.toISOString().slice(0, 10).replace(/-/g, '');
        const randHex = Math.random().toString(16).substring(2, 6).toUpperCase();
        
        const newId = `RES-${dateCode}-${String(reservations.length + 1).padStart(3, '0')}`;
        const newQr = `ISATU-${newId}-${randHex}`;

        const newReservation = {
            id: newId,
            qrCode: newQr,
            status: 'Confirmed',
            createdAt: new Date().toLocaleString(),
            ...reservationData
        };

        reservations.unshift(newReservation);
        localStorage.setItem(STORAGE_KEYS.RESERVATIONS, JSON.stringify(reservations));
        
        // Mark table as reserved
        if (reservationData.tableId) {
            this.updateTableStatus(reservationData.tableId, 'reserved');
        }

        this.broadcast('RESERVATION_CREATED', newReservation);
        return newReservation;
    }

    updateReservationStatus(id, status) {
        const reservations = this.getReservations();
        const idx = reservations.findIndex(r => r.id === id);
        if (idx !== -1) {
            reservations[idx].status = status;
            localStorage.setItem(STORAGE_KEYS.RESERVATIONS, JSON.stringify(reservations));
            
            // Release table if completed or cancelled
            if ((status === 'Completed' || status === 'Cancelled') && reservations[idx].tableId) {
                this.updateTableStatus(reservations[idx].tableId, 'available');
            } else if (status === 'Checked-In' && reservations[idx].tableId) {
                this.updateTableStatus(reservations[idx].tableId, 'occupied');
            }

            this.broadcast('RESERVATION_UPDATED', reservations[idx]);
            return reservations[idx];
        }
        return null;
    }

    // ==================== ORDERS ====================
    getOrders() {
        return JSON.parse(localStorage.getItem(STORAGE_KEYS.ORDERS) || '[]');
    }

    getOrderById(id) {
        return this.getOrders().find(o => o.id === id);
    }

    createOrder(orderData) {
        const orders = this.getOrders();
        const newId = `ORD-${1000 + orders.length + 1}`;
        const now = new Date();
        
        const newOrder = {
            id: newId,
            status: 'Pending',
            time: now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            timestamp: Date.now(),
            ...orderData
        };

        orders.unshift(newOrder);
        localStorage.setItem(STORAGE_KEYS.ORDERS, JSON.stringify(orders));

        // Deduct inventory
        if (newOrder.items && Array.isArray(newOrder.items)) {
            newOrder.items.forEach(item => {
                const prod = this.getProductById(item.id || item.productId);
                if (prod) {
                    this.updateProductStock(prod.id, prod.stock - (item.qty || 1));
                }
            });
        }

        this.broadcast('ORDER_CREATED', newOrder);
        return newOrder;
    }

    updateOrderStatus(id, status) {
        const orders = this.getOrders();
        const idx = orders.findIndex(o => o.id === id);
        if (idx !== -1) {
            orders[idx].status = status;
            localStorage.setItem(STORAGE_KEYS.ORDERS, JSON.stringify(orders));
            this.broadcast('ORDER_STATUS_CHANGED', orders[idx]);
            return orders[idx];
        }
        return null;
    }

    // ==================== SETTINGS & ANALYTICS ====================
    getSettings() {
        return JSON.parse(localStorage.getItem(STORAGE_KEYS.SETTINGS) || '{}');
    }

    getAnalytics() {
        const orders = this.getOrders();
        const reservations = this.getReservations();
        const products = this.getProducts();

        const totalRevenue = orders.reduce((sum, o) => sum + (parseFloat(o.netTotal) || 0), 0);
        const totalOrders = orders.length;
        const pendingOrders = orders.filter(o => o.status === 'Pending' || o.status === 'Brewing').length;
        const completedOrders = orders.filter(o => o.status === 'Completed' || o.status === 'Ready').length;
        const activeReservations = reservations.filter(r => r.status === 'Confirmed' || r.status === 'Checked-In').length;
        const lowStockCount = products.filter(p => p.stock <= 15).length;

        return {
            totalRevenue,
            totalOrders,
            pendingOrders,
            completedOrders,
            activeReservations,
            lowStockCount
        };
    }

    resetAllData() {
        localStorage.setItem(STORAGE_KEYS.PRODUCTS, JSON.stringify(INITIAL_PRODUCTS));
        localStorage.setItem(STORAGE_KEYS.CATEGORIES, JSON.stringify(INITIAL_CATEGORIES));
        localStorage.setItem(STORAGE_KEYS.TABLES, JSON.stringify(INITIAL_TABLES));
        localStorage.setItem(STORAGE_KEYS.RESERVATIONS, JSON.stringify(INITIAL_RESERVATIONS));
        localStorage.setItem(STORAGE_KEYS.ORDERS, JSON.stringify(INITIAL_ORDERS));
        this.broadcast('DATA_RESET', null);
    }
}

// Global Store Instance
window.cafeStore = new Store();
