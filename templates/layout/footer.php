<footer class="bg-gray-800 py-6 mt-8 border-t border-gray-700">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <!-- Copyright -->
            <div class="mb-4 md:mb-0 text-gray-400 text-sm">
                &copy; <?= date('Y') ?> <a href="https://petertam.pro/" target="_blank" class="text-button hover:text-blue-400 transition-colors duration-200">Piotr Tamulewicz</a>. All rights reserved.
            </div>
            
            <!-- Footer Links -->
            <div class="flex flex-wrap justify-center gap-6">
                <a href="<?= getenv('APP_BASE_PATH') ?>/?mode=basic" class="text-gray-400 hover:text-white text-sm transition-colors duration-200">
                    Basic Mode
                </a>
                <a href="<?= getenv('APP_BASE_PATH') ?>/?mode=simple" class="text-gray-400 hover:text-white text-sm transition-colors duration-200">
                    Simple Optimization
                </a>
                <a href="<?= getenv('APP_BASE_PATH') ?>/?mode=advanced" class="text-gray-400 hover:text-white text-sm transition-colors duration-200">
                    Advanced Pipeline
                </a>
            </div>
        </div>
    </div>
</footer>