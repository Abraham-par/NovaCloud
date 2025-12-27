<?php
// Ensure SITE_URL is available for absolute links
require_once __DIR__ . '/config.php';
// Define a default for layout type if not set elsewhere.
// Assume it's a minimal layout unless specified as a 'marketing' page.
$layoutType = $layoutType ?? 'dashboard';
?>

    </div> <footer class="mt-8 py-6 border-t border-gray-200 bg-white">
        
        <?php if ($layoutType === 'dashboard'): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center text-sm text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> **NovaCloud**. All rights reserved.</p>
            <div class="space-x-4">
                <a href="<?php echo SITE_URL; ?>contact.php" class="hover:text-indigo-600 transition-colors duration-200">Support</a>
                <a href="<?php echo SITE_URL; ?>privacy.php" class="hover:text-indigo-600 transition-colors duration-200">Privacy</a>
                <a href="<?php echo SITE_URL; ?>about.php" class="hover:text-indigo-600 transition-colors duration-200">About</a>
            </div>
        </div>

        <?php else: ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">Product</h3>
                    <ul role="list" class="mt-4 space-y-4">
                        <li><a href="#" class="text-base text-gray-500 hover:text-indigo-600">Features</a></li>
                        <li><a href="#" class="text-base text-gray-500 hover:text-indigo-600">Pricing</a></li>
                        <li><a href="#" class="text-base text-gray-500 hover:text-indigo-600">Integrations</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">Company</h3>
                    <ul role="list" class="mt-4 space-y-4">
                        <li><a href="<?php echo SITE_URL; ?>about.php" class="text-base text-gray-500 hover:text-indigo-600">About Us</a></li>
                        <li><a href="#" class="text-base text-gray-500 hover:text-indigo-600">Careers</a></li>
                        <li><a href="#" class="text-base text-gray-500 hover:text-indigo-600">Blog</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">Legal</h3>
                    <ul role="list" class="mt-4 space-y-4">
                        <li><a href="<?php echo SITE_URL; ?>privacy.php" class="text-base text-gray-500 hover:text-indigo-600">Privacy Policy</a></li>
                        <li><a href="<?php echo SITE_URL; ?>terms.php" class="text-base text-gray-500 hover:text-indigo-600">Terms of Service</a></li>
                        <li><a href="<?php echo SITE_URL; ?>privacy.php#cookies" class="text-base text-gray-500 hover:text-indigo-600">Cookie Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">Contact</h3>
                    <p class="mt-4 text-base text-gray-500">Email: support@novacloud.com</p>
                    <div class="flex space-x-4 mt-4">
                         <a href="#" class="text-gray-400 hover:text-indigo-600"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-indigo-600"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="mt-10 pt-8 border-t border-gray-200">
                <p class="text-base text-gray-400 text-center">&copy; <?php echo date('Y'); ?> **NovaCloud**. All rights reserved.</p>
            </div>
        </div>
        <?php endif; ?>

    </footer>

    <script src="assets/js/language-switcher.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo SITE_URL; ?>sw.js', { scope: '<?php echo SITE_URL; ?>' })
                    .then(function(reg) {
                        console.log('ServiceWorker registered with scope:', reg.scope);
                    })
                    .catch(function(err) {
                        console.warn('ServiceWorker registration failed:', err);
                    });
            });
        }
    </script>

    </body>
</html>