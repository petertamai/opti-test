<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ImgTasks - <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Image Processing Tool' ?></title>
    
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="<?= getenv('APP_BASE_PATH') ?>/css/style.css">
    
    <!-- Nunito Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700&display=swap">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= getenv('APP_BASE_PATH') ?>/assets/images/favicon.png">
    
    <!-- Additional CSS (if any) -->
    <?php if (isset($additionalCss)): ?>
        <?= $additionalCss ?>
    <?php endif; ?>
    
    <!-- jQuery -->
    <script src="<?= getenv('APP_BASE_PATH') ?>/js/vendor/jquery.min.js"></script>
</head>
<body class="min-h-screen bg-background text-gray-100 font-sans flex flex-col">
    <!-- Header -->
    <?php include dirname(__FILE__) . '/header.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-8">
        <?php if (isset($contentTemplate) && file_exists(dirname(__DIR__) . '/' . $contentTemplate)): ?>
            <?php include dirname(__DIR__) . '/' . $contentTemplate; ?>
        <?php else: ?>
            <div class="bg-red-600 text-white p-4 rounded-lg shadow-md">
                <h2 class="text-xl font-bold">Error</h2>
                <p>Template not found: <?= htmlspecialchars($contentTemplate ?? 'No template specified') ?></p>
            </div>
        <?php endif; ?>
    </main>
    
    <!-- Footer -->
    <?php include dirname(__FILE__) . '/footer.php'; ?>
    
    <!-- SweetAlert2 -->
    <script src="<?= getenv('APP_BASE_PATH') ?>/js/vendor/sweetalert2.all.min.js"></script>
    
    <!-- App JS -->
    <script src="<?= getenv('APP_BASE_PATH') ?>/js/app.js"></script>
    
    <!-- Additional JS (if any) -->
    <?php if (isset($additionalJs)): ?>
        <?= $additionalJs ?>
    <?php endif; ?>
    
    <!-- Flash Messages -->
    <?php
    if (isset($sessionManager) && $sessionManager->hasFlash('success')): 
        $successMessage = $sessionManager->getFlash('success');
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?= htmlspecialchars($successMessage) ?>',
                timer: 3000,
                timerProgressBar: true
            });
        });
    </script>
    <?php endif; ?>
    
    <?php
    if (isset($sessionManager) && $sessionManager->hasFlash('error')): 
        $errorMessage = $sessionManager->getFlash('error');
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= htmlspecialchars($errorMessage) ?>',
                timer: 5000,
                timerProgressBar: true
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>