<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Tootli Directo: reparto a domicilio para restaurantes, sin comisiones</title>
    <meta name="description" content="Tarifa fija por entrega y cero comisión sobre tus ventas. Repartidor en minutos y cobro en efectivo en la puerta.">
    <link rel="icon" href="{{ asset('public/assets/landing/image/favicon.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        background: '#F8F9FA',
                        foreground: '#111111',
                        'site-brand': '#1AD598', // Tootli Green
                        'site-muted': '#6B7280',
                        'site-hairline': '#E5E7EB',
                        'site-outline': '#D1D5DB'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Montserrat', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            background-color: #F8F9FA;
            color: #111111;
            -webkit-font-smoothing: antialiased;
        }
    </style>

    <!-- AlpineJS for Interactive Calculator -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('calculatorApp', () => ({
                venta: 500,
                clientePaga: 30,
                
                setVenta(amount) {
                    this.venta = amount;
                },

                get teQueda() {
                    return this.venta - (38 - this.clientePaga);
                },
                
                get ahorro() {
                    return this.teQueda - Math.round(this.venta * 0.70);
                }
            }))
        })
    </script>
</head>
<body class="bg-background min-h-screen flex flex-col font-sans selection:bg-site-brand selection:text-foreground">

    <!-- Header / Navbar -->
    <header class="sticky top-0 z-40 bg-background px-3 pb-1 pt-3 md:px-6 md:pb-1.5 md:pt-4 lg:px-8 lg:pb-2 lg:pt-5">
        <nav aria-label="Navegación principal" class="mx-auto flex w-full max-w-[1220px] items-center justify-between rounded-full border border-site-hairline bg-white py-2.5 pl-4 pr-3 md:pl-5 lg:py-3 lg:pl-[26px] lg:pr-3.5 shadow-sm">
            
            <a class="flex items-center gap-2 shrink-0" href="/">
                @php($logo = \App\Models\BusinessSetting::where(['key' => 'logo'])->first()->value ?? '')
                <img src="{{ asset('storage/app/public/business/' . $logo) }}" alt="Tootli" class="h-[29px] w-auto md:h-[33px] lg:h-[39px] object-contain" onerror="this.src='{{ asset('public/assets/admin/img/160x160/img2.jpg') }}'">
                <span class="font-display font-black text-xl tracking-tight hidden sm:inline-block">DIRECTO</span>
            </a>

            <!-- Desktop Links -->
            <ul class="hidden items-center gap-5 md:flex lg:gap-[30px]">
                <li><a class="text-[13.5px] text-foreground transition-opacity hover:opacity-70 lg:text-[15px] font-medium" href="#como-funciona">Cómo funciona</a></li>
                <li><a class="text-[13.5px] text-foreground transition-opacity hover:opacity-70 lg:text-[15px] font-medium" href="#precios">Precios</a></li>
                <li><a class="text-[13.5px] text-foreground transition-opacity hover:opacity-70 lg:text-[15px] font-medium" href="#flotillas">Flotillas</a></li>
            </ul>

            <div class="flex shrink-0 items-center gap-2 lg:gap-2.5">
                <a href="{{ route('restaurant.create') }}" class="hidden rounded-full border border-site-outline px-5 py-[11px] text-[14px] font-semibold text-foreground transition-colors hover:bg-gray-100 lg:inline-block">
                    Iniciar sesión
                </a>
                <a href="{{ route('restaurant.create') }}" class="rounded-full bg-site-brand px-3.5 py-[9px] text-[13px] font-bold text-foreground transition-opacity hover:opacity-90 md:px-4 md:py-2.5 md:text-[13.5px] lg:px-5 lg:py-[11px] lg:text-[14px] shadow-sm">
                    Regístrate
                </a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <main class="flex-grow flex flex-col items-center">
        
        <section class="flex flex-col items-center gap-[18px] bg-background px-5 pb-11 pt-9 md:gap-[22px] md:px-10 md:pb-14 md:pt-12 lg:gap-7 lg:px-[90px] lg:pb-21 lg:pt-18 w-full max-w-[1400px]">
            
            <p class="text-center text-[11px] font-bold uppercase tracking-[1.8px] text-green-600 md:text-[12px] md:tracking-[2px] lg:text-[14px] lg:tracking-[2.5px]">
                Para Restaurantes y Negocios
            </p>

            <h1 class="max-w-[1160px] text-center font-display text-[40px] font-black uppercase leading-[1.02] tracking-[-1px] text-foreground md:text-[58px] md:leading-none md:tracking-[-1.5px] lg:text-[84px] lg:tracking-[-2px]">
                TARIFA FIJA POR ENTREGA Y <br class="hidden md:inline"> 
                <span class="text-site-brand">CERO COMISIÓN</span> SOBRE TUS VENTAS.
            </h1>

            <p class="text-center text-[15px] leading-[1.6] text-site-muted md:max-w-[620px] md:text-[16px] lg:max-w-[780px] lg:text-[18px]">
                Repartidor en minutos y cobro en efectivo en la puerta. Todo el control de tus clientes, ninguna penalización.
            </p>

            <div class="mt-4 flex w-full md:w-auto">
                <a href="{{ route('restaurant.create') }}" class="flex w-full items-center justify-center gap-[9px] rounded-xl bg-site-brand px-8 py-[15px] text-[16px] font-bold text-foreground transition-opacity hover:opacity-90 md:w-auto md:py-4 md:text-[16px] lg:py-[17px] lg:text-[17px] md:gap-2.5 md:px-7 shadow-lg shadow-site-brand/30">
                    Comienza ahora
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </section>

        <!-- Centro de Mando Section -->
        <section class="w-full px-5 py-16 md:py-20 lg:px-10 max-w-[1300px] mx-auto text-center flex flex-col items-center">
            
            <p class="text-[#FFD600] font-bold text-[11px] md:text-xs tracking-[2px] uppercase mb-4">
                Tu centro de mando
            </p>
            <h2 class="font-display font-black uppercase text-3xl md:text-5xl lg:text-[60px] leading-[1.05] tracking-tight text-foreground mb-6 max-w-4xl mx-auto">
                PIDE UN REPARTIDOR <br> EN SEGUNDOS
            </h2>
            <p class="text-gray-500 text-sm md:text-[17px] leading-relaxed max-w-2xl mx-auto mb-12">
                Dirección y monto, y listo: ves la tarifa exacta antes de confirmar y sigues al repartidor en vivo, igual que tu cliente.
            </p>

            <!-- Dashboard Mockup Container -->
            <div class="w-full bg-[#F6F5EB] rounded-[32px] p-4 md:p-8 lg:p-12 shadow-sm border border-[#EBEBEB]">
                <div class="bg-white rounded-[20px] shadow-[0_20px_50px_-12px_rgba(0,0,0,0.15)] flex flex-col md:flex-row overflow-hidden border border-gray-100 h-[480px] lg:h-[550px] relative text-left">
                    
                    <!-- Sidebar -->
                    <div class="w-16 bg-[#111315] flex flex-col items-center py-6 gap-6 shrink-0 z-20">
                        <div class="text-[#1D9C50] mb-2">
                            <!-- Grid icon -->
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                        </div>
                        <div class="text-gray-400 hover:text-white transition-colors">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                        </div>
                        <div class="text-gray-400 hover:text-white transition-colors">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M21 12H3"/><path d="M12 12h.01"/></svg>
                        </div>
                        <div class="text-gray-400 hover:text-white transition-colors">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                        </div>
                        <div class="text-gray-400 hover:text-white transition-colors">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        </div>
                    </div>
                    
                    <!-- Main Map Area -->
                    <div class="flex-grow flex flex-col bg-[#F0F2F5] relative">
                        <!-- Top Bar -->
                        <div class="h-[60px] bg-white border-b border-gray-200 flex items-center justify-between px-6 z-20 shrink-0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-display font-black text-xl tracking-tight text-[#111]">TOOTLI</span><span class="font-display font-normal text-xl text-[#6AA438] tracking-tight">DIRECTO</span>
                            </div>
                            <button class="bg-[#1D9C50] text-white font-bold text-[13px] px-4 py-2 rounded-lg flex items-center gap-1.5 hover:bg-[#188042] transition-colors">
                                <span>+</span> Nueva entrega
                            </button>
                        </div>
                        
                        <!-- Map Content -->
                        <div class="flex-grow relative p-4 overflow-hidden">
                            <!-- Abstract Map Blocks -->
                            <!-- Top Left rounded blob -->
                            <div class="absolute top-0 left-0 w-[240px] h-[180px] bg-[#DFECF5] rounded-br-[100px] opacity-90"></div>
                            
                            <!-- Road grids (White blocks) -->
                            <div class="absolute top-[20px] left-[260px] right-[20px] h-[160px] bg-[#F7F9FC] rounded-xl shadow-sm"></div>
                            
                            <div class="absolute top-[200px] left-[20px] w-[220px] h-[180px] bg-[#F7F9FC] rounded-xl shadow-sm"></div>
                            <div class="absolute top-[200px] left-[260px] right-[20px] h-[180px] bg-[#F7F9FC] rounded-xl shadow-sm"></div>
                            
                            <div class="absolute top-[400px] left-[20px] w-[200px] h-[150px] bg-[#F7F9FC] rounded-xl shadow-sm"></div>
                            <div class="absolute top-[400px] left-[240px] right-[20px] h-[150px] bg-[#F7F9FC] rounded-xl shadow-sm"></div>

                            <!-- Big Green Blob -->
                            <div class="absolute top-[320px] right-[40px] w-[280px] h-[280px] bg-[#E1F0E1] rounded-full opacity-70"></div>
                            
                            <!-- SVG Route Line -->
                            <svg class="absolute inset-0 w-full h-full pointer-events-none" style="z-index: 10;">
                                <!-- Path from store to home -->
                                <path d="M 120 440 L 190 440 L 190 320 L 400 320 L 400 240 L 460 240" fill="none" stroke="#111315" stroke-width="3" />
                            </svg>
                            
                            <!-- Map Markers -->
                            <!-- Store Marker -->
                            <div class="absolute top-[424px] left-[104px] w-[32px] h-[32px] bg-[#111315] rounded-full flex items-center justify-center text-white shadow-md z-20 border-[2px] border-white">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            </div>
                            
                            <!-- Delivery Bike Marker -->
                            <div class="absolute top-[330px] left-[384px] z-30 flex flex-col items-center">
                                <div class="bg-[#111315] text-white text-[10px] font-bold px-3 py-1.5 rounded-full mb-1">
                                    Llega en <span class="text-[#4CD964]">12 min</span>
                                </div>
                                <div class="w-[32px] h-[32px] bg-[#1D9C50] rounded-full flex items-center justify-center text-white shadow-[0_4px_10px_rgba(29,156,80,0.4)] border-[2px] border-white relative">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-3 11.5V14l-3-3 4-3 2 3h2"/></svg>
                                </div>
                            </div>
                            
                            <!-- Destination Marker -->
                            <div class="absolute top-[224px] left-[444px] w-[32px] h-[32px] bg-[#1D9C50] rounded-full flex items-center justify-center text-white shadow-md z-20 border-[2px] border-white">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Sidebar: Orders -->
                    <div class="w-full md:w-[320px] bg-[#FAFAFA] border-l border-gray-200 flex flex-col shrink-0">
                        <div class="px-5 py-4 flex justify-between items-center bg-white border-b border-gray-100 z-20 shrink-0">
                            <h3 class="font-bold text-[14px]">Pedidos de hoy</h3>
                            <span class="text-gray-500 text-[12px] font-semibold">18</span>
                        </div>
                        
                        <div class="flex-grow overflow-y-auto p-4 flex flex-col gap-3 z-20">
                            
                            <!-- Order 1 -->
                            <div class="border border-gray-200 rounded-[10px] p-3.5 hover:shadow-md transition-shadow bg-white cursor-pointer">
                                <div class="flex justify-between items-center mb-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] text-gray-500 font-bold">L-1042</span>
                                        <span class="text-[13px] font-bold">Lupita R.</span>
                                    </div>
                                    <span class="font-bold text-[13px]">$438</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div class="bg-[#E6F8ED] text-[#1D9C50] text-[10px] font-bold px-2 py-1 rounded-full flex items-center gap-1.5">
                                        <div class="w-1.5 h-1.5 bg-[#1D9C50] rounded-full"></div>
                                        En camino
                                    </div>
                                    <span class="text-gray-400 font-bold">›</span>
                                </div>
                            </div>
                            
                            <!-- Order 2 -->
                            <div class="border border-gray-200 rounded-[10px] p-3.5 hover:shadow-md transition-shadow bg-white cursor-pointer">
                                <div class="flex justify-between items-center mb-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] text-gray-500 font-bold">L-1041</span>
                                        <span class="text-[13px] font-bold">Carlos M.</span>
                                    </div>
                                    <span class="font-bold text-[13px]">$268</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div class="bg-[#E6F8ED] text-[#1D9C50] text-[10px] font-bold px-2 py-1 rounded-full flex items-center gap-1.5">
                                        <div class="w-1.5 h-1.5 bg-[#1D9C50] rounded-full"></div>
                                        Buscando repartidor
                                    </div>
                                    <span class="text-gray-400 font-bold">›</span>
                                </div>
                            </div>
                            
                            <!-- Order 3 -->
                            <div class="border border-gray-200 rounded-[10px] p-3.5 hover:shadow-md transition-shadow bg-white opacity-70 cursor-pointer">
                                <div class="flex justify-between items-center mb-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] text-gray-500 font-bold">L-1040</span>
                                        <span class="text-[13px] font-bold">Fonda Marta</span>
                                    </div>
                                    <span class="font-bold text-[13px]">$1,120</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div class="bg-[#F0F2F5] text-gray-600 text-[10px] font-bold px-2 py-1 rounded-full flex items-center gap-1.5">
                                        <div class="w-1.5 h-1.5 bg-gray-400 rounded-full"></div>
                                        Programado 7:30 pm
                                    </div>
                                    <span class="text-gray-400 font-bold">›</span>
                                </div>
                            </div>

                            <!-- Order 4 -->
                            <div class="border border-gray-200 rounded-[10px] p-3.5 hover:shadow-md transition-shadow bg-white cursor-pointer">
                                <div class="flex justify-between items-center mb-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] text-gray-500 font-bold">L-1039</span>
                                        <span class="text-[13px] font-bold">Ana G.</span>
                                    </div>
                                    <span class="font-bold text-[13px]">$312</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div class="bg-[#E6F8ED] text-[#1D9C50] text-[10px] font-bold px-2 py-1 rounded-full flex items-center gap-1.5">
                                        <div class="w-1.5 h-1.5 bg-[#1D9C50] rounded-full"></div>
                                        Entregado
                                    </div>
                                    <span class="text-gray-400 font-bold">›</span>
                                </div>
                            </div>
                            
                            <!-- Order 5 -->
                            <div class="border border-gray-200 rounded-[10px] p-3.5 hover:shadow-md transition-shadow bg-white cursor-pointer">
                                <div class="flex justify-between items-center mb-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] text-gray-500 font-bold">L-1038</span>
                                        <span class="text-[13px] font-bold">Beto T.</span>
                                    </div>
                                    <span class="font-bold text-[13px]">$189</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div class="bg-[#E6F8ED] text-[#1D9C50] text-[10px] font-bold px-2 py-1 rounded-full flex items-center gap-1.5">
                                        <div class="w-1.5 h-1.5 bg-[#1D9C50] rounded-full"></div>
                                        Entregado
                                    </div>
                                    <span class="text-gray-400 font-bold">›</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cobra como siempre Section -->
        <section class="w-full px-5 py-16 md:py-20 lg:px-10 max-w-[1300px] mx-auto text-center flex flex-col items-center">
            
            <p class="text-[#FFD600] font-bold text-[11px] md:text-xs tracking-[2px] uppercase mb-4">
                COBRA COMO SIEMPRE
            </p>
            <h2 class="font-display font-black uppercase text-3xl md:text-5xl lg:text-[60px] leading-[1.05] tracking-tight text-foreground mb-6 max-w-4xl mx-auto">
                EL EFECTIVO SÍ ES BIENVENIDO
            </h2>
            <p class="text-gray-500 text-sm md:text-[17px] leading-relaxed max-w-2xl mx-auto mb-16">
                En México, la gran mayoría prefiere pagar sus pedidos en efectivo, pero casi ninguna app lo permite. Nosotros entendimos las reglas del juego desde el día uno.
            </p>

            <!-- Cards Container -->
            <div class="w-full bg-[#F6F5EB] rounded-[32px] p-6 md:p-12 lg:p-20 shadow-sm border border-[#EBEBEB] relative h-[650px] lg:h-[500px] overflow-hidden">
                
                <div class="relative w-full max-w-4xl h-full mx-auto">
                    
                    <!-- Top Card (Efectivo) -->
                    <div class="bg-white rounded-[24px] shadow-xl p-6 md:p-8 w-[90%] md:w-[480px] text-left border border-gray-100 z-20 absolute top-4 left-1/2 -translate-x-1/2 lg:top-[5%]">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="font-bold text-[14px] md:text-[16px]">Pedido L-1042 · Lupita R.</h3>
                            <div class="bg-[#E6F8ED] text-[#1D9C50] text-[11px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1.5 shrink-0">
                                <div class="w-1.5 h-1.5 bg-[#1D9C50] rounded-full"></div>
                                En camino
                            </div>
                        </div>
                        <p class="text-gray-500 text-[13px] mb-6">Av. Chapultepec 480, Col. Americana</p>
                        
                        <div class="bg-[#FFFDF2] rounded-xl border border-[#F3E8B5] p-4 flex flex-col gap-2">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2 text-[13px] font-bold">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                                    El repartidor cobra en efectivo
                                </div>
                                <span class="font-black text-lg text-foreground">$438</span>
                            </div>
                            <p class="text-[11px] text-gray-500">Pedido $370 + envío $68 · sin nada que cobrar tú</p>
                        </div>
                    </div>

                    <!-- Bottom Left Card (Tarjeta) -->
                    <div class="bg-white rounded-[20px] shadow-xl p-6 w-[280px] text-left border border-gray-100 z-10 absolute bottom-[220px] lg:bottom-[10%] left-4 lg:left-0">
                        <div class="flex items-center gap-2 mb-3 text-[13px] font-bold text-foreground">
                            <!-- Link icon (WhatsApp approximation) -->
                            <svg class="text-[#1D9C50]" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                            Link de pago enviado
                        </div>
                        <p class="text-gray-500 text-[12px] leading-relaxed mb-4">Tu cliente paga con tarjeta antes de que salga el pedido.</p>
                        
                        <div class="bg-[#E6F8ED] text-[#1D9C50] text-[11px] font-bold px-2.5 py-1 rounded-md inline-flex items-center gap-1.5 shrink-0">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            Pagado · $312
                        </div>
                    </div>

                    <!-- Bottom Right Card (SPEI) -->
                    <div class="bg-[#15171C] rounded-[20px] shadow-2xl p-6 w-[300px] lg:w-[320px] text-left border border-gray-800 z-30 absolute bottom-4 right-4 lg:bottom-[10%] lg:right-[5%]">
                        <div class="flex justify-between items-start mb-3">
                            <h4 class="text-white text-[13px] font-bold">Depósito recurrente · SPEI</h4>
                            <div class="text-[#1D9C50]">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                        </div>
                        <p class="text-white font-black text-3xl mb-4 tracking-tight">$8,240</p>
                        <div class="flex items-center gap-2 text-[11px] text-gray-400">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                            Comprobante de transferencia adjunto
                        </div>
                    </div>
                    
                </div>
            </div>

            <!-- Two info cards -->
            <div class="w-full max-w-[1000px] mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 mt-12 md:mt-16 text-left">
                <!-- Card 1 (Efectivo) -->
                <div class="bg-[#F8F9FA] border border-gray-200 rounded-[20px] p-8 md:p-10 shadow-[0_6px_0_0_#111315]">
                    <h3 class="font-display font-black text-[18px] md:text-[20px] mb-4 text-foreground">Cobro en efectivo</h3>
                    <p class="text-gray-500 text-[14px] leading-[1.6] mb-6">
                        El repartidor cobra al entregar, sin pedirte nada a ti. Tu saldo se libera de forma recurrente y lo retiras por SPEI con comprobante. Pedidos en efectivo hasta $1,000.
                    </p>
                    <a href="#" class="font-bold text-[13px] text-foreground hover:underline inline-flex items-center gap-1.5">
                        Cómo funciona el efectivo <span class="text-lg leading-none">→</span>
                    </a>
                </div>

                <!-- Card 2 (Tarjeta) -->
                <div class="bg-white border border-gray-100 rounded-[20px] p-8 md:p-10 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.1)]">
                    <h3 class="font-display font-black text-[18px] md:text-[20px] mb-4 text-foreground">Tarjeta y transferencia</h3>
                    <p class="text-gray-500 text-[14px] leading-[1.6] mb-6">
                        Para tickets grandes, tu cliente recibe un link de pago seguro por WhatsApp y el pedido sale cuando ya está pagado. ¿Te transfieren directo? Recibes de tu cliente al instante y el envío se descuenta de tu saldo Tootli.
                    </p>
                    <a href="#" class="font-bold text-[13px] text-foreground hover:underline inline-flex items-center gap-1.5">
                        Ver formas de pago <span class="text-lg leading-none">→</span>
                    </a>
                </div>
            </div>
        </section>

        <section id="como-funciona" class="w-full bg-white border-t border-site-hairline md:border-none mt-12 md:mt-16 mb-16 md:mb-24 scroll-mt-24">
            <div class="w-full px-5 py-16 md:py-24 lg:px-10 max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                    <!-- Left Side: Channels List -->
                    <div class="flex flex-col">
                        <h2 class="font-display font-black uppercase text-4xl md:text-5xl leading-[1.05] tracking-tight text-foreground mb-12">
                            FUNCIONA CON TUS <br class="hidden md:inline">
                            CANALES DE SIEMPRE
                        </h2>
                        
                        <ul class="flex flex-col gap-6 mb-10">
                            <li class="flex items-center gap-4 py-3 border-b border-gray-100">
                                <div class="text-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                </div>
                                <p class="text-[15px]"><span class="font-bold text-foreground">WhatsApp</span> <span class="text-gray-500 ml-1">Pedidos por chat, como siempre</span></p>
                            </li>
                            <li class="flex items-center gap-4 py-3 border-b border-gray-100">
                                <div class="text-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                </div>
                                <p class="text-[15px]"><span class="font-bold text-foreground">Teléfono</span> <span class="text-gray-500 ml-1">Los captura tu equipo en segundos</span></p>
                            </li>
                            <li class="flex items-center gap-4 py-3 border-b border-gray-100">
                                <div class="text-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                </div>
                                <p class="text-[15px]"><span class="font-bold text-foreground">Mostrador</span> <span class="text-gray-500 ml-1">Para llevar o a domicilio</span></p>
                            </li>
                            <li class="flex items-center gap-4 py-3 border-b border-gray-100">
                                <div class="text-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"></rect><rect width="7" height="7" x="14" y="3" rx="1"></rect><rect width="7" height="7" x="14" y="14" rx="1"></rect><rect width="7" height="7" x="3" y="14" rx="1"></rect></svg>
                                </div>
                                <p class="text-[15px]"><span class="font-bold text-foreground">Menú digital</span> <span class="text-gray-500 ml-1">Con Tootli Direct, pedidos automáticos</span></p>
                            </li>
                        </ul>

                        <div>
                            <a href="{{ route('restaurant.create') }}" class="inline-flex items-center justify-center gap-2 bg-[#FFD600] text-black font-bold px-8 py-3.5 rounded-lg shadow-sm hover:bg-[#F0C900] transition-colors">
                                Empieza ya
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Right Side: Pills List -->
                    <div class="flex flex-col gap-4">
                        <!-- Pill 1 -->
                        <div class="bg-gray-50 rounded-full px-5 py-4 md:py-5 flex items-center gap-4 border border-transparent hover:border-gray-200 transition-colors shadow-sm">
                            <div class="w-10 h-10 shrink-0 bg-white rounded-full flex items-center justify-center shadow-sm">
                                <span class="font-black text-lg">%</span>
                            </div>
                            <span class="font-bold text-[13px] md:text-[14px] uppercase tracking-wide text-foreground">0% DE COMISIÓN SOBRE TUS VENTAS</span>
                        </div>

                        <!-- Pill 2 -->
                        <div class="bg-gray-50 rounded-full px-5 py-4 md:py-5 flex items-center gap-4 border border-transparent hover:border-gray-200 transition-colors shadow-sm">
                            <div class="w-10 h-10 shrink-0 bg-white rounded-full flex items-center justify-center shadow-sm text-foreground">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
                            </div>
                            <span class="font-bold text-[13px] md:text-[14px] uppercase tracking-wide text-foreground">EFECTIVO, TARJETA Y TRANSFERENCIA</span>
                        </div>

                        <!-- Pill 3 -->
                        <div class="bg-gray-50 rounded-full px-5 py-4 md:py-5 flex items-center gap-4 border border-transparent hover:border-gray-200 transition-colors shadow-sm">
                            <div class="w-10 h-10 shrink-0 bg-white rounded-full flex items-center justify-center shadow-sm text-foreground">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"></path><path d="M3 10h18"></path><path d="M5 6l7-3 7 3"></path><path d="M4 10v11"></path><path d="M20 10v11"></path><path d="M8 14v3"></path><path d="M12 14v3"></path><path d="M16 14v3"></path></svg>
                            </div>
                            <span class="font-bold text-[13px] md:text-[14px] uppercase tracking-wide text-foreground">DEPÓSITO RECURRENTES POR SPEI, CON COMPROBANTE</span>
                        </div>

                        <!-- Pill 4 (Dark) -->
                        <div class="bg-[#111315] rounded-full px-5 py-4 md:py-5 flex items-center gap-4 shadow-xl mt-2">
                            <div class="w-10 h-10 shrink-0 bg-[#FFD600] rounded-full flex items-center justify-center text-black shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"></path><path d="M7 7h.01"></path></svg>
                            </div>
                            <span class="font-bold text-[13px] md:text-[14px] uppercase tracking-wide text-white">DESDE $38.00, DIVIDE EL ENVÍO CON TU CLIENTE</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Calculator Section (Dark Theme) -->
        <section id="precios" class="w-full bg-[#15171C] text-white px-5 py-20 md:py-32 lg:px-10 overflow-hidden scroll-mt-24">
            <div class="max-w-6xl mx-auto flex flex-col items-center">
                
                <div class="text-center mb-12">
                    <p class="text-[#FFD600] font-bold text-xs md:text-sm tracking-[2px] uppercase mb-4">
                        Porcentaje vs Tarifa Fija
                    </p>
                    <h2 class="font-display font-black uppercase text-3xl md:text-5xl lg:text-[64px] leading-[1.05] tracking-tight text-white mb-6">
                        ENTRE MÁS VENDES, <br class="hidden md:inline">
                        MÁS TE QUITAN. AQUÍ NO.
                    </h2>
                    <p class="text-gray-400 text-sm md:text-[17px] leading-relaxed max-w-2xl mx-auto">
                        Una app cobra un porcentaje de cada venta. Tootli Directo cobra una tarifa fija por entrega, y tú decides cuánto absorbes.
                    </p>
                </div>

                <!-- Interactive App -->
                <div class="w-full max-w-4xl" x-data="calculatorApp()">
                    
                    <!-- Tabs -->
                    <div class="flex justify-center mb-12">
                        <div class="bg-[#1C1F26] rounded-full p-1.5 flex items-center gap-2">
                            <button @click="setVenta(250)" :class="venta === 250 ? 'bg-[#FFD600] text-black' : 'text-gray-400 hover:text-white'" class="px-6 py-2 rounded-full font-bold text-sm transition-colors">
                                $250
                            </button>
                            <button @click="setVenta(500)" :class="venta === 500 ? 'bg-[#FFD600] text-black' : 'text-gray-400 hover:text-white'" class="px-6 py-2 rounded-full font-bold text-sm transition-colors">
                                $500
                            </button>
                            <button @click="setVenta(1000)" :class="venta === 1000 ? 'bg-[#FFD600] text-black' : 'text-gray-400 hover:text-white'" class="px-6 py-2 rounded-full font-bold text-sm transition-colors">
                                $1,000
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 items-start mt-8">
                        
                        <!-- Receipt Mockup -->
                        <div class="relative w-full max-w-[380px] mx-auto filter drop-shadow-2xl">
                            <!-- Receipt Body -->
                            <div class="bg-[#FCFBF7] rounded-t-sm px-6 pt-10 pb-8 text-[#111111] font-mono relative z-10" style="border-radius: 4px 4px 0 0;">
                                
                                <div class="text-center mb-6">
                                    <h4 class="font-bold tracking-[0.2em] text-lg font-sans mb-1">TU RESTAURANTE</h4>
                                    <p class="text-gray-500 text-xs tracking-wider">cuenta del pedido</p>
                                </div>
                                
                                <div class="border-t border-dashed border-gray-400 opacity-50 my-4"></div>
                                
                                <div class="flex justify-between items-center mb-3 text-[14px]">
                                    <span>Venta</span>
                                    <span class="font-semibold" x-text="'$' + venta"></span>
                                </div>
                                <div class="flex justify-between items-center mb-3 text-[14px]">
                                    <span>Envío tarifa fija</span>
                                    <span class="font-semibold">$38</span>
                                </div>
                                <div class="flex justify-between items-center mb-3 text-[14px]">
                                    <span>Lo paga tu cliente</span>
                                    <span class="font-semibold" x-text="'-$' + clientePaga"></span>
                                </div>
                                <div class="flex justify-between items-center mb-3 text-[14px] font-bold">
                                    <span>Lo pones tú</span>
                                    <span x-text="'$' + (38 - clientePaga)"></span>
                                </div>
                                
                                <div class="border-t border-dashed border-gray-400 opacity-50 my-4"></div>
                                
                                <div class="flex justify-between items-center mb-6 text-lg font-bold font-sans tracking-wide">
                                    <span>TE QUEDA</span>
                                    <span class="text-2xl" x-text="'$' + teQueda"></span>
                                </div>

                                <div class="border-t border-dashed border-gray-400 opacity-50 my-4"></div>
                                
                                <div class="flex justify-between items-center text-[13px] font-bold text-[#D93F3C] mb-4">
                                    <span>En apps de comida (30%)</span>
                                    <span x-text="'$' + Math.round(venta * 0.70)"></span>
                                </div>

                                <div class="bg-[#E6F8ED] text-[#1D9C50] rounded px-4 py-2.5 text-center font-bold text-[13px] tracking-wide mt-2">
                                    CON TOOTLI AHORRAS $<span x-text="ahorro"></span>
                                </div>

                                <div class="text-center mt-5 text-[11px] text-gray-400 tracking-wider">
                                    comisión: $0 &bull; gracias
                                </div>
                            </div>
                            
                            <!-- Receipt Zig-Zag Bottom Edge -->
                            <div class="h-3 w-full relative z-10" style="background: linear-gradient(-45deg, transparent 33.33%, #FCFBF7 33.33%, #FCFBF7 66.66%, transparent 66.66%), linear-gradient(45deg, transparent 33.33%, #FCFBF7 33.33%, #FCFBF7 66.66%, transparent 66.66%); background-size: 10px 14px; background-repeat: repeat-x;"></div>
                        </div>

                        <!-- Controls -->
                        <div class="flex flex-col gap-6 pt-2">
                            <div class="bg-[#1C1F26] rounded-2xl p-6 lg:p-8 border border-white/5 shadow-2xl">
                                <h3 class="font-bold text-[17px] mb-8 tracking-wide">¿Quién paga el envío de $38?</h3>
                                
                                <div class="relative w-full mb-10">
                                    <input type="range" min="0" max="38" x-model="clientePaga" class="w-full h-1.5 bg-gray-600 rounded-lg appearance-none cursor-pointer accent-[#FFD600]">
                                    <style>
                                        input[type="range"]::-webkit-slider-thumb {
                                            -webkit-appearance: none;
                                            width: 22px;
                                            height: 22px;
                                            background: white;
                                            border: 4px solid #FFD600;
                                            border-radius: 50%;
                                            cursor: pointer;
                                            box-shadow: 0 0 0 2px rgba(255, 214, 0, 0.2);
                                        }
                                        input[type="range"]::-moz-range-thumb {
                                            width: 22px;
                                            height: 22px;
                                            background: white;
                                            border: 4px solid #FFD600;
                                            border-radius: 50%;
                                            cursor: pointer;
                                        }
                                    </style>
                                </div>

                                <div class="flex justify-between items-end mb-8">
                                    <div>
                                        <p class="text-gray-400 text-[13px] mb-1">Tu cliente paga</p>
                                        <p class="font-bold text-[28px] leading-none text-white font-sans" x-text="'$' + clientePaga"></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-gray-400 text-[13px] mb-1">Tú pones</p>
                                        <p class="font-bold text-[28px] leading-none text-[#FFD600] font-sans" x-text="'$' + (38 - clientePaga)"></p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2.5">
                                    <button @click="clientePaga = 38" class="px-5 py-2 rounded-full border border-[#33353A] bg-transparent text-[13px] font-semibold text-gray-300 hover:bg-white/5 hover:text-white transition-colors">
                                        Cliente paga todo
                                    </button>
                                    <button @click="clientePaga = 19" class="px-5 py-2 rounded-full border border-[#33353A] bg-transparent text-[13px] font-semibold text-gray-300 hover:bg-white/5 hover:text-white transition-colors">
                                        Mitad y mitad
                                    </button>
                                    <button @click="clientePaga = 0" class="px-5 py-2 rounded-full border border-[#33353A] bg-transparent text-[13px] font-semibold text-gray-300 hover:bg-white/5 hover:text-white transition-colors">
                                        Envío gratis
                                    </button>
                                </div>
                            </div>
                            
                            <p class="text-gray-400 text-[14.5px] leading-relaxed px-1 font-medium mt-2">
                                Mueves el reparto en cada pedido y el ticket cambia al instante. Tu tarifa nunca crece con la venta, y en las apps tu cliente también paga envío y cuota de servicio.
                            </p>

                            <div class="mt-4 px-1">
                                <a href="{{ route('restaurant.create') }}" class="inline-block px-7 py-3.5 rounded-lg border border-white text-white font-bold text-[15px] hover:bg-white hover:text-black transition-colors">
                                    Haz tu cuenta con nosotros
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Flotillas Section Placeholder -->
        <section id="flotillas" class="scroll-mt-24">
            <!-- Espacio reservado para la sección de Flotillas -->
        </section>

    </main>

    <!-- Footer -->
    <footer class="w-full bg-background border-t border-site-hairline py-8">
        <div class="max-w-[1220px] mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-site-muted text-sm font-medium">
                &copy; {{ date('Y') }} Tootli Directo. Todos los derechos reservados.
            </p>
            <div class="flex gap-6 text-sm font-medium">
                <a href="{{ route('terms-and-conditions') }}" class="text-site-muted hover:text-foreground transition-colors">Términos y condiciones</a>
                <a href="{{ route('privacy-policy') }}" class="text-site-muted hover:text-foreground transition-colors">Aviso de privacidad</a>
            </div>
        </div>
    </footer>

</body>
</html>
