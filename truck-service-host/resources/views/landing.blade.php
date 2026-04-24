<x-guest.layout>
    <x-slot name="title">{{ $settings['landing_title'] ?? 'بول ستيشن - حمل التطبيق الآن' }}</x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box }
        :root {
            --indigo: #4F46E5; --indigo-dark: #3730A3; --indigo-light: #EEF2FF;
            --slate: #0F172A; --slate-mid: #475569; --slate-light: #94A3B8;
            --green: #10B981; --green-light: #ECFDF5;
            --orange: #F97316; --orange-light: #FFF7ED;
        }
        body { font-family: 'Cairo', sans-serif; background: #FAFAFA; color: var(--slate); direction: rtl; overflow-x: hidden }
        .page { min-height: 100vh }

        /* ── NAV ── */
        nav { display: flex; align-items: center; justify-content: center; padding: 1.5rem 2rem; background: rgba(255,255,255,0.9); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(79,70,229,0.08); position: sticky; top: 0; z-index: 100 }
        .logo-img { height: 50px; width: auto; object-contain: contain; }
        .logo-text { font-size: 1.6rem; font-weight: 900; letter-spacing: -0.03em; color: var(--indigo); text-transform: uppercase }

        /* ── HERO ── */
        .hero { padding: 5rem 2rem 4rem; text-align: center; max-width: 850px; margin: 0 auto }
        .hero-badge { display: inline-flex; align-items: center; gap: 0.4rem; background: var(--indigo-light); color: var(--indigo); font-size: 0.8rem; font-weight: 700; padding: 0.35rem 1rem; border-radius: 999px; margin-bottom: 1.5rem; letter-spacing: 0.02em }
        .badge-dot { width: 6px; height: 6px; background: var(--indigo); border-radius: 50%; animation: pulse 2s infinite }
        @keyframes pulse { 0%, 100% { opacity: 1; transform: scale(1) } 50% { opacity: .5; transform: scale(0.85) } }
        h1 { font-size: clamp(2.2rem, 5vw, 3.5rem); font-weight: 900; line-height: 1.1; margin-bottom: 1.25rem; color: var(--slate) }
        h1 span { color: var(--indigo) }
        .hero-sub { font-size: 1.1rem; color: var(--slate-mid); line-height: 1.7; margin-bottom: 2.5rem; max-width: 600px; margin-left: auto; margin-right: auto }

        /* ── BUTTONS ── */
        .btns { display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center; margin-bottom: 3rem }
        .btn { display: inline-flex; align-items: center; gap: 0.75rem; padding: 0.9rem 1.75rem; border-radius: 14px; font-family: 'Cairo', sans-serif; font-size: 1rem; font-weight: 700; cursor: pointer; text-decoration: none; transition: transform 0.15s, box-shadow 0.15s; border: none }
        .btn:hover { transform: translateY(-2px) }
        .btn-primary { background: var(--indigo); color: #fff; box-shadow: 0 4px 20px rgba(79,70,229,0.35) }
        .btn-primary:hover { background: var(--indigo-dark); box-shadow: 0 8px 30px rgba(79,70,229,0.45) }
        .btn-dark { background: var(--slate); color: #fff; box-shadow: 0 4px 20px rgba(15,23,42,0.25) }
        .btn-dark:hover { background: #1e293b; box-shadow: 0 8px 30px rgba(15,23,42,0.35) }
        .btn-icon { width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem }
        .btn-text-wrap { text-align: right }
        .btn-hint { display: block; font-size: 0.68rem; font-weight: 400; opacity: 0.75; margin-bottom: 1px }

        /* ── SCREENSHOTS ── */
        .screens { display: flex; justify-content: center; align-items: flex-end; gap: 1.25rem; padding: 0 1rem 3rem }
        .screen-wrap { flex-shrink: 0; position: relative; transition: all 0.4s ease }
        .screen-wrap.side { width: 150px; transform: translateY(16px) rotate(-5deg); opacity: 0.85 }
        .screen-wrap.side.right { transform: translateY(16px) rotate(5deg) }
        .screen-wrap.center { width: 210px; z-index: 2 }
        .screen-wrap:hover { transform: translateY(-10px) scale(1.02) !important; opacity: 1 !important; }
        .screen-img { width: 100%; border-radius: 24px; display: block; background: #e2e8f0; aspect-ratio: 9/19; object-fit: cover; border: 2px solid rgba(255,255,255,0.9); shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
        .screen-wrap.center .screen-img { border: 8px solid var(--slate); border-radius: 32px }
        .screen-placeholder { width: 100%; aspect-ratio: 9/19; background: linear-gradient(160deg,#c7d2fe 0%,#e0e7ff 60%,#ede9fe 100%); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2rem }

        /* ── STATS ── */
        .stats { display: flex; justify-content: center; flex-wrap: wrap; gap: 0.75rem; max-width: 600px; margin: 0 auto 4rem; padding: 0 1.5rem }
        .stat { background: #fff; border: 1px solid rgba(79,70,229,0.1); border-radius: 12px; padding: 0.9rem 1.4rem; text-align: center; flex: 1; min-width: 120px }
        .stat-num { font-size: 1.5rem; font-weight: 900; color: var(--indigo) }
        .stat-lbl { font-size: 0.72rem; color: var(--slate-mid); margin-top: 2px }

        /* ── FEATURES ── */
        .section { padding: 4rem 1.5rem; max-width: 900px; margin: 0 auto }
        .section-label { text-align: center; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.1em; color: var(--indigo); text-transform: uppercase; margin-bottom: 0.6rem }
        .section-title { text-align: center; font-size: clamp(1.5rem, 3.5vw, 2rem); font-weight: 900; color: var(--slate); margin-bottom: 2.5rem }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem }
        .feat { background: #fff; border: 1px solid rgba(0,0,0,0.06); border-radius: 20px; padding: 1.75rem; transition: transform 0.2s, box-shadow 0.2s; cursor: default }
        .feat:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.08) }
        .feat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 1rem }
        .feat-icon.purple { background: var(--indigo-light); color: var(--indigo) }
        .feat-icon.green { background: var(--green-light); color: var(--green) }
        .feat-icon.orange { background: var(--orange-light); color: var(--orange) }

        /* ── FOOTER ── */
        footer { text-align: center; padding: 2.5rem 1rem; border-top: 1px solid rgba(0,0,0,0.06); color: var(--slate-light); font-size: 0.82rem }
        footer a { color: var(--indigo); text-decoration: none; font-weight: 600 }
        
        .divider { height: 1px; background: rgba(0,0,0,0.05); max-width: 860px; margin: 0 auto }
        
        @media (max-width: 640px) {
            .screens { flex-direction: column; align-items: center; gap: 2rem; }
            .screen-wrap.side { width: 80%; transform: none !important; }
            .screen-wrap.center { width: 90%; transform: scale(1) !important; }
        }
    </style>

    <div class="page">
        <!-- NAV -->
        <nav>
            <span class="logo-text">BULL<span class="logo-dot">.</span>STATION</span>
        </nav>

        <!-- HERO -->
        <div class="hero">
            <div class="hero-badge">
                <span class="badge-dot"></span>
                متاح الآن على أندرويد وiOS
            </div>
            <h1>{{ $settings['landing_title'] ?? 'نقل المعدات الثقيلة' }}<br><span>بلمسة واحدة</span></h1>
            <p class="hero-sub">{{ $settings['landing_subtitle'] ?? 'منصة بول ستيشن تربطك بمئات الشاحنات والمعدات القريبة منك في أقل من دقيقة، باحترافية وأمان تام.' }}</p>

            <div class="btns">
                @if(isset($settings['android_app_file']))
                    <a href="{{ asset('storage/'.$settings['android_app_file']) }}" class="btn btn-primary">
                        <div class="btn-icon"><i class="fab fa-android"></i></div>
                        <div class="btn-text-wrap">
                            <span class="btn-hint">تحميل مباشر</span>
                            أندرويد APK
                        </div>
                    </a>
                @endif

                @if(isset($settings['ios_app_link']))
                    <a href="{{ $settings['ios_app_link'] }}" target="_blank" class="btn btn-dark">
                        <div class="btn-icon"><i class="fab fa-apple"></i></div>
                        <div class="btn-text-wrap">
                            <span class="btn-hint">متوفر على</span>
                            App Store
                        </div>
                    </a>
                @endif
            </div>
        </div>

        <!-- SCREENS -->
        <div class="screens">
            <!-- Left Screen -->
            <div class="screen-wrap side">
                @if(isset($settings['app_screenshot_2']))
                    <img src="{{ asset('storage/'.$settings['app_screenshot_2']) }}" class="screen-img" alt="Screenshot">
                @else
                    <div class="screen-placeholder">📦</div>
                @endif
            </div>

            <!-- Center Screen (Logo or Main Screenshot) -->
            <div class="screen-wrap center">
                @if(isset($settings['app_screenshot_1']))
                    <img src="{{ asset('storage/'.$settings['app_screenshot_1']) }}" class="screen-img" alt="Screenshot Main">
                @else
                    <div class="screen-placeholder" style="font-size:2.5rem">🚛</div>
                @endif
            </div>

            <!-- Right Screen -->
            <div class="screen-wrap side right">
                @if(isset($settings['app_screenshot_3']))
                    <img src="{{ asset('storage/'.$settings['app_screenshot_3']) }}" class="screen-img" alt="Screenshot">
                @else
                    <div class="screen-placeholder">🗺️</div>
                @endif
            </div>
        </div>

        <!-- STATS -->
        <div class="stats">
            <div class="stat">
                <div class="stat-num">500+</div>
                <div class="stat-lbl">معدة متاحة</div>
            </div>
            <div class="stat">
                <div class="stat-num">10K+</div>
                <div class="stat-lbl">عملية ناجحة</div>
            </div>
            <div class="stat">
                <div class="stat-num">4.9★</div>
                <div class="stat-lbl">تقييم المستخدمين</div>
            </div>
        </div>

        <div class="divider"></div>

        <!-- FEATURES -->
        <div class="section">
            <div class="section-label">لماذا بول ستيشن؟</div>
            <div class="section-title">كل ما تحتاجه في مكان واحد</div>
            <p class="hero-sub" style="margin-top: -1.5rem; margin-bottom: 2rem;">{{ $settings['landing_description'] ?? '' }}</p>
            
            <div class="features">
                <div class="feat">
                    <div class="feat-icon purple"><i class="fas fa-shield-halved"></i></div>
                    <h3>أمان تام</h3>
                    <p>جميع عمليات الحجز موثقة ومضمونة، حقوقك محفوظة في كل خطوة عبر منصتنا.</p>
                </div>
                <div class="feat">
                    <div class="feat-icon green"><i class="fas fa-bolt"></i></div>
                    <h3>سرعة الإنجاز</h3>
                    <p>ابحث عن المعدة، احجز، وادفع في أقل من دقيقة واحدة دون تعقيدات.</p>
                </div>
                <div class="feat">
                    <div class="feat-icon orange"><i class="fas fa-location-dot"></i></div>
                    <h3>تغطية واسعة</h3>
                    <p>مئات الشاحنات والمعدات الثقيلة منتشرة في جميع المناطق لتصل إليك بسرعة.</p>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <footer>
            <div>© {{ date('Y') }} BULL STATION — جميع الحقوق محفوظة.</div>
            <div class="footer-links" style="display: flex; justify-content: center; gap: 1.5rem; margin-top: 0.75rem;">
                <a href="{{ route('policies.public') }}">الشروط والسياسات</a>
                {{-- الايميل أصبح ديناميكياً هنا --}}
                <a href="mailto:{{ $settings['support_email'] ?? 'support@bull-station.com' }}">تواصل معنا</a>
            </div>
        </footer>
    </div>

    <!-- إضافة مكتبة الأيقونات FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</x-guest.layout>