<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
        <img src="{{ asset('bower_components/adminLTE/dist/img/AdminLTELogo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3">
        <span class="brand-text font-weight-light">{{ __('messages.admin') }}</span>
    </a>
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @foreach (config('menu') as $key => $value)
                    <li class="nav-item">
                        <a href="{{ route($value['route']) }}" class="nav-link">
                            <i class="nav-icon fas {{ $value['icon'] }}"></i>
                            <p>{{ __('messages.' . $value['label']) }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
