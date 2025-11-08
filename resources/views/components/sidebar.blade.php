@php
    $user = session('user');
    $isSuperAdmin = $user && $user->role === 'superadmin';
    $isAdminOPD = $user && $user->role === 'admin_opd';
@endphp

<div id="sidebar" class="sidebar bg-blue-900 text-white w-72 flex-shrink-0 hidden md:block overflow-y-auto">
    <div class="flex items-center justify-center h-20 border-b-10 border-blue-700 bg-blue-900">
    <a class="navbar-brand flex justify-center items-center w-full" href="#">
        <img src="{{ asset('assets/logo_baru.png') }}" alt="Logo OPD" class="logo-img">
    </a>
</div>
    
    <div class="py-2">
        <nav>
            <!-- DASHBOARD -->
            <a href="{{ route('dashboard') }}" class="sidebar-category {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="text-sm font-bold">DASHBOARD</span>
            </a>

            <!-- DATA MASTER - Hanya untuk Superadmin -->
            @if($isSuperAdmin)
            <div class="sidebar-category dropdown-toggle {{ in_array(Route::currentRouteName(), ['opd.master', 'admin-opd.index']) ? 'active-parent' : '' }}" 
                 data-target="dataMaster">
                <span class="text-sm">DATA MASTER</span>
            </div>
            <div id="dataMaster" class="dropdown-content {{ in_array(Route::currentRouteName(), ['opd.master', 'admin-opd.index']) ? 'show' : '' }}">
                <a href="{{ route('admin-opd.index') }}" class="sidebar-item {{ request()->routeIs('admin-opd.index') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                    </svg>
                    <span class="text-sm">MASTER ADMIN OPD</span>
                </a>
                <a href="{{ route('opd.master') }}" class="sidebar-item {{ request()->routeIs('opd.master') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 -960 960 960" fill="currentColor">
                        <path d="M120-120v-560h160v-160h400v320h160v400H520v-160h-80v160H120Zm80-80h80v-80h-80v80Zm0-160h80v-80h-80v80Zm0-160h80v-80h-80v80Zm160 160h80v-80h-80v80Zm0-160h80v-80h-80v80Zm0-160h80v-80h-80v80Zm160 320h80v-80h-80v80Zm0-160h80v-80h-80v80Zm0-160h80v-80h-80v80Zm160 480h80v-80h-80v80Zm0-160h80v-80h-80v80Z"/>
                    </svg>
                    <span class="text-sm">MASTER OPD</span>
                </a>
            </div>
            @endif
            
            <!-- DATA SURVEY - Semua role bisa akses -->
            <div class="sidebar-category dropdown-toggle {{ in_array(Route::currentRouteName(), ['aplikasi.master']) ? 'active-parent' : '' }}" 
                 data-target="dataSurvey">
                <span class="text-sm">DATA SURVEY</span>
            </div>
            <div id="dataSurvey" class="dropdown-content {{ in_array(Route::currentRouteName(), ['aplikasi.master']) ? 'show' : '' }}">
                <a href="{{ route('aplikasi.master') }}" class="sidebar-item {{ request()->routeIs('aplikasi.master') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.660.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                    </svg>
                    <span class="text-sm">APLIKASI DAN SURVEY</span>
                </a>
            </div>
            
            <!-- DATA KUESIONER - Hanya untuk Superadmin -->
            @if($isSuperAdmin)
            <div class="sidebar-category dropdown-toggle {{ in_array(Route::currentRouteName(), ['kuesioner.index', 'kategori-kuesioner.index']) ? 'active-parent' : '' }}" 
                data-target="dataKuesioner">
                <span class="text-sm">DATA KUESIONER</span>
            </div>
            <div id="dataKuesioner" class="dropdown-content {{ in_array(Route::currentRouteName(), ['kuesioner.index','kategori-kuesioner.index']) ? 'show' : '' }}">
                <a href="{{ route('kuesioner.index') }}" class="sidebar-item {{ request()->routeIs('kuesioner.index') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                    </svg>
                    <span class="text-sm">LIST KUESIONER</span>
                </a>
                <a href="{{ route('kategori-kuesioner.index') }}" class="sidebar-item {{ request()->routeIs('kategori-kuesioner.index') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 -960 960 960" fill="currentColor">
                        <path d="m260-520 220-360 220 360H260ZM700-80q-75 0-127.5-52.5T520-260q0-75 52.5-127.5T700-440q75 0 127.5 52.5T880-260q0 75-52.5 127.5T700-80Zm-580-20v-320h320v320H120Zm580-60q42 0 71-29t29-71q0-42-29-71t-71-29q-42 0-71 29t-29 71q0 42 29 71t71 29Zm-500-20h160v-160H200v160Zm202-420h156l-78-126-78 126Zm78 0ZM360-340Zm340 80Z"/>
                    </svg>
                    <span class="text-sm">KATEGORI KUESIONER</span>
                </a>
            </div>
            @endif

            <!-- DATA ANALISA - Semua role bisa akses -->
            <div class="sidebar-category dropdown-toggle {{ in_array(Route::currentRouteName(), ['analisis.index']) ? 'active-parent' : '' }}" 
                 data-target="dataAnalisa">
                <span class="text-sm">DATA ANALISA</span>
            </div>
            <div id="dataAnalisa" class="dropdown-content {{ in_array(Route::currentRouteName(), ['analisis.index']) ? 'show' : '' }}">
                <a href="{{ route('analisis.index') }}" class="sidebar-item {{ request()->routeIs('analisis.index') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                        <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
                    </svg>
                    <span class="text-sm">DETAIL ANALISA</span>
                </a>
            </div>
        </nav>
    </div>
</div>

<script src="/js/components.js" defer></script>