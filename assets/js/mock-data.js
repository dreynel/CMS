/**
 * ISAT U Coffee Shop - Default Mock Data
 * Initial categories, products, tables, and seed records
 */

const INITIAL_CATEGORIES = [
    { id: 'all', name: 'All Items', icon: 'coffee' },
    { id: 'signature', name: 'ISAT U Signatures', icon: 'sparkles' },
    { id: 'espresso', name: 'Espresso & Classics', icon: 'cup-soda' },
    { id: 'coldbrew', name: 'Cold Brew & Iced', icon: 'snowflake' },
    { id: 'noncoffee', name: 'Milk Teas & Non-Coffee', icon: 'heart' },
    { id: 'pastries', name: 'Pastries & Bakes', icon: 'cake' },
    { id: 'meals', name: 'Snacks & Light Meals', icon: 'utensils' }
];

const INITIAL_PRODUCTS = [
    {
        id: 'PROD-001',
        name: 'ISAT U Golden Tamaraw Latte',
        category: 'signature',
        price: 135.00,
        cost: 65.00,
        stock: 45,
        rating: 4.9,
        badge: 'Bestseller',
        image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?w=600&auto=format&fit=crop&q=80',
        description: 'Espresso layered with golden honey honeycomb, fresh milk, and subtle caramel drizzle. Inspired by the university pride.',
        hasCustomizations: true,
        sizes: [
            { name: 'Regular (12oz)', priceAdd: 0 },
            { name: 'Large (16oz)', priceAdd: 25 },
            { name: 'Jumbo (22oz)', priceAdd: 45 }
        ],
        sugarLevels: ['0%', '25%', '50%', '75%', '100%'],
        iceLevels: ['Hot', 'Less Ice', 'Normal Ice', 'Extra Ice'],
        addons: [
            { name: 'Extra Espresso Shot', price: 25 },
            { name: 'Oat Milk Sub', price: 35 },
            { name: 'Caramel Drizzle', price: 15 },
            { name: 'Coffee Jelly', price: 20 }
        ]
    },
    {
        id: 'PROD-002',
        name: 'Blue & Gold Velvet Frappe',
        category: 'signature',
        price: 150.00,
        cost: 70.00,
        stock: 38,
        rating: 4.8,
        badge: 'Signature',
        image: 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=600&auto=format&fit=crop&q=80',
        description: 'Rich vanilla butterfly pea flower blend with golden salted caramel crust and whipped cream.',
        hasCustomizations: true,
        sizes: [
            { name: 'Regular (16oz)', priceAdd: 0 },
            { name: 'Large (22oz)', priceAdd: 30 }
        ],
        sugarLevels: ['25%', '50%', '75%', '100%'],
        iceLevels: ['Blended Ice'],
        addons: [
            { name: 'Whipped Cream', price: 20 },
            { name: 'Cheesecake Foam', price: 30 },
            { name: 'Caramel Crunch', price: 20 }
        ]
    },
    {
        id: 'PROD-003',
        name: 'Classic Spanish Latte',
        category: 'espresso',
        price: 120.00,
        cost: 55.00,
        stock: 60,
        rating: 4.9,
        badge: 'Staff Pick',
        image: 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=600&auto=format&fit=crop&q=80',
        description: 'Double shot rich espresso with sweetened condensed milk and silky textured steamed milk.',
        hasCustomizations: true,
        sizes: [
            { name: 'Regular (12oz)', priceAdd: 0 },
            { name: 'Large (16oz)', priceAdd: 20 }
        ],
        sugarLevels: ['0%', '25%', '50%', '75%', '100%'],
        iceLevels: ['Hot', 'Less Ice', 'Normal Ice'],
        addons: [
            { name: 'Extra Espresso Shot', price: 25 },
            { name: 'Vanilla Syrup', price: 20 }
        ]
    },
    {
        id: 'PROD-004',
        name: 'Campustech Dark Mocha',
        category: 'espresso',
        price: 130.00,
        cost: 60.00,
        stock: 52,
        rating: 4.7,
        badge: '',
        image: 'https://images.unsplash.com/photo-1534778101976-62847782c213?w=600&auto=format&fit=crop&q=80',
        description: 'Davao dark chocolate combined with bold espresso roast and steamed whole milk.',
        hasCustomizations: true,
        sizes: [
            { name: 'Regular (12oz)', priceAdd: 0 },
            { name: 'Large (16oz)', priceAdd: 25 }
        ],
        sugarLevels: ['0%', '25%', '50%', '75%', '100%'],
        iceLevels: ['Hot', 'Less Ice', 'Normal Ice'],
        addons: [
            { name: 'Extra Espresso Shot', price: 25 },
            { name: 'Marshmallows', price: 15 }
        ]
    },
    {
        id: 'PROD-005',
        name: 'Nitro Sweet Cream Cold Brew',
        category: 'coldbrew',
        price: 140.00,
        cost: 50.00,
        stock: 25,
        rating: 5.0,
        badge: 'Premium',
        image: 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=600&auto=format&fit=crop&q=80',
        description: '18-hour slow-steeped Arabica blend topped with house-crafted vanilla sweet cream foam.',
        hasCustomizations: true,
        sizes: [
            { name: 'Large (16oz)', priceAdd: 0 },
            { name: 'Jumbo (22oz)', priceAdd: 30 }
        ],
        sugarLevels: ['0%', '25%', '50%', '75%'],
        iceLevels: ['Less Ice', 'Normal Ice'],
        addons: [
            { name: 'Sweet Cream Top-up', price: 30 },
            { name: 'Hazelnut Shot', price: 20 }
        ]
    },
    {
        id: 'PROD-006',
        name: 'Oat Milk Brown Sugar Boba',
        category: 'noncoffee',
        price: 125.00,
        cost: 50.00,
        stock: 40,
        rating: 4.8,
        badge: '',
        image: 'https://images.unsplash.com/photo-1558857563-b371033873b8?w=600&auto=format&fit=crop&q=80',
        description: 'Rich brown sugar boba pearls slow-cooked, paired with smooth creamy oat milk.',
        hasCustomizations: true,
        sizes: [
            { name: 'Regular (16oz)', priceAdd: 0 },
            { name: 'Large (22oz)', priceAdd: 25 }
        ],
        sugarLevels: ['25%', '50%', '75%', '100%'],
        iceLevels: ['Less Ice', 'Normal Ice'],
        addons: [
            { name: 'Extra Boba Pearls', price: 20 },
            { name: 'Egg Pudding', price: 25 },
            { name: 'Cheese Foam', price: 30 }
        ]
    },
    {
        id: 'PROD-007',
        name: 'Butter Croissant with Honey Butter',
        category: 'pastries',
        price: 85.00,
        cost: 35.00,
        stock: 22,
        rating: 4.9,
        badge: 'Freshly Baked',
        image: 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600&auto=format&fit=crop&q=80',
        description: 'Flaky, buttery French croissant baked daily at 6:00 AM, served with Iloilo farm honey butter.',
        hasCustomizations: false,
        sizes: [],
        sugarLevels: [],
        iceLevels: [],
        addons: [
            { name: 'Warm / Toasted', price: 0 },
            { name: 'Extra Cream Cheese', price: 25 }
        ]
    },
    {
        id: 'PROD-008',
        name: 'Ube Halaya Basque Cheesecake Slice',
        category: 'pastries',
        price: 110.00,
        cost: 45.00,
        stock: 14,
        rating: 4.9,
        badge: 'Campus Favorite',
        image: 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=600&auto=format&fit=crop&q=80',
        description: 'Silky burnt cheesecake infused with authentic Filipino purple yam halaya.',
        hasCustomizations: false,
        sizes: [],
        sugarLevels: [],
        iceLevels: [],
        addons: []
    },
    {
        id: 'PROD-009',
        name: 'Clubhouse Panini with Fries',
        category: 'meals',
        price: 145.00,
        cost: 65.00,
        stock: 18,
        rating: 4.7,
        badge: '',
        image: 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=600&auto=format&fit=crop&q=80',
        description: 'Smoked ham, grilled chicken, egg, cheddar, and crisp lettuce on toasted sourdough panini.',
        hasCustomizations: false,
        sizes: [],
        sugarLevels: [],
        iceLevels: [],
        addons: [
            { name: 'Extra Bacon', price: 35 },
            { name: 'Cheese Dip', price: 25 }
        ]
    },
    {
        id: 'PROD-010',
        name: 'Creamy Pesto Pasta with Garlic Toast',
        category: 'meals',
        price: 160.00,
        cost: 70.00,
        stock: 12,
        rating: 4.8,
        badge: '',
        image: 'https://images.unsplash.com/photo-1621996346565-e3d5d6281691?w=600&auto=format&fit=crop&q=80',
        description: 'Al dente penne in fresh basil parmesan cream sauce with grilled herb chicken.',
        hasCustomizations: false,
        sizes: [],
        sugarLevels: [],
        iceLevels: [],
        addons: [
            { name: 'Extra Garlic Bread (2pcs)', price: 30 },
            { name: 'Parmesan Shavings', price: 20 }
        ]
    }
];

const INITIAL_TABLES = [
    { id: 'T-01', name: 'Table 1 (Lounge)', zone: 'Lounge Zone', capacity: 2, status: 'available', icon: 'sofa', description: 'Cozy leather seating with low coffee table.' },
    { id: 'T-02', name: 'Table 2 (Lounge)', zone: 'Lounge Zone', capacity: 4, status: 'available', icon: 'sofa', description: 'Spacious plush sofa booth near window view.' },
    { id: 'T-03', name: 'Study Pod A1', zone: 'Study Hub', capacity: 1, status: 'reserved', icon: 'monitor', description: 'Individual quiet pod with USB-C/power outlet & desk lamp.' },
    { id: 'T-04', name: 'Study Pod A2', zone: 'Study Hub', capacity: 1, status: 'available', icon: 'monitor', description: 'Individual quiet pod with high-speed campus Wi-Fi.' },
    { id: 'T-05', name: 'Study Pod B1 (Duo)', zone: 'Study Hub', capacity: 2, status: 'available', icon: 'users', description: 'Dual workstation with whiteboard divider.' },
    { id: 'T-06', name: 'Table 6 (Main Café)', zone: 'Main Hall', capacity: 4, status: 'available', icon: 'coffee', description: 'Central café wooden dining table.' },
    { id: 'T-07', name: 'Table 7 (Main Café)', zone: 'Main Hall', capacity: 6, status: 'occupied', icon: 'coffee', description: 'Large group table for research teams & org meetings.' },
    { id: 'T-08', name: 'Terrace T1 (Al Fresco)', zone: 'Outdoor Terrace', capacity: 2, status: 'available', icon: 'sun', description: 'Open-air garden terrace with fresh breeze.' },
    { id: 'T-09', name: 'Terrace T2 (Al Fresco)', zone: 'Outdoor Terrace', capacity: 4, status: 'available', icon: 'sun', description: 'Shaded terrace seating overlooking university grounds.' }
];

const INITIAL_RESERVATIONS = [
    {
        id: 'RES-20260830-001',
        qrCode: 'ISATU-RES-20260830-001-A9F2',
        customerName: 'Engr. Mark Villanueva',
        customerType: 'Faculty',
        idNumber: 'ISATU-FAC-2018-091',
        phone: '0917-123-4567',
        email: 'mark.villanueva@isatu.edu.ph',
        tableId: 'T-03',
        tableName: 'Study Pod A1',
        date: '2026-08-30',
        timeSlot: '10:00 AM - 12:00 PM',
        guests: 1,
        status: 'Confirmed', // Confirmed, Checked-In, Completed, Cancelled
        preOrderItems: [
            {
                productId: 'PROD-001',
                name: 'ISAT U Golden Tamaraw Latte',
                size: 'Large (16oz)',
                sugar: '50%',
                ice: 'Normal Ice',
                addons: ['Extra Espresso Shot'],
                qty: 1,
                price: 160.00
            },
            {
                productId: 'PROD-007',
                name: 'Butter Croissant with Honey Butter',
                size: 'Standard',
                sugar: '-',
                ice: '-',
                addons: ['Warm / Toasted'],
                qty: 1,
                price: 85.00
            }
        ],
        totalAmount: 245.00,
        paymentStatus: 'Paid (GCash)',
        createdAt: '2026-08-30 08:30 AM'
    }
];

const INITIAL_ORDERS = [
    {
        id: 'ORD-1001',
        type: 'Dine-In',
        tableNumber: 'Table 6',
        customerName: 'Jasmine Soriano (Student)',
        cashier: 'Staff - Bea R.',
        status: 'Brewing', // Pending, Brewing, Ready, Completed
        items: [
            {
                id: 'PROD-001',
                name: 'ISAT U Golden Tamaraw Latte',
                size: 'Regular (12oz)',
                sugar: '75%',
                ice: 'Normal Ice',
                addons: ['Coffee Jelly'],
                qty: 1,
                price: 155.00
            },
            {
                id: 'PROD-008',
                name: 'Ube Halaya Basque Cheesecake Slice',
                size: 'Standard',
                sugar: '-',
                ice: '-',
                addons: [],
                qty: 1,
                price: 110.00
            }
        ],
        subtotal: 265.00,
        discountType: 'ISAT U Student (10%)',
        discountAmount: 26.50,
        vatAmount: 28.39,
        netTotal: 238.50,
        paymentMethod: 'GCash',
        paymentRef: 'GCASH-902381283',
        time: '09:15 AM',
        timestamp: Date.now() - 600000
    },
    {
        id: 'ORD-1002',
        type: 'Takeout',
        tableNumber: 'Pickup Counter',
        customerName: 'Prof. Dennis Ramos',
        cashier: 'Staff - Bea R.',
        status: 'Ready',
        items: [
            {
                id: 'PROD-005',
                name: 'Nitro Sweet Cream Cold Brew',
                size: 'Large (16oz)',
                sugar: '25%',
                ice: 'Less Ice',
                addons: [],
                qty: 2,
                price: 280.00
            }
        ],
        subtotal: 280.00,
        discountType: 'None',
        discountAmount: 0.00,
        vatAmount: 30.00,
        netTotal: 280.00,
        paymentMethod: 'Cash',
        paymentRef: 'CASH-₱500 (Change: ₱220)',
        time: '09:20 AM',
        timestamp: Date.now() - 300000
    }
];
