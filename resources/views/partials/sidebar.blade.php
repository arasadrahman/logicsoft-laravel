<aside class="main-sidebar sidebar-dark-primary">
    <a href="{{ route('dashboard') }}" class="brand-link">
        <img src="{{ auth()->user()->Logo
            ? asset('assets/logo/' . auth()->user()->Logo)
            : asset('assets/img/CLMSLogo.png') }}"
            class="brand-image img-circle">

        <span class="brand-text font-weight-light">
            <b>{{ auth()->user()->ShopName ?? 'POS Software' }}</b>
        </span>
    </a>

    <div class="sidebar">
        <nav class="mt-3">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

                @foreach($sidebarMenu as $menu)

                    @if(isset($menu['header']))
                        <li class="nav-header">{{ $menu['header'] }}</li>
                        @continue
                    @endif

                    @if(isset($menu['group']) && $menu['group'])
                        @php
                            $isOpen = false;
                            foreach ($menu['routes_pattern'] as $pattern) {
                                if (request()->routeIs($pattern)) {
                                    $isOpen = true;
                                    break;
                                }
                            }
                        @endphp

                        <li class="nav-item has-treeview {{ $isOpen ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $isOpen ? 'active' : '' }}">
                                <i class="nav-icon {{ $menu['icon'] }}"></i>
                                <p>
                                    {{ $menu['title'] }}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">
                                @foreach($menu['children'] as $child)
                                    <li class="nav-item">
                                        <a href="{{ route($child['route']) }}"
                                           class="nav-link {{ request()->routeIs($child['route']) ? 'active' : '' }}">
                                            <i class="far {{ request()->routeIs($child['route']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                            <p>{{ $child['title'] }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route($menu['route']) }}"
                               class="nav-link {{ request()->routeIs($menu['route']) ? 'active' : '' }}">
                                <i class="nav-icon {{ $menu['icon'] }}"></i>
                                <p>{{ $menu['title'] }}</p>
                            </a>
                        </li>
                    @endif

                @endforeach

                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="nav-link btn btn-default text-red text-left w-100">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </button>
                    </form>
                </li>

            </ul>
        </nav>
    </div>
</aside>
