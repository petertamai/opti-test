<header class="bg-gray-800 shadow-md">
    <div class="container mx-auto px-4 py-4">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <!-- Logo and Site Title -->
            <div class="flex items-center mb-4 md:mb-0">
                <a href="<?= getenv('APP_BASE_PATH') ?>/" class="flex items-center">
                    <span class="text-button text-2xl font-bold mr-2">Img</span>
                    <span class="text-white text-2xl font-bold">Tasks</span>
                </a>
                <span class="ml-4 text-sm text-gray-400">Image Processing Simplified</span>
            </div>
            
            <!-- Mode Navigation -->
            <nav class="w-full md:w-auto">
                <ul class="flex flex-wrap justify-center space-x-1 md:space-x-2">
                    <?php 
                    $currentMode = $sessionManager->get('current_mode', 'basic');
                    $modes = [
                        'basic' => 'Basic Mode',
                        'simple' => 'Simple Optimization',
                        'advanced' => 'Advanced Pipeline'
                    ];
                    ?>
                    
                    <?php foreach ($modes as $mode => $label): ?>
                        <li>
                            <a href="<?= getenv('APP_BASE_PATH') ?>/?mode=<?= $mode ?>" 
                               class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200
                                     <?= $currentMode === $mode 
                                        ? 'bg-button text-white shadow-lg' 
                                        : 'bg-gray-700 text-gray-300 hover:bg-gray-600' ?>">
                                <?= $label ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </div>
</header>