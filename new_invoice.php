<?php 
require_once 'header.php'; 

$customers = $db->query("SELECT * FROM customers ORDER BY name ASC")->fetchAll();
$invoice_number = 'INV-' . strtoupper(substr(uniqid(), -6));
$selected_customer_id = $_GET['customer_id'] ?? '';
?>

<div class="space-y-8">
    <div class="flex items-center gap-4">
        <a href="invoices.php" class="p-2 hover:bg-gray-100 rounded-xl transition-all border border-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Create Invoice</h1>
            <p class="text-gray-500 mt-1">Select a customer and add purchase items.</p>
        </div>
    </div>

    <form action="save_invoice.php" method="POST" id="invoice-form" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <!-- Customer Section -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Customer Billing
                    </h2>
                    <a href="customers.php" class="text-blue-600 text-sm font-bold hover:underline">+ New Customer</a>
                </div>
                
                <select name="customer_id" id="customer_id" class="w-full px-4 py-4 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 transition-all font-bold uppercase tracking-tight" required>
                    <option value="">-- Choose Customer --</option>
                    <?php foreach($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $selected_customer_id == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['email'] ?: 'No email') ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Items Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-bold">Purchase Items</h2>
                    <button type="button" onclick="addItem()" class="text-blue-600 text-sm font-bold flex items-center gap-1 hover:bg-blue-50 px-3 py-1.5 rounded-lg transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Add Item
                    </button>
                </div>
                
                <div class="p-6">
                    <div id="items-container" class="space-y-4">
                        <div class="item-row flex gap-4">
                            <div class="flex-1">
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Description</label>
                                <input type="text" name="desc[]" class="w-full px-4 py-2 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="Product Name" required>
                            </div>
                            <div class="w-24">
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Qty</label>
                                <input type="number" name="qty[]" class="qty w-full px-4 py-2 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500" value="1" min="1" onchange="calculate()">
                            </div>
                            <div class="w-32">
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Price</label>
                                <input type="number" name="price[]" step="0.01" class="price w-full px-4 py-2 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="0.00" required onchange="calculate()">
                            </div>
                            <div class="w-10 pt-6"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="space-y-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                <h3 class="text-lg font-bold">Summary</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Invoice Number</label>
                        <input type="text" name="invoice_number" value="<?= $invoice_number ?>" class="w-full px-4 py-2 bg-blue-50 text-blue-700 border-none rounded-xl font-bold uppercase tracking-widest">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Invoice Date</label>
                        <div class="w-full px-4 py-2 bg-gray-50 text-gray-500 border-none rounded-xl font-medium">
                            <?= date('F d, Y') ?>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 space-y-3">
                    <div class="flex justify-between text-gray-500 text-sm">
                        <span>Subtotal</span>
                        <span id="subtotal-display"><?= htmlspecialchars($site_settings['currency']) ?> 0.00</span>
                    </div>
                    <div class="flex justify-between text-gray-500 text-sm">
                        <span>Tax (10%)</span>
                        <span id="tax-display"><?= htmlspecialchars($site_settings['currency']) ?> 0.00</span>
                    </div>
                    <div class="flex justify-between text-xl font-black text-gray-900 pt-3 border-t-2 border-gray-900 uppercase">
                        <span>Total</span>
                        <span id="total-display"><?= htmlspecialchars($site_settings['currency']) ?> 0.00</span>
                    </div>
                </div>

                <button type="submit" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold text-lg hover:bg-blue-700 shadow-xl shadow-blue-100 transition-all uppercase tracking-widest">
                    Generate Invoice
                </button>
            </div>

            <div class="bg-amber-50 p-6 rounded-2xl border border-amber-100">
                <p class="text-xs text-amber-800 leading-relaxed font-medium">
                    <span class="font-bold flex items-center gap-1 mb-1 italic">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Tip:
                    </span>
                    Ensure the customer profile is updated before generation. After saving, you will be redirected to the printable version.
                </p>
            </div>
        </div>
    </form>
</div>

<script>
function addItem() {
    const container = document.getElementById('items-container');
    const row = document.createElement('div');
    row.className = 'item-row flex gap-4 animate-in fade-in slide-in-from-top-1';
    row.innerHTML = `
        <div class="flex-1">
            <input type="text" name="desc[]" class="w-full px-4 py-2 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="Product Name" required>
        </div>
        <div class="w-24">
            <input type="number" name="qty[]" class="qty w-full px-4 py-2 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500" value="1" min="1" onchange="calculate()">
        </div>
        <div class="w-32">
            <input type="number" name="price[]" step="0.01" class="price w-full px-4 py-2 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="0.00" required onchange="calculate()">
        </div>
        <div class="w-10">
            <button type="button" onclick="this.parentElement.parentElement.remove(); calculate()" class="p-2 text-gray-300 hover:text-red-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        </div>
    `;
    container.appendChild(row);
}

function calculate() {
    let subtotal = 0;
    const currency = '<?= htmlspecialchars($site_settings['currency']) ?>';
    const rows = document.querySelectorAll('.item-row');
    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const price = parseFloat(row.querySelector('.price').value) || 0;
        subtotal += qty * price;
    });

    const tax = subtotal * 0.10; // 10% Tax
    const total = subtotal + tax;

    document.getElementById('subtotal-display').innerText = currency + ' ' + subtotal.toFixed(2);
    document.getElementById('tax-display').innerText = currency + ' ' + tax.toFixed(2);
    document.getElementById('total-display').innerText = currency + ' ' + total.toFixed(2);
}
</script>

<?php require_once 'footer.php'; ?>
