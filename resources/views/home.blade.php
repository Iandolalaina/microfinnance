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
        .hero { background: var(--mitsinjo-paper); }
        .hero-inner { width: min(1160px, calc(100% - 48px)); min-height: 550px; margin: auto; display: grid; grid-template-columns: 1fr 1.02fr; align-items: center; gap: 60px; padding: 54px 0 62px; }
        .eyebrow { display: inline-flex; align-items: center; gap: 9px; color: var(--mitsinjo-green); font-size: 11px; font-weight: 700; letter-spacing: .13em; text-transform: uppercase; }
        .eyebrow::before { width: 24px; height: 2px; background: var(--mitsinjo-gold); content: ''; }
        h1, h2, h3, p { margin-top: 0; }
        .hero h1 { max-width: 540px; margin: 20px 0 18px; color: var(--mitsinjo-green); font-family: 'Lora', serif; font-size: clamp(40px, 5vw, 64px); font-weight: 600; line-height: 1.08; }
        .hero h1 span { color: var(--mitsinjo-blue); }
        .hero-copy { max-width: 465px; margin-bottom: 28px; color: #5f7069; font-size: 16px; line-height: 1.8; }
        .hero-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 22px; }
        .text-link { display: inline-flex; align-items: center; gap: 8px; color: var(--mitsinjo-green); font-size: 13px; font-weight: 700; }
        .hero-photo { position: relative; min-height: 400px; }
        .hero-photo img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; border-radius: 2px; }
        .photo-note { position: absolute; right: -20px; bottom: 24px; max-width: 215px; padding: 17px 20px; color: #fff; background: var(--mitsinjo-green); font-size: 12px; line-height: 1.6; }
        .photo-note strong { display: block; margin-bottom: 3px; color: #f4c65c; font-size: 17px; }
        .intro { width: min(1160px, calc(100% - 48px)); margin: auto; padding: 74px 0 66px; display: grid; grid-template-columns: .72fr 1fr; gap: 80px; align-items: start; }
        .intro h2, .section-heading h2, .impact-copy h2 { margin: 13px 0 0; color: var(--mitsinjo-green); font-family: 'Lora', serif; font-size: 34px; line-height: 1.2; }
        .intro p { margin: 0; color: #63716c; font-size: 15px; line-height: 1.9; }
        .services { padding: 72px 0 78px; background: #edf3ef; }
        .section-inner { width: min(1160px, calc(100% - 48px)); margin: auto; }
        .section-heading { display: flex; align-items: end; justify-content: space-between; gap: 30px; margin-bottom: 38px; }
        .section-heading p { max-width: 330px; margin: 0 0 3px; color: #63716c; font-size: 14px; line-height: 1.75; }
        .service-list { display: grid; grid-template-columns: repeat(3, 1fr); border-top: 1px solid #cedbd3; }
        .service-item { min-height: 205px; padding: 28px 28px 24px 0; border-bottom: 1px solid #cedbd3; }
        .service-item + .service-item { padding-left: 28px; border-left: 1px solid #cedbd3; }
        .service-number { color: var(--mitsinjo-blue); font-size: 12px; font-weight: 700; letter-spacing: .08em; }
        .service-item h3 { margin: 22px 0 10px; color: var(--mitsinjo-green); font-family: 'Lora', serif; font-size: 21px; }
        .service-item p { max-width: 300px; margin-bottom: 0; color: #63716c; font-size: 13px; line-height: 1.8; }
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
            .hero-inner { width: min(100% - 36px, 620px); grid-template-columns: 1fr; gap: 32px; padding: 54px 0 46px; }
            .hero h1 { max-width: 520px; font-size: 46px; }
            .hero-photo { min-height: 310px; margin-right: 14px; }
            .photo-note { right: -14px; bottom: 15px; }
            .intro { width: min(100% - 36px, 620px); grid-template-columns: 1fr; gap: 22px; padding: 56px 0; }
            .intro h2, .section-heading h2, .impact-copy h2 { font-size: 29px; }
            .section-inner, .impact-inner, .footer-inner { width: min(100% - 36px, 620px); }
            .services { padding: 56px 0; }
            .section-heading { display: block; margin-bottom: 26px; }
            .section-heading p { margin-top: 13px; }
            .service-list { grid-template-columns: 1fr; }
            .service-item, .service-item + .service-item { min-height: 0; padding: 22px 0; border-left: 0; }
            .service-item h3 { margin-top: 12px; }
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
            <div class="hero-inner">
                <div class="reveal">
                    <span class="eyebrow">La finance au plus près de vous</span>
                    <h1>Faisons grandir <span>vos projets.</span></h1>
                    <p class="hero-copy">MITSINJO accompagne les personnes et les petites activités avec des solutions de microfinance accessibles, un suivi clair et un service de proximité.</p>
                    <div class="hero-actions">
                        <a class="text-link" href="#services">Nos services <span aria-hidden="true">→</span></a>
                    </div>
                </div>
                <div class="hero-photo reveal" aria-label="Des membres d'une communauté réunis">
                    <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=85" alt="Un groupe réuni autour d'un projet commun" fetchpriority="high">
                    <div class="photo-note"><strong>À vos côtés</strong>Un accompagnement humain, au rythme de vos projets.</div>
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

        <section class="services" id="services">
            <div class="section-inner">
                <div class="section-heading">
                    <div><span class="eyebrow">Nos services</span><h2>Un appui concret, à chaque étape.</h2></div>
                    <p>Des outils conçus pour faciliter le suivi des financements et des remboursements.</p>
                </div>
                <div class="service-list">
                    <article class="service-item"><span class="service-number">01 / FINANCEMENT</span><h3><a href="{{ route('credits.index') }}">Des projets accompagnés</a></h3><p>Un suivi de crédit organisé pour aider les clients à garder le cap sur leurs objectifs.</p></article>
                    <article class="service-item"><span class="service-number">02 / SUIVI</span><h3>Une vision claire</h3><p>Retrouvez les montants, les échéances et l’avancement de vos remboursements dans votre espace.</p></article>
                    <article class="service-item"><span class="service-number">03 / PROXIMITÉ</span><h3>Un lien avec votre agent</h3><p>Les agents assurent le suivi sur le terrain et vous accompagnent dans vos démarches.</p></article>
                </div>
            </div>
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
</body>
</html>