<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar (sidebar toggle) -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>

    <!-- Right navbar (user menu) -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <img
                    src="{{ auth()->user()->Logo
                        ? asset('assets/logo/' . auth()->user()->Logo)
                        : asset('assets/img/CLMSLogo.png') }}"
                    class="user-image img-circle"
                    alt="User Image">
            </a>

            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <li class="user-header bg-primary">
                    <img
                        src="{{ auth()->user()->Logo
                            ? asset('assets/logo/' . auth()->user()->Logo)
                            : asset('assets/img/CLMSLogo.png') }}"
                        class="img-circle"
                        alt="User Image">

                    <p>{{ auth()->user()->ShopName }}</p>
                </li>

                <li class="user-footer">
                    <a href="{{ route('billing') }}" class="btn btn-default btn-flat float-left">
                        Billing
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="float-right">
                        @csrf
                        <button type="submit" class="btn btn-default btn-flat">
                            Logout
                        </button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav>
