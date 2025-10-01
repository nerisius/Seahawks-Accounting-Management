<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    
    <!-- Preload logo with fallback -->
    <link rel="preload" href="images/ss_logo_png.png" as="image" importance="high">
    
    <!-- Critical CSS to hide content initially -->
    <style>
        body { visibility: hidden; opacity: 0; }
        body.loaded { visibility: visible; opacity: 1; }
        
        /* Navigation active state - added to critical CSS */
        nav ul li a.active {
            background-color:rgb(32, 14, 123);
            color: white;
            font-weight: italic;
            border-radius: 3px;
            box-shadow: 4px 4px 8px rgba(0,0,0,0.1);
        }
    </style>
    
    <!-- Main CSS with load handler -->
    <link rel="stylesheet" href="assets/style.css" onload="document.body.classList.add('loaded')">
    <noscript><link rel="stylesheet" href="assets/style.css"></noscript>
    
    <!-- Preload other important resources if needed -->
</head>
<body>
    <header>
        <img src="images/ss_logo_png.png" alt="Seahawks Club Logo" class="club-logo">
        <h1><?php echo SITE_NAME; ?></h1>
        <nav>
            <ul>
                <?php
                $current_page = basename($_SERVER['PHP_SELF']);
                $nav_items = [
                    'index.php' => 'Dashboard',
                    'expenses.php' => 'Expenses',
                    'incomes.php' => 'Income',
                    'obligors.php' => 'Obligors'
                ];
                
                foreach ($nav_items as $page => $title) {
                    $active_class = ($current_page == $page) ? 'active' : '';
                    echo '<li><a href="' . SITE_URL . '/' . $page . '" class="' . $active_class . '">' . $title . '</a></li>';
                }
                ?>
            </ul>
        </nav>
    </header>
    <main>