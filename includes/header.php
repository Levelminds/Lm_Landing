<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LevelMinds | Empowering Modern Learning Communities</title>
    <meta name="description" content="LevelMinds connects educators and schools with tools, insights, and a vibrant community designed to accelerate learning outcomes.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --lm-primary: #0A67E6;
            --lm-primary-light: #4B8FF7;
            --lm-dark: #0B1D3C;
            --lm-text: #25334D;
            --lm-muted: #6F7B90;
            --lm-bg: #ffffff;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--lm-text);
            background-color: var(--lm-bg);
        }

        .text-primary {
            color: var(--lm-primary) !important;
        }

        .btn-gradient {
            background: linear-gradient(135deg, var(--lm-primary) 0%, var(--lm-primary-light) 100%);
            color: #fff;
            border: none;
            box-shadow: 0 10px 25px rgba(10, 103, 230, 0.3);
        }

        .btn-gradient:hover,
        .btn-gradient:focus {
            color: #fff;
            box-shadow: 0 12px 30px rgba(10, 103, 230, 0.35);
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            border-color: rgba(10, 103, 230, 0.2);
            color: var(--lm-primary);
            background: rgba(10, 103, 230, 0.06);
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            border-color: var(--lm-primary);
            color: #fff;
            background: var(--lm-primary);
        }

        .btn-gradient.btn-light {
            background: rgba(255, 255, 255, 0.85);
            color: var(--lm-primary);
        }

        .btn-gradient.btn-light:hover {
            background: #fff;
            color: var(--lm-primary);
        }

        .navbar {
            box-shadow: 0 8px 24px rgba(15, 39, 80, 0.05);
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--lm-dark) !important;
        }

        .navbar-nav .nav-link {
            color: var(--lm-muted);
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link:focus,
        .navbar-nav .nav-link.active {
            color: var(--lm-primary);
        }

        .hero-section {
            padding: 6rem 0 5rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 999px;
            background: rgba(10, 103, 230, 0.08);
            color: var(--lm-primary);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .hero-headline {
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            font-weight: 700;
            line-height: 1.1;
            color: var(--lm-dark);
        }

        .hero-subtext {
            font-size: 1.1rem;
            color: var(--lm-muted);
            max-width: 520px;
        }

        .hero-graphic {
            border-radius: 30px;
            box-shadow: 0 30px 70px rgba(10, 103, 230, 0.15);
            overflow: hidden;
        }

        .section-title {
            font-weight: 700;
            color: var(--lm-dark);
            margin-bottom: 1rem;
        }

        .section-subtitle {
            color: var(--lm-muted);
            max-width: 640px;
        }

        .section-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--lm-primary);
            background: rgba(10, 103, 230, 0.08);
        }

        .card-soft {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 45px rgba(10, 103, 230, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-soft:hover {
            transform: translateY(-6px);
            box-shadow: 0 25px 60px rgba(10, 103, 230, 0.12);
        }

        .highlight-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.7rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            background: rgba(10, 103, 230, 0.1);
            color: var(--lm-primary);
        }

        .step-card {
            border: none;
            border-radius: 18px;
            padding: 2rem;
            background: #fff;
            box-shadow: 0 20px 45px rgba(10, 103, 230, 0.08);
            height: 100%;
        }

        .step-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: rgba(10, 103, 230, 0.1);
            color: var(--lm-primary);
            display: grid;
            place-items: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .educator-card {
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 25px 55px rgba(10, 103, 230, 0.08);
            border: none;
            overflow: hidden;
            height: 100%;
        }

        .educator-card img {
            height: 280px;
            object-fit: cover;
        }

        .avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
        }

        .testimonial-card {
            border-radius: 20px;
            border: none;
            background: #fff;
            box-shadow: 0 18px 45px rgba(10, 103, 230, 0.08);
            padding: 2.5rem;
            height: 100%;
        }

        .quote-icon {
            color: var(--lm-primary);
            font-size: 1.75rem;
        }

        .mission-section {
            background: linear-gradient(135deg, rgba(10, 103, 230, 0.08) 0%, rgba(75, 143, 247, 0.18) 100%);
            border-radius: 28px;
            padding: 3rem;
        }

        .final-cta {
            border-radius: 24px;
            background: #0B1D3C;
            color: #fff;
            padding: 3rem;
            box-shadow: 0 30px 65px rgba(11, 29, 60, 0.35);
        }

        footer {
            background-color: #0B1D3C;
            color: rgba(255, 255, 255, 0.85);
        }

        footer a {
            color: rgba(255, 255, 255, 0.75);
        }

        footer a:hover {
            color: #ffffff;
        }

        @media (max-width: 991px) {
            .hero-graphic {
                margin-top: 2rem;
            }
        }
    </style>
</head>
<body class="homepage">
    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <img src="assets/images/logo/logo.svg" alt="LevelMinds" class="me-2" style="height: 36px;">
                LevelMinds
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="solutions.html">Solutions</a></li>
                    <li class="nav-item"><a class="nav-link" href="tour.html">Tour</a></li>
                    <li class="nav-item"><a class="nav-link" href="team.html">Team</a></li>
                    <li class="nav-item"><a class="nav-link" href="pricing.html">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.html">Contact</a></li>
                    <!-- <li class="nav-item"><a class="nav-link" href="blogs.php">Blogs</a></li> -->
                </ul>
                <a class="btn btn-gradient ms-lg-3 mt-3 mt-lg-0 px-4" href="https://www.staging.levelminds.in" target="_blank" rel="noopener">Login / Sign Up</a>
            </div>
        </div>
    </nav>
    <main>
