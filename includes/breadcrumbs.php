<?php
if (!defined('ROOT_URL')) {
    require_once __DIR__ . '/../config/constants.php';
}

function getBreadcrumbs() {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $paths = array_filter(explode('/', str_replace('/Blog-Dambwe/', '', $path)));
    
    $breadcrumbs = [];
    $currentPath = '';
    
    // Add home
    $breadcrumbs[] = [
        'title' => 'Home',
        'url' => ROOT_URL
    ];
    
    foreach ($paths as $segment) {
        $currentPath .= '/' . $segment;
        
        // Clean up segment for display
        $title = ucwords(str_replace(['-', '.php'], [' ', ''], $segment));
        
        // Skip admin folder in title but keep in path
        if ($segment === 'admin') {
            continue;
        }
        
        $breadcrumbs[] = [
            'title' => $title,
            'url' => ROOT_URL . ltrim($currentPath, '/')
        ];
    }
    
    return $breadcrumbs;
}
?>

<div class="breadcrumbs">
    <div class="container">
        <?php
        $breadcrumbs = getBreadcrumbs();
        foreach ($breadcrumbs as $index => $crumb) {
            if ($index > 0) {
                echo '<i class="fas fa-chevron-right"></i>';
            }
            if ($index === array_key_last($breadcrumbs)) {
                echo '<span>' . htmlspecialchars($crumb['title']) . '</span>';
            } else {
                echo '<a href="' . htmlspecialchars($crumb['url']) . '">' . htmlspecialchars($crumb['title']) . '</a>';
            }
        }
        ?>
    </div>
</div>
