<?php 
require_once 'header.php'; 

// Fetch Stats
$total_invoices = $db->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
$total_customers = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$total_revenue = $db->query("SELECT SUM(total) FROM invoices WHERE status = 'paid'")->fetchColumn() ?: 0;
$pending_revenue = $db->query("SELECT SUM(total) FROM invoices WHERE status = 'pending'")->fetchColumn() ?: 0;

$recent_invoices = $db->query("SELECT * FROM invoices ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Dashboard Overview</h1>
        <p class="text-gray-500 mt-2">Welcome back to your Ecommerce CRM.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-green-50 text-green-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Revenue (Paid)</p>
                <p class="text-2xl font-bold"><?= htmlspecialchars($site_settings['currency']) . number_format($total_revenue, 2) ?></p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pending</p>
                <p class="text-2xl font-bold"><?= htmlspecialchars($site_settings['currency']) . number_format($pending_revenue, 2) ?></p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Invoices</p>
                <p class="text-2xl font-bold"><?= $total_invoices ?></p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Customers</p>
                <p class="text-2xl font-bold"><?= $total_customers ?></p>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold">Recent Invoices</h2>
                <a href="invoices.php" class="text-blue-600 text-sm font-medium hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($recent_invoices as $inv): ?>
                        <tr class="hover:bg-gray-50 transition-all cursor-pointer" onclick="window.location='view_invoice.php?id=<?= $inv['id'] ?>'">
                            <td class="px-6 py-4 font-bold text-blue-600 uppercase">#<?= htmlspecialchars($inv['invoice_number']) ?></td>
                            <td class="px-6 py-4 text-sm font-medium"><?= htmlspecialchars($inv['customer_name'] ?: 'Guest') ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?= date('M d, Y', strtotime($inv['date'])) ?></td>
                            <td class="px-6 py-4 text-right font-bold"><?= htmlspecialchars($site_settings['currency']) . number_format($inv['total'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($recent_invoices)): ?>
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400">No invoices yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl shadow-xl p-8 text-white relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="text-2xl font-bold mb-4">Quick Actions</h3>
                <div class="space-y-4">
                    <a href="new_invoice.php" class="flex items-center gap-4 bg-white/10 hover:bg-white/20 p-4 rounded-xl transition-all group">
                        <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </div>
                        <div>
                            <p class="font-bold group-hover:translate-x-1 transition-transform">Create New Invoice</p>
                            <p class="text-blue-100 text-xs">Generate and print instantly</p>
                        </div>
                    </a>
                    <a href="customers.php" class="flex items-center gap-4 bg-white/10 hover:bg-white/20 p-4 rounded-xl transition-all group">
                        <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        </div>
                        <div>
                            <p class="font-bold group-hover:translate-x-1 transition-transform">Add Customer</p>
                            <p class="text-blue-100 text-xs">Expand your database</p>
                        </div>
                    </a>
                </div>
            </div>
            <!-- Decorative circle -->
            <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-white/5 rounded-full"></div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
