<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Sistema de Compras'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo isset($css_path) ? $css_path : '../'; ?>styles.css">
    <?php if (isset($extra_css)): ?>
        <style><?php echo $extra_css; ?></style>
    <?php endif; ?>
</head>
<body>

    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title"><?php echo isset($page_heading) ? $page_heading : 'Panel de Gestión'; ?></div>
    </header>

    <div class="main-container">