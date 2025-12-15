@extends('layouts.app')

@section('title', 'Point of Sale - Motorshop POS')

@section('content')
<h2 style="margin-bottom: 1.5rem;">Point of Sale</h2>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; height: calc(100vh - 160px); overflow: hidden;">
    <!-- Left Side: Product Selection -->
    <div class="card" style="display:flex; flex-direction:column; overflow:hidden;">
        <div class="card-header">Product Selection</div>
        
        <div class="form-group">
            <label class="form-label">Search Products or Services</label>
            <div style="display:flex; gap:0.5rem; align-items:center; margin-bottom:0.5rem;">
                <div style="display:flex; gap:0.25rem;">
                    <button type="button" id="searchModeProduct" class="btn btn-primary" style="padding: 0.35rem 0.6rem; font-size: 0.85rem;">Products</button>
                    <button type="button" id="searchModeService" class="btn" style="padding: 0.35rem 0.6rem; font-size: 0.85rem;">Services</button>
                </div>
                <input type="text" id="productSearch" class="form-control" placeholder="Scan barcode or search product name..." autofocus style="flex: 1;">
            </div>
        </div>

        <!-- Item Cards: Equipment grid -->
        <div id="itemCardsContainer" style="flex:1; overflow-y:auto; border:1px solid #eee; padding:0.5rem; border-radius:4px; margin-bottom:1rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                <strong>Equipment Items</strong>
                <button type="button" id="refreshItemsBtn" class="btn btn-sm">Refresh</button>
            </div>
            <div id="itemCards" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap:0.5rem;"></div>
        </div>

        <!-- Product Search Results -->
        <div id="searchResults" style="display: none; max-height: 28vh; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 1rem;">
            <!-- Results will be populated here -->
        </div>

        <!-- Cart Items -->
        <div style="margin-top: 1rem; overflow-y:auto;">
            <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Cart Items</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="cartItems">
                    <tr id="emptyCartRow">
                        <td colspan="5" style="text-align: center; color: #7f8c8d;">Cart is empty</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Side: Payment -->
    <div class="card" style="display:flex; flex-direction:column; overflow:hidden;">
        <div class="card-header">Payment</div>
        
        <div style="background: #ecf0f1; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <div style="font-size: 0.9rem; color: #7f8c8d;">Total Amount:</div>
            <div id="totalAmount" style="font-size: 2rem; font-weight: bold; color: #2c3e50;">₱0.00</div>
        </div>

        <form id="checkoutForm" style="display:flex; flex-direction:column; gap:0.5rem;">
            <div class="form-group">
                <label class="form-label">Customer (Optional)</label>
                <input type="text" id="customerSearch" class="form-control" placeholder="Search customer name or phone...">
                <input type="hidden" id="customerId" name="customer_id">
                <div id="customerResults" style="display: none; border: 1px solid #ddd; border-radius: 4px; max-height: 150px; overflow-y: auto; margin-top: 0.5rem;">
                    <!-- Customer search results -->
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Payment Method</label>
                <select id="paymentMethod" name="payment_method" class="form-control" required>
                    <option value="cash">Cash</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="debit_card">Debit Card</option>
                    <option value="gcash">GCash</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Amount Received</label>
                <input type="number" id="amountReceived" class="form-control" placeholder="0.00" step="0.01" required>
            </div>

            <div class="form-group">
                <label class="form-label">Change Due</label>
                <input type="text" id="changeDue" class="form-control" readonly style="background: #f8f9fa; font-weight: bold; font-size: 1.1rem;">
            </div>

            <button type="submit" class="btn btn-success" style="width: 100%; margin-bottom: 0.5rem;" id="completeSaleBtn">Complete Sale</button>
            <button type="button" class="btn btn-danger" style="width: 100%;" id="clearCartBtn">Clear Cart</button>
        </form>
    </div>
</div>

<script>
const CURRENT_USER_NAME = {{ json_encode(optional(Auth::user())->name ?? '') }};
// Cart array to store items
let cart = [];
let searchMode = 'product'; // or 'service'

// Product search functionality
document.getElementById('productSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value;
    
    if (searchTerm.length < 2) {
        const sr = document.getElementById('searchResults');
        if (sr) sr.style.display = 'none';
        return;
    }

    // Fetch products or services from server depending on mode
    const endpoint = searchMode === 'service' ? '/api/services/search' : '/api/products/search';
    fetch(`${endpoint}?q=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(products => {
            const resultsDiv = document.getElementById('searchResults');
            if (!resultsDiv) return;
            
            if (products.length === 0) {
                resultsDiv.style.display = 'none';
                return;
            }

            if (searchMode === 'service') {
                resultsDiv.innerHTML = products.map(service => `
                    <div style="padding: 0.75rem; border-bottom: 1px solid #eee; cursor: pointer;" onclick="addToCartService(${service.id}, '${service.name.replace(/'/g, "\\'")}', ${service.labor_fee}, '${service.code}')">
                        <strong>${service.name}</strong> (${service.code})<br>
                        <span style="color: #7f8c8d; font-size: 0.9rem;">Fee: ₱${parseFloat(service.labor_fee).toFixed(2)} | Est: ${service.estimated_duration ?? 'N/A'}</span>
                    </div>
                `).join('');
            } else {
                resultsDiv.innerHTML = products.map(product => `
                    <div style="padding: 0.75rem; border-bottom: 1px solid #eee; cursor: pointer;" onclick="addToCart(${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.selling_price}, '${product.sku}')">
                        <strong>${product.name}</strong> (${product.sku})<br>
                        <span style="color: #7f8c8d; font-size: 0.9rem;">Stock: ${product.stock} | Price: ₱${parseFloat(product.selling_price).toFixed(2)}</span>
                    </div>
                `).join('');
            }
            
            resultsDiv.style.display = 'block';
        })
        .catch(error => console.error('Error:', error));
});

// Customer search functionality
document.getElementById('customerSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value;
    
    if (searchTerm.length < 2) {
        const cr = document.getElementById('customerResults');
        if (cr) cr.style.display = 'none';
        return;
    }

    fetch(`/api/customers/search?q=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(customers => {
            const resultsDiv = document.getElementById('customerResults');
            if (!resultsDiv) return;
            
            if (customers.length === 0) {
                resultsDiv.style.display = 'none';
                return;
            }

            resultsDiv.innerHTML = customers.map(customer => `
                <div style="padding: 0.75rem; border-bottom: 1px solid #eee; cursor: pointer;" onclick="selectCustomer(${customer.id}, '${customer.first_name} ${customer.last_name}')">
                    <strong>${customer.first_name} ${customer.last_name}</strong><br>
                    <span style="color: #7f8c8d; font-size: 0.9rem;">${customer.phone}</span>
                </div>
            `).join('');
            
            resultsDiv.style.display = 'block';
        });
});

// Search mode toggles for products and services
document.getElementById('searchModeProduct').addEventListener('click', function() {
    searchMode = 'product';
    this.classList.add('btn-primary');
    this.classList.remove('btn');
    document.getElementById('searchModeService').classList.remove('btn-primary');
    document.getElementById('searchModeService').classList.add('btn');
    document.getElementById('productSearch').placeholder = 'Scan barcode or search product name...';
    document.getElementById('productSearch').focus();
    loadItemCards();
});

document.getElementById('searchModeService').addEventListener('click', function() {
    searchMode = 'service';
    this.classList.add('btn-primary');
    this.classList.remove('btn');
    document.getElementById('searchModeProduct').classList.remove('btn-primary');
    document.getElementById('searchModeProduct').classList.add('btn');
    document.getElementById('productSearch').placeholder = 'Search services by name or code...';
    document.getElementById('productSearch').focus();
    loadItemCards();
});

function selectCustomer(id, name) {
    const cid = document.getElementById('customerId'); if (cid) cid.value = id;
    const cs = document.getElementById('customerSearch'); if (cs) cs.value = name;
    const cr = document.getElementById('customerResults'); if (cr) cr.style.display = 'none';
}

function addToCart(productId, productName, price, sku) {
    // Add product item to cart
    const cartId = 'p_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
    const existingItem = cart.find(item => item.type === 'product' && item.id === productId);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            cartId: cartId,
            type: 'product',
            id: productId,
            name: productName,
            price: parseFloat(price),
            quantity: 1,
            sku: sku
        });
    }
    
    updateCart();
    document.getElementById('productSearch').value = '';
    const sr = document.getElementById('searchResults'); if (sr) sr.style.display = 'none';
}

function addToCartService(serviceId, serviceName, price, code) {
    const cartId = 's_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
    const existingItem = cart.find(item => item.type === 'service' && item.service_id === serviceId);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            cartId: cartId,
            type: 'service',
            service_id: serviceId,
            name: serviceName,
            price: parseFloat(price),
            quantity: 1,
            code: code
        });
    }
    updateCart();
    document.getElementById('productSearch').value = '';
    const sr = document.getElementById('searchResults'); if (sr) sr.style.display = 'none';
}

function removeFromCart(cartId) {
    cart = cart.filter(item => item.cartId !== cartId);
    updateCart();
}
function updateQuantity(cartId, newQuantity) {
    const item = cart.find(item => item.cartId === cartId);
    if (item && newQuantity > 0) {
        item.quantity = parseInt(newQuantity);
        updateCart();
    }
}

function updateCart() {
    const cartItemsBody = document.getElementById('cartItems');
    if (!cartItemsBody) return;
    
    if (cart.length === 0) {
        cartItemsBody.innerHTML = '<tr id="emptyCartRow"><td colspan="5" style="text-align: center; color: #7f8c8d;">Cart is empty</td></tr>';
    } else {
        cartItemsBody.innerHTML = cart.map(item => {
            const subtotal = item.price * item.quantity;
            const codeOrSku = item.type === 'product' ? item.sku : (item.code || 'SERV');
            const typeBadge = item.type === 'service' ? '<small style="color:#fff; background:#3498db; padding:0.15rem 0.25rem; border-radius:4px; font-size:0.75rem;">Service</small>' : '';
            return `
                <tr>
                    <td><strong>${item.name} ${typeBadge}</strong><br><small style="color: #7f8c8d;">${codeOrSku}</small></td>
                    <td>₱${item.price.toFixed(2)}</td>
                    <td><input type="number" value="${item.quantity}" min="1" style="width: 60px; padding: 0.25rem;" onchange="updateQuantity('${item.cartId}', this.value)"></td>
                    <td><strong>₱${subtotal.toFixed(2)}</strong></td>
                    <td><button type="button" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" onclick="removeFromCart('${item.cartId}')">Remove</button></td>
                </tr>
            `;
        }).join('');
    }
    
    updateTotal();
}

function updateTotal() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    document.getElementById('totalAmount').textContent = '₱' + total.toFixed(2);
    calculateChange();
}

document.getElementById('amountReceived').addEventListener('input', calculateChange);

function calculateChange() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const amtEl = document.getElementById('amountReceived');
    const received = amtEl ? (parseFloat(amtEl.value) || 0) : 0;
    const change = received - total;
    
    const changeEl = document.getElementById('changeDue');
    if (changeEl) {
        changeEl.value = change >= 0 ? '₱' + change.toFixed(2) : '₱0.00';
        changeEl.style.color = change >= 0 ? '#27ae60' : '#e74c3c';
    }
}

document.getElementById('clearCartBtn').addEventListener('click', function() {
    if (confirm('Are you sure you want to clear the cart?')) {
        cart = [];
        updateCart();
        document.getElementById('customerSearch').value = '';
        document.getElementById('customerId').value = '';
        document.getElementById('amountReceived').value = '';
        const cd = document.getElementById('changeDue'); if (cd) { cd.value = ''; cd.style.color = ''; }
    }
});

document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (cart.length === 0) {
        alert('Cart is empty!');
        return;
    }
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const received = parseFloat(document.getElementById('amountReceived').value) || 0;
    
    if (received < total) {
        alert('Amount received is less than total amount!');
        return;
    }
    
    const itemsPayload = cart.map(item => {
        if (item.type === 'service') {
            return {
                service_id: item.service_id,
                code: item.code || null,
                quantity: item.quantity,
                price: item.price
            };
        }
        return {
            id: item.id,
            quantity: item.quantity,
            price: item.price
        };
    });

    const saleData = {
        customer_id: document.getElementById('customerId').value || null,
        payment_method: document.getElementById('paymentMethod').value,
        amount_paid: received,
        items: itemsPayload
    };
    
    // Submit sale to server
    const completeBtn = document.getElementById('completeSaleBtn');
    completeBtn.disabled = true;
    fetch('/api/sales', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify(saleData)
    })
    .then(async response => {
        let data;
        try {
            data = await response.json();
        } catch (err) {
            throw new Error('Invalid server response');
        }
        if (!response.ok) {
            throw new Error(data.message || 'Server error while processing the sale');
        }
        return data;
    })
    .then(data => {
        if (data.success) {
            alert('Sale completed successfully!');
            cart = [];
            updateCart();
            document.getElementById('checkoutForm').reset();
            document.getElementById('customerSearch').value = '';
            const cs = document.getElementById('customerSearch'); if (cs) cs.value = '';
            const cid = document.getElementById('customerId'); if (cid) cid.value = '';
            // show receipt and allow printing, then return to POS start
            showReceipt({
                sale: data.sale || data,
                items: saleData.items,
                total: total,
                received: received,
                change: received - total
            });
        } else {
            alert('Error: ' + (data.message || 'Sale failed'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the sale: ' + (error.message || error));
    })
    .finally(() => {
        completeBtn.disabled = false;
    });
});

// Equipment item cards loader
function renderItemCard(item) {
    const price = parseFloat(item.selling_price || item.price || 0).toFixed(2);
    const stock = item.stock ?? 'N/A';
    const sku = item.sku || item.code || '';
    return `
        <div style="border:1px solid #e6e6e6; padding:0.5rem; border-radius:6px; background:#fff; cursor:pointer; display:flex; flex-direction:column; gap:0.25rem;" onclick="addToCart(${item.id}, '${(item.name||'').replace(/'/g, "\\'")}', ${price}, '${(sku||'').replace(/'/g, "\\'")}')">
            <div style="font-weight:600; font-size:0.95rem;">${item.name}</div>
            <div style="color:#7f8c8d; font-size:0.8rem;">${sku}</div>
            <div style="margin-top:auto; display:flex; justify-content:space-between; align-items:center;">
                <div style="font-weight:700;">₱${price}</div>
                <div style="font-size:0.8rem; color:#95a5a6;">Stock: ${stock}</div>
            </div>
        </div>
    `;
}

function renderServiceCard(item) {
    const price = parseFloat(item.labor_fee || 0).toFixed(2);
    const code = item.code || '';
    return `
        <div style="border:1px solid #e6e6e6; padding:0.5rem; border-radius:6px; background:#fff; cursor:pointer; display:flex; flex-direction:column; gap:0.25rem;" onclick="addToCartService(${item.id}, '${(item.name||'').replace(/'/g, "\\'")}', ${price}, '${(code||'').replace(/'/g, "\\'")}')">
            <div style="font-weight:600; font-size:0.95rem;">${item.name}</div>
            <div style="color:#7f8c8d; font-size:0.8rem;">${code}</div>
            <div style="margin-top:auto; display:flex; justify-content:space-between; align-items:center;">
                <div style="font-weight:700;">₱${price}</div>
                <div style="font-size:0.8rem; color:#95a5a6;">Est: ${item.estimated_duration || 'N/A'}</div>
            </div>
        </div>
    `;
}

function loadItemCards() {
    const container = document.getElementById('itemCards');
    container.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#7f8c8d; padding:1rem;">Loading items...</div>';
    if (searchMode === 'service') {
        fetch('/api/services')
            .then(res => res.json())
            .then(items => {
                if (!items || items.length === 0) {
                    container.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#7f8c8d; padding:1rem;">No services found.</div>';
                    return;
                }
                container.innerHTML = items.map(item => renderServiceCard(item)).join('');
            })
            .catch(err => {
                console.error('Failed to load services', err);
                container.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#e74c3c; padding:1rem;">Error loading services</div>';
            });
    } else {
        fetch('/api/equipment')
            .then(res => res.json())
            .then(items => {
                if (!items || items.length === 0) {
                    container.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#7f8c8d; padding:1rem;">No equipment items found.</div>';
                    return;
                }
                container.innerHTML = items.map(item => renderItemCard(item)).join('');
            })
            .catch(err => {
                console.error('Failed to load equipment items', err);
                container.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#e74c3c; padding:1rem;">Error loading items</div>';
            });
    }
}

// Initial load and refresh handler
loadItemCards();
document.getElementById('refreshItemsBtn').addEventListener('click', loadItemCards);

// Receipt modal and printing
function showReceipt(data) {
    let receiptEl = document.getElementById('receiptModal');
    if (!receiptEl) return;

    const sale = data.sale || {};
    const items = data.items || [];
    const total = data.total || 0;
    const received = data.received || 0;
    const change = data.change || 0;

    const itemsHtml = items.map(i => {
        const name = i.name || i.code || (i.id ? 'Item #' + i.id : 'Item');
        const qty = i.quantity || 1;
        const price = parseFloat(i.price || 0).toFixed(2);
        const subtotal = (parseFloat(i.price || 0) * qty).toFixed(2);
        return `<tr><td>${name}</td><td style="text-align:right;">${qty}</td><td style="text-align:right;">₱${price}</td><td style="text-align:right;">₱${subtotal}</td></tr>`;
    }).join('');

    const printedBy = (typeof CURRENT_USER_NAME !== 'undefined' && CURRENT_USER_NAME) ? CURRENT_USER_NAME : (sale.user_name || '');

    receiptEl.querySelector('.receipt-content').innerHTML = `
        <div style="padding:1rem; font-family:Arial, Helvetica, sans-serif; color:#222;">
            <h3 style="margin:0 0 0.5rem 0; text-align:center;">GEARPOINT</h3>
            <div style="text-align:center; font-size:0.9rem; color:#555; margin-bottom:0.75rem;">Sales Receipt</div>
            <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                <thead><tr><th style="text-align:left;">Item</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Price</th><th style="text-align:right;">Subtotal</th></tr></thead>
                <tbody>${itemsHtml}</tbody>
                <tfoot>
                    <tr><td colspan="3" style="text-align:right; font-weight:700;">Total</td><td style="text-align:right; font-weight:700;">₱${total.toFixed(2)}</td></tr>
                    <tr><td colspan="3" style="text-align:right;">Received</td><td style="text-align:right;">₱${received.toFixed(2)}</td></tr>
                    <tr><td colspan="3" style="text-align:right;">Change</td><td style="text-align:right;">₱${change.toFixed(2)}</td></tr>
                </tfoot>
            </table>
            <div style="margin-top:1rem; font-size:0.85rem; color:#666; text-align:center;">${new Date().toLocaleString()} | Report by: ${printedBy || (sale.user_name || '')}</div>
        </div>
    `;

    receiptEl.style.display = 'flex';
}

function printReceipt() {
    const receiptEl = document.getElementById('receiptModal');
    if (!receiptEl) return;
    const content = receiptEl.querySelector('.receipt-content').innerHTML;
    const w = window.open('', '_blank', 'width=600,height=800');
    if (!w) return alert('Unable to open print window');
    w.document.write(`<!doctype html><html><head><title>Receipt</title><meta charset="utf-8"><style>body{font-family:Arial,Helvetica,sans-serif;padding:10px}</style></head><body>${content}</body></html>`);
    w.document.close();
    w.focus();
    setTimeout(() => { w.print(); /* do not auto-close to let user save */ }, 350);
}

function closeReceipt() {
    const receiptEl = document.getElementById('receiptModal');
    if (receiptEl) receiptEl.style.display = 'none';
    // Redirect back to POS start (reload to reset state)
    window.location.href = '/pos';
}

</script>

<!-- Receipt modal -->
<div id="receiptModal" style="display:none; position:fixed; inset:0; align-items:center; justify-content:center; background:rgba(0,0,0,0.45); z-index:9999;">
    <div style="background:#fff; width:420px; max-width:95%; border-radius:6px; overflow:hidden; box-shadow:0 8px 24px rgba(0,0,0,0.2);">
        <div style="padding:0.5rem 0.75rem; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
            <strong>Receipt</strong>
            <div>
                <button type="button" class="btn btn-sm btn-primary" onclick="printReceipt()" style="margin-right:0.5rem;">Print</button>
                <button type="button" class="btn btn-sm" onclick="closeReceipt()">Done</button>
            </div>
        </div>
        <div class="receipt-content" style="padding:0.75rem; max-height:70vh; overflow:auto;"></div>
    </div>
</div>
</script>
@endsection