<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrganizaAI - Gestão de Contratos e Assinaturas</title>
    
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/bootstrap.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/custom.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Auth JS (Token Manager) -->
    <script src="<?php echo $base; ?>/assets/js/auth.js"></script>
</head>
<body>
    <header class="bg-gradient">
        <div class="container">
            <h1 class="mb-0">📋 OrganizaAI</h1>
            <p class="mb-0 mt-2 opacity-75">Gestão de Contratos e Assinaturas</p>
        </div>
    </header>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $base; ?>/">
                <strong>OrganizaAI</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'active' : ''; ?>" href="<?php echo $base; ?>/dashboard">
                            <i class="fas fa-chart-line"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'clientes') !== false ? 'active' : ''; ?>" href="<?php echo $base; ?>/clientes">
                            <i class="fas fa-users"></i> Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'sistemas') !== false ? 'active' : ''; ?>" href="<?php echo $base; ?>/sistemas">
                            <i class="fas fa-cogs"></i> Sistemas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'assinaturas') !== false ? 'active' : ''; ?>" href="<?php echo $base; ?>/assinaturas">
                            <i class="fas fa-file-contract"></i> Assinaturas
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> Perfil
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo $base; ?>/usuarios"><i class="fas fa-users-cog"></i> Usuários</a></li>
                            <li><a class="dropdown-item" href="<?php echo $base; ?>/empresa"><i class="fas fa-building"></i> Empresa</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $base; ?>/sair"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">
