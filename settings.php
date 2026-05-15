<?php 
require_once 'header.php'; 

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_name = $_POST['admin_name'] ?? '';
    $logo_url = $_POST['logo_url'] ?? '';
    $business_address = $_POST['business_address'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $currency = $_POST['currency'] ?? '$';

    $stmt = $db->prepare("UPDATE settings SET admin_name = ?, logo_url = ?, business_address = ?, email = ?, phone = ?, currency = ? WHERE id = 1");
    if ($stmt->execute([$admin_name, $logo_url, $business_address, $email, $phone, $currency])) {
        $message = '<div class="bg-green-100 text-green-700 px-4 py-3 rounded-xl mb-6">Settings updated successfully!</div>';
        // Refresh settings for the current page load
        $settings_stmt = $db->query("SELECT * FROM settings LIMIT 1");
        $site_settings = $settings_stmt->fetch();
    } else {
        $message = '<div class="bg-red-100 text-red-700 px-4 py-3 rounded-xl mb-6">Error updating settings.</div>';
    }
}
?>

<div class="max-w-4xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight">Business Settings</h1>
        <p class="text-gray-500 mt-2">Manage your business profile and invoice branding.</p>
    </div>

    <?= $message ?>

    <form method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Business Profile
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Admin / Business Name</label>
                            <input type="text" name="admin_name" value="<?= htmlspecialchars($site_settings['admin_name']) ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 transition-all" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Logo URL</label>
                            <input type="url" name="logo_url" value="<?= htmlspecialchars($site_settings['logo_url']) ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 transition-all" placeholder="https://example.com/logo.png">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Currency Symbol</label>
                            <input type="text" name="currency" value="<?= htmlspecialchars($site_settings['currency']) ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($site_settings['email']) ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($site_settings['phone']) ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Business Address</label>
                    <textarea name="business_address" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 transition-all resize-none"><?= htmlspecialchars($site_settings['business_address']) ?></textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-10 py-4 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 shadow-xl shadow-blue-100 transition-all">
                    Save Changes
                </button>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-4">Preview Branding</h3>
                <div class="p-6 bg-gray-50 rounded-xl flex flex-col items-center text-center">
                    <?php if(!empty($site_settings['logo_url'])): ?>
                    <img src="<?= htmlspecialchars($site_settings['logo_url']) ?>" alt="Preview" class="max-h-20 mb-4">
                    <?php else: ?>
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-bold text-2xl mb-4">?</div>
                    <?php endif; ?>
                    <p class="font-bold text-lg"><?= htmlspecialchars($site_settings['admin_name']) ?></p>
                    <p class="text-xs text-gray-500 mt-1">Invoice Preview Style</p>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>
