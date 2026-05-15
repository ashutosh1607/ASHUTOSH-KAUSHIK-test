<?php 
require_once 'header.php'; 

// Handle Status Change
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];
    $stmt = $db->prepare("UPDATE invoices SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    header("Location: invoices.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM invoices WHERE id = ?")->execute([$id]);
    header("Location: invoices.php");
    exit;
}

$invoices = $db->query("SELECT * FROM invoices ORDER BY id DESC")->fetchAll();
?>

<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Invoice History</h1>
            <p class="text-gray-500 mt-2">Manage and track all issued invoices.</p>
        </div>
        <a href="new_invoice.php" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all uppercase tracking-widest text-sm">
            + Create Invoice
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                <tr>
                    <th class="px-8 py-6">ID</th>
                    <th class="px-8 py-6">Customer</th>
                    <th class="px-8 py-6">Date</th>
                    <th class="px-8 py-6 text-right">Total</th>
                    <th class="px-8 py-6">Status</th>
                    <th class="px-8 py-6 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach($invoices as $inv): ?>
                <tr class="hover:bg-gray-50 transition-all group">
                    <td class="px-8 py-6 font-black text-blue-600 uppercase tracking-tighter">#<?= htmlspecialchars($inv['invoice_number']) ?></td>
                    <td class="px-8 py-6">
                        <p class="font-bold uppercase tracking-tight"><?= htmlspecialchars($inv['customer_name'] ?: 'Guest') ?></p>
                    </td>
                    <td class="px-8 py-6 text-sm text-gray-500"><?= date('M d, Y', strtotime($inv['date'])) ?></td>
                    <td class="px-8 py-6 text-right font-black text-gray-900"><?= htmlspecialchars($site_settings['currency']) . number_format($inv['total'], 2) ?></td>
                    <td class="px-8 py-6">
                        <select onchange="window.location='invoices.php?id=<?= $inv['id'] ?>&status=' + this.value" class="text-[10px] font-black uppercase rounded-full px-4 py-1.5 cursor-pointer border-none focus:ring-0 <?= $inv['status'] === 'paid' ? 'bg-green-100 text-green-700' : ($inv['status'] === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') ?>">
                            <option value="pending" <?= $inv['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="paid" <?= $inv['status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="cancelled" <?= $inv['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-all">
                            <a href="view_invoice.php?id=<?= $inv['id'] ?>" class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                            <a href="invoices.php?delete=<?= $inv['id'] ?>" onclick="return confirm('Are you sure?')" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($invoices)): ?>
                <tr>
                    <td colspan="6" class="px-8 py-20 text-center text-gray-400 font-medium">No invoices found in history.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>
