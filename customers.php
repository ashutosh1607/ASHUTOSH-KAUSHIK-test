<?php 
require_once 'header.php'; 

// Handle Add Customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    $stmt = $db->prepare("INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $address]);
    header("Location: customers.php?success=1");
    exit;
}

$search = $_GET['search'] ?? '';
$where = "WHERE 1=1";
if ($search) {
    $where .= " AND (name LIKE '%$search%' OR email LIKE '%$search%')";
}

$customers = $db->query("SELECT * FROM customers $where ORDER BY name ASC")->fetchAll();
?>

<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Customers</h1>
            <p class="text-gray-500 mt-2">Manage your ecommerce customer database.</p>
        </div>
        
        <button onclick="document.getElementById('customer-modal').classList.remove('hidden')" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all">
            + Add New Customer
        </button>
    </div>

    <!-- Search -->
    <div class="relative max-w-xl">
        <form method="GET">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email..." class="w-full pl-12 pr-4 py-4 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-blue-500 transition-all shadow-sm">
            <svg class="absolute left-4 top-4.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </form>
    </div>

    <!-- Customers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach($customers as $c): ?>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-all group relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50/50 rounded-full -translate-y-12 translate-x-12 opacity-0 group-hover:opacity-100 transition-all"></div>
            
            <div class="flex items-start gap-4 mb-6 relative z-10">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-bold text-xl uppercase">
                    <?= substr($c['name'], 0, 1) ?>
                </div>
                <div>
                    <h3 class="font-bold text-lg leading-tight uppercase tracking-tight"><?= htmlspecialchars($c['name']) ?></h3>
                    <p class="text-xs text-gray-400 font-medium">Joined <?= date('M Y', strtotime($c['created_at'])) ?></p>
                </div>
            </div>

            <div class="space-y-3 text-sm text-gray-600 relative z-10">
                <?php if($c['email']): ?>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span><?= htmlspecialchars($c['email']) ?></span>
                </div>
                <?php endif; ?>
                <?php if($c['phone']): ?>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    <span><?= htmlspecialchars($c['phone']) ?></span>
                </div>
                <?php endif; ?>
                <?php if($c['address']): ?>
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-gray-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="line-clamp-2"><?= htmlspecialchars($c['address']) ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="mt-6 pt-6 border-t border-gray-50 flex items-center justify-end">
                <a href="new_invoice.php?customer_id=<?= $c['id'] ?>" class="text-blue-600 font-bold text-xs uppercase hover:underline mb-2">Create Invoice</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Customer Modal -->
<div id="customer-modal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-2xl font-bold">Add New Customer</h2>
            <button onclick="document.getElementById('customer-modal').classList.add('hidden')" class="p-2 hover:bg-gray-100 rounded-xl transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l18 18"></path></svg>
            </button>
        </div>
        <form method="POST" class="p-8 space-y-4">
            <input type="hidden" name="add_customer" value="1">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                <input type="text" name="name" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 transition-all font-bold uppercase tracking-tight" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                    <input type="text" name="phone" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 transition-all">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Billing Address</label>
                <textarea name="address" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 transition-all resize-none italic"></textarea>
            </div>
            <div class="pt-4 flex gap-4">
                <button type="button" onclick="document.getElementById('customer-modal').classList.add('hidden')" class="flex-1 py-4 border border-gray-200 rounded-2xl font-bold text-gray-600 hover:bg-gray-50 transition-all">Cancel</button>
                <button type="submit" class="flex-1 py-4 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 shadow-xl shadow-blue-100 transition-all uppercase tracking-widest">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
