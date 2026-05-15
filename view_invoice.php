<?php
require_once 'db.php';

$id = $_GET['id'] ?? 0;
$inv_stmt = $db->prepare("SELECT * FROM invoices WHERE id = ?");
$inv_stmt->execute([$id]);
$invoice = $inv_stmt->fetch();

if (!$invoice) {
    die("Invoice not found.");
}

$c_stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$c_stmt->execute([$invoice['customer_id']]);
$customer = $c_stmt->fetch();

$items = $db->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$items->execute([$id]);
$invoice_items = $items->fetchAll();

$settings_stmt = $db->query("SELECT * FROM settings LIMIT 1");
$site_settings = $settings_stmt->fetch();

require_once 'header.php';
?>

<div class="space-y-8 animate-in fade-in duration-500">
    <!-- Header Actions - Hidden on Print -->
    <div class="no-print flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="invoices.php" class="p-2 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold tracking-tight uppercase">Invoice #<?= htmlspecialchars($invoice['invoice_number']) ?></h1>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?= $invoice['status'] === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>">
                        <?= $invoice['status'] ?>
                    </span>
                </div>
                <p class="text-gray-500 mt-1 italic">Generated on <?= date('M d, Y', strtotime($invoice['date'])) ?></p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="flex items-center gap-2 px-6 py-3 bg-white border border-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-50 transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Invoice
            </button>
        </div>
    </div>

    <!-- The Actual Invoice Template -->
    <div class="bg-white rounded-3xl shadow-xl overflow-hidden print:shadow-none print:m-0 print:p-0 print:border-none">
        <div class="p-12 md:p-16 space-y-12">
            <!-- Branding -->
            <div class="flex flex-col md:flex-row justify-between gap-12">
                <div class="space-y-6">
                    <?php if (!empty($site_settings['logo_url'])): ?>
                        <img src="<?= htmlspecialchars($site_settings['logo_url']) ?>" alt="Logo" class="h-16 object-contain">
                    <?php else: ?>
                        <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center text-white font-bold text-3xl">I</div>
                    <?php endif; ?>
                    
                    <div class="space-y-1">
                        <h2 class="text-xl font-black uppercase tracking-tighter"><?= htmlspecialchars($site_settings['admin_name']) ?></h2>
                        <p class="text-gray-500 text-sm whitespace-pre-wrap leading-relaxed max-w-xs lowercase"><?= htmlspecialchars($site_settings['business_address']) ?></p>
                        <div class="pt-4 text-xs font-bold text-blue-600 uppercase tracking-widest">
                            <?= htmlspecialchars($site_settings['email']) ?> | <?= htmlspecialchars($site_settings['phone']) ?>
                        </div>
                    </div>
                </div>

                <div class="text-right space-y-6">
                    <h1 class="text-6xl font-black uppercase tracking-tighter opacity-10">Invoice</h1>
                    <div class="space-y-2">
                        <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Invoice Number</p>
                        <p class="text-3xl font-black text-blue-600 uppercase tracking-tighter">#<?= htmlspecialchars($invoice['invoice_number']) ?></p>
                    </div>
                </div>
            </div>

            <div class="h-px bg-gray-100"></div>

            <!-- Billing Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div class="space-y-4">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.25em] text-gray-400">Billed To</h3>
                    <div class="space-y-1">
                        <p class="text-2xl font-black uppercase tracking-tight"><?= htmlspecialchars($customer['name']) ?></p>
                        <p class="text-gray-500 text-sm italic"><?= htmlspecialchars($customer['address']) ?></p>
                        <div class="pt-2 text-xs font-bold text-gray-400 flex flex-col uppercase tracking-widest gap-1">
                            <span><?= htmlspecialchars($customer['email']) ?></span>
                            <span><?= htmlspecialchars($customer['phone']) ?></span>
                        </div>
                    </div>
                    <div class="pt-6">
                        <div class="inline-flex flex-col px-6 py-2 bg-gray-900 text-white rounded-lg">
                            <span class="text-[8px] font-bold uppercase tracking-[0.3em] opacity-50">Issue Date</span>
                            <span class="font-bold text-sm uppercase"><?= date('M d, Y', strtotime($invoice['date'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl border border-gray-100">
                <table class="w-full text-left">
                    <thead class="bg-gray-100 text-[10px] uppercase font-black tracking-[0.2em] text-gray-500">
                        <tr>
                            <th class="px-8 py-5">S.NO</th>
                            <th class="px-8 py-5">Description</th>
                            <th class="px-8 py-5 text-center">Qty</th>
                            <th class="px-8 py-5 text-right">Price</th>
                            <th class="px-8 py-5 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($invoice_items as $i => $item): ?>
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-8 py-6 font-mono text-xs text-gray-300"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></td>
                            <td class="px-8 py-6 font-bold uppercase text-gray-900"><?= htmlspecialchars($item['description']) ?></td>
                            <td class="px-8 py-6 text-center text-gray-600 font-medium"><?= $item['quantity'] ?></td>
                            <td class="px-8 py-6 text-right text-gray-600"><?= htmlspecialchars($site_settings['currency']) . number_format($item['price'], 2) ?></td>
                            <td class="px-8 py-6 text-right font-black"><?= htmlspecialchars($site_settings['currency']) . number_format($item['total'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="flex flex-col md:flex-row justify-end gap-12">
                <div class="w-full md:w-80 space-y-4">
                    <div class="space-y-4 border-t-4 border-gray-900 pt-6 px-2">
                        <div class="flex justify-between text-xs font-bold uppercase tracking-[0.2em] text-gray-400">
                            <span>Subtotal</span>
                            <span class="text-gray-900"><?= htmlspecialchars($site_settings['currency']) . number_format($invoice['subtotal'], 2) ?></span>
                        </div>
                        <div class="flex justify-between text-xs font-bold uppercase tracking-[0.2em] text-gray-400">
                            <span>Tax (10%)</span>
                            <span class="text-gray-900"><?= htmlspecialchars($site_settings['currency']) . number_format($invoice['tax'], 2) ?></span>
                        </div>
                        <div class="h-px bg-gray-100 my-4"></div>
                        <div class="flex justify-between text-4xl font-black uppercase tracking-tighter">
                            <span>Total</span>
                            <span class="text-blue-600"><?= htmlspecialchars($site_settings['currency']) . number_format($invoice['total'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-20 text-center">
                <p class="text-[10px] font-black uppercase tracking-[0.5em] text-gray-300 italic">Thank you for your business</p>
                <div class="mt-8 flex justify-center opacity-10 grayscale border-t border-gray-100 pt-8 gap-12">
                     <span class="font-black italic uppercase tracking-widest">Mastercard</span>
                     <span class="font-black italic uppercase tracking-widest">Visa</span>
                     <span class="font-black italic uppercase tracking-widest">Paypal</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
