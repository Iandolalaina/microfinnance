<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MITSINJO accompagne les communautés avec des solutions de microfinance accessibles et un suivi transparent.">
    <title>MITSINJO — La finance au service de vos projets</title>
    @include('partials.pwa')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Lora:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --mitsinjo-blue: #0259a0; --mitsinjo-green: #174c3b; --mitsinjo-gold: #e7ae38; --mitsinjo-ink: #182b35; --mitsinjo-paper: #f5f8f4; }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; color: var(--mitsinjo-ink); font-family: 'DM Sans', sans-serif; }
        a { color: inherit; text-decoration: none; }
        .site-shell { overflow: hidden; }
        .site-header { position: relative; z-index: 2; background: #fff; border-bottom: 1px solid #e7ede8; }
        .site-nav { width: min(1160px, calc(100% - 48px)); min-height: 82px; margin: auto; display: flex; align-items: center; justify-content: space-between; gap: 28px; }
        .brand { display: inline-flex; align-items: center; gap: 12px; flex: 0 0 auto; }
        .brand img { width: 48px; height: 48px; object-fit: contain; }
        .brand-name { color: var(--mitsinjo-blue); font-size: 18px; font-weight: 700; letter-spacing: .04em; }
        .brand-caption { display: block; margin-top: 2px; color: #72817a; font-size: 10px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
        .nav-links { display: flex; align-items: center; gap: 28px; color: #4f615a; font-size: 13px; font-weight: 600; }
        .nav-links a:hover, .text-link:hover { color: var(--mitsinjo-blue); }
        .services-menu { position: relative; }
        .services-menu summary { cursor: pointer; list-style: none; }
        .services-menu summary::-webkit-details-marker { display: none; }
        .services-menu summary::after { margin-left: 5px; content: '▾'; }
        .services-dropdown { position: absolute; top: calc(100% + 12px); left: -16px; z-index: 5; min-width: 170px; padding: 7px; border: 1px solid #e7ede8; border-radius: 6px; background: #fff; box-shadow: 0 12px 30px rgb(24 43 53 / 12%); }
        .services-dropdown a { display: block; padding: 10px 12px; border-radius: 4px; }
        .services-dropdown a:hover { background: #edf3ef; }
        .mobile-services { display: none; }
        .nav-cta, .button-primary, .button-outline { display: inline-flex; align-items: center; justify-content: center; min-height: 46px; padding: 0 20px; border-radius: 4px; font-size: 13px; font-weight: 700; transition: transform .2s ease, background .2s ease; }
        .nav-cta, .button-primary { color: #fff; background: var(--mitsinjo-blue); }
        .nav-cta:hover, .button-primary:hover { background: #034b82; transform: translateY(-2px); }
        .eyebrow { display: inline-flex; align-items: center; gap: 9px; color: var(--mitsinjo-green); font-size: 11px; font-weight: 700; letter-spacing: .13em; text-transform: uppercase; }
        .eyebrow::before { width: 24px; height: 2px; background: var(--mitsinjo-gold); content: ''; }
        h1, h2, h3, p { margin-top: 0; }
        .hero { width: 100%; }
        .hero-carousel { position: relative; width: 100%; height: clamp(360px, 62vw, 650px); overflow: hidden; background: #e7ede8; }
        .carousel-slide { position: absolute; inset: 0; }
        .carousel-slide[hidden] { display: none; }
        .carousel-slide img { width: 100%; height: 100%; object-fit: cover; }
        .carousel-placeholder { display: flex; width: 100%; height: 100%; align-items: center; justify-content: center; padding: 24px; color: #4f615a; background: linear-gradient(135deg, #edf3ef, #dce7df); text-align: center; }
        .carousel-placeholder code { display: block; margin-top: 10px; color: var(--mitsinjo-blue); font-size: 14px; }
        .carousel-controls { position: absolute; right: 20px; bottom: 20px; left: 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .carousel-arrow, .carousel-toggle, .carousel-indicator { display: inline-flex; min-width: 44px; min-height: 44px; align-items: center; justify-content: center; border: 1px solid rgb(255 255 255 / 70%); border-radius: 4px; color: #fff; background: rgb(24 43 53 / 68%); cursor: pointer; }
        .carousel-arrow { font-size: 24px; }
        .carousel-navigation { display: flex; align-items: center; justify-content: center; gap: 8px; }
        .carousel-indicator { min-width: 34px; min-height: 34px; border-radius: 50%; font-size: 12px; }
        .carousel-indicator[aria-current="true"] { color: var(--mitsinjo-ink); background: #fff; }
        .carousel-toggle { padding: 0 12px; font-size: 12px; font-weight: 600; }
        .carousel-arrow:hover, .carousel-toggle:hover, .carousel-indicator:hover { background: var(--mitsinjo-blue); }
        .carousel-arrow:focus-visible, .carousel-toggle:focus-visible, .carousel-indicator:focus-visible { outline: 3px solid #f4c65c; outline-offset: 3px; }
        .intro { width: min(1160px, calc(100% - 48px)); margin: auto; padding: 74px 0 66px; display: grid; grid-template-columns: .72fr 1fr; gap: 80px; align-items: start; }
        .intro h2, .impact-copy h2 { margin: 13px 0 0; color: var(--mitsinjo-green); font-family: 'Lora', serif; font-size: 34px; line-height: 1.2; }
        .intro p { margin: 0; color: #63716c; font-size: 15px; line-height: 1.9; }
        .impact { color: #fff; background: var(--mitsinjo-green); }
        .impact-inner { width: min(1160px, calc(100% - 48px)); min-height: 250px; margin: auto; display: flex; align-items: center; justify-content: space-between; gap: 45px; padding: 52px 0; }
        .impact-copy { max-width: 580px; }
        .impact .eyebrow { color: #c8dfd2; }
        .impact-copy h2 { color: #fff; }
        .impact-copy p { margin: 15px 0 0; color: #d2e0d8; font-size: 14px; line-height: 1.8; }
        .button-outline { flex: 0 0 auto; border: 1px solid #a9c7b6; color: #fff; }
        .button-outline:hover { color: var(--mitsinjo-green); background: #fff; }
        .site-footer { background: #fff; }
        .footer-inner { width: min(1160px, calc(100% - 48px)); min-height: 96px; margin: auto; display: flex; align-items: center; justify-content: space-between; gap: 20px; color: #72817a; font-size: 12px; }
        .footer-inner p { margin: 0; }
        .footer-links { display: flex; gap: 22px; }
        .reveal { animation: rise-in .65s both; }
        @keyframes rise-in { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 800px) {
            .site-nav { width: min(100% - 36px, 620px); min-height: 72px; gap: 12px; }
            .brand img { width: 42px; height: 42px; }
            .brand-name { font-size: 16px; }
            .nav-links { display: none; }
            .mobile-services { display: block; padding: 0 18px 12px; color: #4f615a; font-size: 13px; font-weight: 600; }
            .mobile-services summary { cursor: pointer; list-style: none; }
            .mobile-services summary::-webkit-details-marker { display: none; }
            .mobile-services summary::after { margin-left: 5px; content: '▾'; }
            .mobile-services .services-dropdown { position: static; display: flex; gap: 8px; margin-top: 8px; padding: 0; border: 0; box-shadow: none; }
            .mobile-services .services-dropdown a { padding: 8px 12px; background: #edf3ef; }
            .nav-cta { min-height: 40px; padding: 0 14px; font-size: 12px; }
            .hero-carousel { height: 62vh; min-height: 300px; max-height: 520px; }
            .carousel-controls { right: 12px; bottom: 12px; left: 12px; gap: 8px; }
            .carousel-arrow, .carousel-toggle { min-width: 40px; min-height: 40px; }
            .carousel-navigation { gap: 5px; }
            .carousel-indicator { min-width: 32px; min-height: 32px; }
            .intro { width: min(100% - 36px, 620px); grid-template-columns: 1fr; gap: 22px; padding: 56px 0; }
            .intro h2, .impact-copy h2 { font-size: 29px; }
            .impact-inner, .footer-inner { width: min(100% - 36px, 620px); }
            .impact-inner { min-height: 0; align-items: start; flex-direction: column; gap: 24px; padding: 48px 0; }
            .footer-inner { min-height: 100px; align-items: start; flex-direction: column; justify-content: center; gap: 11px; }
        }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; } }
    </style>
</head>
<body>
@php
    $dashboardUrl = auth()->check()
        ? match (auth()->user()->role) {
            'admin' => url('/admin/dashboard'),
            'agent' => url('/agent/dashboard'),
            default => url('/client/dashboard'),
        }
        : route('login');
@endphp
<div class="site-shell">
    <header class="site-header">
        <nav class="site-nav" aria-label="Navigation principale">
            <a class="brand" href="{{ route('home') }}" aria-label="MITSINJO, accueil">
                <img src="{{ asset('icons/icon-192.png') }}" alt="">
                <span><span class="brand-name">MITSINJO</span><span class="brand-caption">Finance solidaire</span></span>
            </a>
            <div class="nav-links">
                <a href="#accueil">Accueil</a>
                <details class="services-menu">
                    <summary>Nos services</summary>
                    <div class="services-dropdown">
                        <a href="{{ route('savings.index') }}">Épargne</a>
                        <a href="{{ route('credits.index') }}">Crédit</a>
                    </div>
                </details>
                <a href="#engagement">Notre engagement</a>
                <a href="{{ route('register') }}">Devenir membre</a>
            </div>
            <a class="nav-cta" href="{{ $dashboardUrl }}">{{ auth()->check() ? 'Mon espace' : 'Se connecter' }}</a>
        </nav>
        <details class="mobile-services">
            <summary>Nos services</summary>
            <div class="services-dropdown">
                <a href="{{ route('savings.index') }}">Épargne</a>
                <a href="{{ route('credits.index') }}">Crédit</a>
            </div>
        </details>
    </header>

    <main>
        <section class="hero" id="accueil">
            @php
                $homeSlides = [
                    ['path' => 'images/accueil/accueil-1.jpg', 'alt' => 'Membres de la communauté réunis autour de leurs projets'],
                    ['path' => 'images/accueil/accueil-2.jpg', 'alt' => 'Activité locale soutenue par la microfinance'],
                    ['path' => 'images/accueil/accueil-3.jpg', 'alt' => 'Agent accompagnant un membre dans ses démarches'],
                ];
            @endphp
            <div class="hero-carousel" data-carousel role="region" aria-roledescription="carrousel" aria-label="Photos de MITSINJO">
                @foreach ($homeSlides as $index => $slide)
                    <div class="carousel-slide" data-carousel-slide role="group" aria-roledescription="diapositive" aria-label="{{ $index + 1 }} sur {{ count($homeSlides) }}" @if ($index > 0) hidden @endif>
                        @if (file_exists(public_path($slide['path'])))
                            <img src="{{ asset($slide['path']) }}" alt="{{ $slide['alt'] }}" @if ($index === 0) fetchpriority="high" @endif>
                        @else
                            <div class="carousel-placeholder">
                                <p>Photo à ajouter<code>public/{{ $slide['path'] }}</code></p>
                            </div>
                        @endif
                    </div>
                @endforeach
                <div class="carousel-controls">
                    <button class="carousel-arrow" type="button" data-carousel-previous aria-label="Photo précédente">‹</button>
                    <div class="carousel-navigation" aria-label="Choisir une photo">
                        @foreach ($homeSlides as $index => $slide)
                            <button class="carousel-indicator" type="button" data-carousel-indicator="{{ $index }}" aria-label="Afficher la photo {{ $index + 1 }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}">{{ $index + 1 }}</button>
                        @endforeach
                    </div>
                    <button class="carousel-toggle" type="button" data-carousel-toggle aria-label="Mettre le défilement en pause">Pause</button>
                    <button class="carousel-arrow" type="button" data-carousel-next aria-label="Photo suivante">›</button>
                </div>
            </div>
        </section>

        <section class="intro" id="engagement">
            <div>
                <span class="eyebrow">Notre engagement</span>
                <h2>Donner aux initiatives locales les moyens d’avancer.</h2>
            </div>
            <p>Nous croyons qu’un accompagnement financier de proximité peut aider chacun à concrétiser ses projets. MITSINJO met l’accent sur la relation avec les agents, la clarté du suivi et des outils simples pour gérer les échéances.</p>
        </section>

        <section class="impact">
            <div class="impact-inner">
                <div class="impact-copy"><span class="eyebrow">MITSINJO à vos côtés</span><h2>Votre prochain pas commence ici.</h2><p>Connectez-vous à votre espace pour retrouver votre suivi ou rapprochez-vous de votre agent ONG.</p></div>
                <a class="button-outline" href="{{ $dashboardUrl }}">{{ auth()->check() ? 'Accéder à mon espace' : 'Accéder à mon espace' }} <span aria-hidden="true">&nbsp;→</span></a>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <p>© {{ date('Y') }} MITSINJO. Tous droits réservés.</p>
            <div class="footer-links"><a href="{{ route('home') }}">Accueil</a><a href="{{ route('login') }}">Connexion</a><a href="{{ route('register') }}">Devenir membre</a></div>
        </div>
    </footer>
</div>
<script>
    (() => {
        const carousel = document.querySelector('[data-carousel]');

        if (!carousel) return;

        const slides = [...carousel.querySelectorAll('[data-carousel-slide]')];
        const indicators = [...carousel.querySelectorAll('[data-carousel-indicator]')];
        const toggle = carousel.querySelector('[data-carousel-toggle]');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let currentSlide = 0;
        let timer;
        let paused = reducedMotion;
        let hovered = false;
        let focused = false;

        const showSlide = (index) => {
            currentSlide = (index + slides.length) % slides.length;
            slides.forEach((slide, slideIndex) => {
                slide.hidden = slideIndex !== currentSlide;
            });
            indicators.forEach((indicator, indicatorIndex) => {
                indicator.setAttribute('aria-current', String(indicatorIndex === currentSlide));
            });
        };

        const stopRotation = () => {
            window.clearInterval(timer);
            timer = undefined;
        };

        const startRotation = () => {
            stopRotation();
            if (!paused && !hovered && !focused && !document.hidden && slides.length > 1) {
                timer = window.setInterval(() => showSlide(currentSlide + 1), 5000);
            }
        };

        carousel.querySelector('[data-carousel-previous]').addEventListener('click', () => {
            showSlide(currentSlide - 1);
            startRotation();
        });
        carousel.querySelector('[data-carousel-next]').addEventListener('click', () => {
            showSlide(currentSlide + 1);
            startRotation();
        });
        indicators.forEach((indicator) => {
            indicator.addEventListener('click', () => {
                showSlide(Number(indicator.dataset.carouselIndicator));
                startRotation();
            });
        });
        toggle.addEventListener('click', () => {
            paused = !paused;
            toggle.textContent = paused ? 'Reprendre' : 'Pause';
            toggle.setAttribute('aria-label', paused ? 'Reprendre le défilement' : 'Mettre le défilement en pause');
            startRotation();
        });
        carousel.addEventListener('mouseenter', () => {
            hovered = true;
            stopRotation();
        });
        carousel.addEventListener('mouseleave', () => {
            hovered = false;
            startRotation();
        });
        carousel.addEventListener('focusin', () => {
            focused = true;
            stopRotation();
        });
        carousel.addEventListener('focusout', (event) => {
            if (!carousel.contains(event.relatedTarget)) {
                focused = false;
                startRotation();
            }
        });
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) stopRotation();
            else startRotation();
        });

        toggle.textContent = paused ? 'Reprendre' : 'Pause';
        startRotation();
    })();
</script>
</body>
</html>