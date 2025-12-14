@extends('layouts.app')

@section('title', 'Add Product - Motorshop POS')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="margin: 0;">Add New Product</h2>
    <a href="{{ route('inventory') }}" class="btn btn-danger">← Back to Inventory</a>
</div>

@if($errors->any())
<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
    <strong>Please fix the following errors:</strong>
    <ul style="margin: 0.5rem 0 0 1.5rem;">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card">
    <div class="card-header">Product Information</div>
    
    <form action="{{ route('inventory.store') }}" method="POST">
        @csrf
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g., Motor Oil 10W-40" value="{{ old('name') }}" required autofocus>
                <span class="form-hint">Enter the full product name</span>
            </div>

            <div class="form-group">
                <label class="form-label">Product Code/SKU *</label>
                <input type="text" name="sku" class="form-control" placeholder="e.g., MO-10W40-001" value="{{ old('sku') }}" required>
                <span class="form-hint">Unique identifier for this product</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Category *</label>
                <div style="display:flex; gap:0.5rem; align-items:center;">
                    <select name="category_id" class="form-control" required style="flex:1;">
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                        <div style="display:flex; gap:0.5rem;">
                            <button type="button" id="add-category-btn" class="btn btn-primary">Add</button>
                            <button type="button" id="manage-category-btn" class="btn btn-danger">Manage</button>
                        </div>
                    @else
                        <!-- Non-managers see category select only -->
                    @endif
                </div>
                <span class="form-hint">Product category for organization. Click Add to create a new category.</span>
            </div>

            <div class="form-group">
                <label class="form-label">Brand</label>
                <input type="text" name="brand" class="form-control" placeholder="e.g., Honda, Yamaha, Shell" value="{{ old('brand') }}">
                <span class="form-hint">Manufacturer or brand name (optional)</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Cost Price (₱) *</label>
                <input type="number" name="cost_price" class="form-control" placeholder="0.00" step="0.01" value="{{ old('cost_price') }}" required>
                <span class="form-hint">How much you paid for this product</span>
            </div>

            <div class="form-group">
                <label class="form-label">Selling Price (₱) *</label>
                <input type="number" name="selling_price" class="form-control" placeholder="0.00" step="0.01" value="{{ old('selling_price') }}" required>
                <span class="form-hint">Price you charge customers</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Initial Stock Quantity *</label>
                <input type="number" name="stock" class="form-control" placeholder="0" value="{{ old('stock', 0) }}" required>
                <span class="form-hint">Number of units currently in stock</span>
            </div>

            <div class="form-group">
                <label class="form-label">Reorder Level *</label>
                <input type="number" name="reorder_level" class="form-control" placeholder="10" value="{{ old('reorder_level', 10) }}" required>
                <span class="form-hint">Alert when stock falls below this number</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Unit *</label>
                <select name="unit" class="form-control" required>
                    <option value="piece" {{ old('unit', 'piece') == 'piece' ? 'selected' : '' }}>Piece</option>
                    <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>Liter</option>
                    <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>Box</option>
                    <option value="set" {{ old('unit') == 'set' ? 'selected' : '' }}>Set</option>
                    <option value="pair" {{ old('unit') == 'pair' ? 'selected' : '' }}>Pair</option>
                    <option value="kilogram" {{ old('unit') == 'kilogram' ? 'selected' : '' }}>Kilogram</option>
                </select>
                <span class="form-hint">Unit of measurement</span>
            </div>

            <div class="form-group">
                <!-- Empty space for alignment -->
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Product description, specifications, compatibility information...">{{ old('description') }}</textarea>
            <span class="form-hint">Additional details about the product (optional)</span>
        </div>

        <div style="display: flex; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid #ddd;">
            <button type="submit" class="btn btn-success">✓ Save Product</button>
            <a href="{{ route('inventory') }}" class="btn btn-danger">✗ Cancel</a>
        </div>
    </form>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('add-category-btn');
        const select = document.querySelector('select[name="category_id"]');
        if (!btn || !select) return;

        btn.addEventListener('click', async function () {
            const name = prompt('Enter new category name:');
            if (!name) return;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch('/api/categories', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name })
                });
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    alert(err.message || 'Failed to create category');
                    return;
                }
                const cat = await res.json();
                const opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.name;
                opt.selected = true;
                select.appendChild(opt);
            } catch (e) {
                alert('Network error');
            }
        });
    });
    </script>
    
    <!-- Manage Categories Modal -->
    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
    <div id="manage-cat-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); align-items:center; justify-content:center;">
        <div style="background:#fff; width:600px; max-width:95%; border-radius:8px; padding:1rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                <strong>Manage Categories</strong>
                <button id="close-manage-cat" class="btn btn-danger">Close</button>
            </div>
            <div id="manage-cat-list" style="max-height:300px; overflow:auto; border:1px solid #eee; padding:0.5rem; border-radius:4px;"></div>
            <div style="margin-top:0.75rem; display:flex; gap:0.5rem; justify-content:flex-end;">
                <button id="refresh-cat-list" class="btn">Refresh</button>
            </div>
        </div>
    </div>
    @endif

    <script>
    (function(){
        const manageBtn = document.getElementById('manage-category-btn');
        const modal = document.getElementById('manage-cat-modal');
        const closeBtn = document.getElementById('close-manage-cat');
        const listContainer = document.getElementById('manage-cat-list');
        const refreshBtn = document.getElementById('refresh-cat-list');
        const select = document.querySelector('select[name="category_id"]');

        if (manageBtn && modal) {
            manageBtn.addEventListener('click', openModal);
            closeBtn.addEventListener('click', closeModal);
            refreshBtn.addEventListener('click', loadList);
        }

        function openModal() {
            modal.style.display = 'flex';
            loadList();
        }
        function closeModal() { modal.style.display = 'none'; }

        async function loadList() {
            listContainer.innerHTML = 'Loading...';
            try {
                const res = await fetch('/api/categories', { headers: { 'Accept': 'application/json' } });
                const cats = await res.json();
                renderList(cats);
            } catch (e) {
                listContainer.innerHTML = 'Failed to load';
            }
        }

        function renderList(cats) {
            listContainer.innerHTML = '';
            if (!cats.length) { listContainer.innerHTML = '<div>No categories</div>'; return; }
            cats.forEach(cat => {
                const row = document.createElement('div');
                row.style.display = 'flex';
                row.style.justifyContent = 'space-between';
                row.style.alignItems = 'center';
                row.style.padding = '0.25rem 0';

                const name = document.createElement('div');
                name.textContent = cat.name;
                name.style.flex = '1';

                const actions = document.createElement('div');
                actions.style.display = 'flex';
                actions.style.gap = '0.5rem';

                const editBtn = document.createElement('button');
                editBtn.className = 'btn';
                editBtn.textContent = 'Rename';
                editBtn.addEventListener('click', () => renameCategory(cat));

                const delBtn = document.createElement('button');
                delBtn.className = 'btn btn-danger';
                delBtn.textContent = 'Delete';
                delBtn.addEventListener('click', () => deleteCategory(cat));

                actions.appendChild(editBtn);
                actions.appendChild(delBtn);

                row.appendChild(name);
                row.appendChild(actions);
                listContainer.appendChild(row);
            });
        }

        async function renameCategory(cat) {
            const newName = prompt('Rename category', cat.name);
            if (!newName || newName === cat.name) return;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch('/api/categories/' + cat.id, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: JSON.stringify({ name: newName })
                });
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    alert(err.message || 'Failed to rename');
                    return;
                }
                const updated = await res.json();
                // update select option text
                const opt = select.querySelector('option[value="' + updated.id + '"]');
                if (opt) opt.textContent = updated.name;
                loadList();
            } catch (e) { alert('Network error'); }
        }

        async function deleteCategory(cat) {
            if (!confirm('Delete category "' + cat.name + '"? This cannot be undone.')) return;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch('/api/categories/' + cat.id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    alert(err.message || 'Failed to delete');
                    return;
                }
                // remove option from select
                const opt = select.querySelector('option[value="' + cat.id + '"]');
                if (opt) opt.remove();
                loadList();
            } catch (e) { alert('Network error'); }
        }
    })();
    </script>
</div>
@endsection