<?php
// index.php - Landing page describing the website and services
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>SmartFix - Digital Services</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">SmartFix</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="bg-light py-5">
        <div class="container text-center">
            <h1 class="display-4">Welcome to SmartFix</h1>
            <p class="lead">Your one-stop solution for professional digital services</p>
        </div>
    </header>

    <main class="container my-5">
        <h2 class="mb-4">Our Services</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-file-alt me-2"></i>CV Editing</h5>
                        <p class="card-text">Professional CV and resume editing to help you stand out to employers.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-book me-2"></i>School Projects & Research</h5>
                        <p class="card-text">Assistance with school projects, research papers, and academic writing.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-passport me-2"></i>Passport Editing</h5>
                        <p class="card-text">Editing and formatting passport documents for official use.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-file-contract me-2"></i>Document Proofreading</h5>
                        <p class="card-text">Proofreading and editing of various documents to ensure accuracy and clarity.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-globe me-2"></i>Translation Services</h5>
                        <p class="card-text">Professional translation services for multiple languages.</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-laptop-code me-2"></i>Digital Content Creation</h5>
                        <p class="card-text">Creation of digital content including presentations, reports, and marketing materials.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-primary text-white text-center py-3">
        &copy; <?= date('Y') ?> SmartFix. All rights reserved.
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
