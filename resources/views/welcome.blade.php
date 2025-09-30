<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Inventory System') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .feature-card {
            transition: transform 0.3s ease-in-out;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .stats-card {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border: none;
            border-radius: 15px;
        }
        
        .btn-primary-custom {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .section-title {
            position: relative;
            margin-bottom: 3rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 2px;
        }
        
        .policy-item {
            padding: 1.5rem;
            border-left: 4px solid #667eea;
            background: #f8f9fa;
            margin-bottom: 1rem;
            border-radius: 0 8px 8px 0;
        }
            </style>
    </head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ url('/') }}">
                <i class="bi bi-boxes text-primary me-2" style="font-size: 1.5rem;"></i>
                <span>Inventory Pro</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a href="{{ url('/dashboard') }}" class="btn btn-outline-primary me-2">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin" class="btn btn-primary-custom" target="_blank">
                                <i class="bi bi-gear"></i> Admin Panel
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a href="{{ route('register') }}" class="btn btn-primary-custom">
                                    <i class="bi bi-person-plus"></i> Get Started
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
            </div>
        </div>
                </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Professional Inventory Management System</h1>
                    <p class="lead mb-4">Streamline your inventory operations with our comprehensive web-based solution. Track stock levels, manage purchase requests, and optimize your supply chain with ease.</p>
                    
                    <!-- Live Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="stats-card card p-3 text-center">
                                <h3 class="text-primary fw-bold mb-1">{{ $stats['total_items'] ?? 0 }}</h3>
                                <small class="text-muted">Total Items</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stats-card card p-3 text-center">
                                <h3 class="text-primary fw-bold mb-1">{{ $stats['total_requests'] ?? 0 }}</h3>
                                <small class="text-muted">Purchase Requests</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stats-card card p-3 text-center">
                                <h3 class="text-primary fw-bold mb-1">{{ $stats['total_users'] ?? 0 }}</h3>
                                <small class="text-muted">Active Users</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex flex-wrap gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-light btn-lg">
                                <i class="bi bi-speedometer2"></i> Go to Dashboard
                            </a>
                            <a href="/admin" class="btn btn-outline-light btn-lg" target="_blank">
                                <i class="bi bi-gear"></i> Admin Panel
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-light btn-lg">
                                <i class="bi bi-person-plus"></i> Get Started
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Sign In
                            </a>
                        @endauth
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="bi bi-boxes" style="font-size: 15rem; opacity: 0.8;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">What Our System Does</h2>
                <p class="lead text-muted">A complete solution for managing your organization's inventory and purchase workflows</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card card text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-box text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="fw-bold">Inventory Tracking</h5>
                        <p class="text-muted">Real-time tracking of stock levels, automatic reorder alerts, and comprehensive item management.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card card text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-cart-plus text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="fw-bold">Purchase Requests</h5>
                        <p class="text-muted">Streamlined approval workflow from employee request to stock keeper fulfillment.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card card text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-people text-info" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="fw-bold">Role-Based Access</h5>
                        <p class="text-muted">Secure access control with different permissions for employees, approvers, and administrators.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card card text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-graph-up text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="fw-bold">Smart Analytics</h5>
                        <p class="text-muted">Complete audit trails, reporting, and insights to optimize your inventory operations.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Policies Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">System Policies & Guidelines</h2>
                <p class="lead text-muted">Important policies governing the use of our inventory management system</p>
            </div>
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="policy-item">
                        <h5 class="fw-bold mb-2"><i class="bi bi-shield-check text-primary me-2"></i>Access Control Policy</h5>
                        <p class="mb-0">All users must register with a valid email address and receive appropriate role assignments. Access is granted based on job responsibilities and organizational hierarchy.</p>
                    </div>
                    
                    <div class="policy-item">
                        <h5 class="fw-bold mb-2"><i class="bi bi-clipboard-check text-success me-2"></i>Purchase Request Policy</h5>
                        <p class="mb-0">All purchase requests must include proper justification and be submitted through the system. Approval is required before any items can be fulfilled from inventory.</p>
                    </div>
                    
                    <div class="policy-item">
                        <h5 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Stock Management Policy</h5>
                        <p class="mb-0">Stock levels must be maintained above reorder points. All stock movements are tracked and audited. Only authorized stock keepers can fulfill requests and adjust inventory.</p>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="policy-item">
                        <h5 class="fw-bold mb-2"><i class="bi bi-person-badge text-info me-2"></i>User Responsibility Policy</h5>
                        <p class="mb-0">Users are responsible for the accuracy of their requests and proper use of system resources. Misuse or fraudulent activity will result in access termination.</p>
                    </div>
                    
                    <div class="policy-item">
                        <h5 class="fw-bold mb-2"><i class="bi bi-clock-history text-secondary me-2"></i>Data Retention Policy</h5>
                        <p class="mb-0">All transactions, requests, and system activities are logged and retained for audit purposes. Historical data helps maintain accountability and compliance.</p>
                    </div>
                    
                    <div class="policy-item">
                        <h5 class="fw-bold mb-2"><i class="bi bi-envelope-check text-primary me-2"></i>Email Verification Policy</h5>
                        <p class="mb-0">While email verification is recommended for security notifications, users can access the system immediately. Verify your email to receive important updates and alerts.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- User Roles Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">User Roles & Permissions</h2>
                <p class="lead text-muted">Understanding different access levels within the system</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card card p-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-person text-primary" style="font-size: 2.5rem;"></i>
                        </div>
                        <h6 class="fw-bold">Employee</h6>
                        <small class="text-muted">Create and track purchase requests, view inventory items</small>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card card p-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-person-check text-success" style="font-size: 2.5rem;"></i>
                        </div>
                        <h6 class="fw-bold">Approver</h6>
                        <small class="text-muted">Review and approve/reject purchase requests from employees</small>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card card p-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-person-gear text-info" style="font-size: 2.5rem;"></i>
                        </div>
                        <h6 class="fw-bold">Stock Keeper</h6>
                        <small class="text-muted">Fulfill approved requests, manage stock movements and inventory</small>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card card p-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-person-fill-gear text-warning" style="font-size: 2.5rem;"></i>
                        </div>
                        <h6 class="fw-bold">Administrator</h6>
                        <small class="text-muted">Full system access, user management, and system configuration</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-boxes me-2" style="font-size: 1.5rem;"></i>
                        <h5 class="fw-bold mb-0">Inventory Pro</h5>
                    </div>
                    <p class="text-muted">Professional inventory management system built with Laravel, Bootstrap, and modern web technologies.</p>
                </div>
                
                <div class="col-md-6 text-md-end">
                    <h6 class="fw-bold mb-3">Quick Access</h6>
                    <div class="d-flex justify-content-md-end flex-wrap gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-white-50 text-decoration-none">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                            <a href="/admin" class="text-white-50 text-decoration-none" target="_blank">
                                <i class="bi bi-gear"></i> Admin Panel
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-white-50 text-decoration-none">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                            <a href="{{ route('register') }}" class="text-white-50 text-decoration-none">
                                <i class="bi bi-person-plus"></i> Register
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
            
            <hr class="my-3 opacity-25">
            
            <div class="row">
                <div class="col-12 text-center">
                    <p class="text-muted mb-0">
                        © {{ date('Y') }} Inventory Pro. Built with <i class="bi bi-heart-fill text-danger"></i> using Laravel {{ app()->version() }} & Bootstrap 5
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.borderBottom = '1px solid rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.borderBottom = '1px solid rgba(255, 255, 255, 0.1)';
            }
        });
    </script>
    </body>
</html>