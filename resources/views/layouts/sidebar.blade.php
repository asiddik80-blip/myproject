<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="index.html">
            <span class="align-middle">KBIH Aceh Tamiang</span>
        </a>

        <ul class="sidebar-nav">
            <li class="sidebar-header">Pages</li>

            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                    <i class="align-middle" data-feather="sliders"></i> 
                    <span class="align-middle">Dasbor</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('sejarah') }}" :active="request()->routeIs('sejarah')">
                    <i class="align-middle" data-feather="user"></i> 
                    <span class="align-middle">{{ __('Sejarah') }}</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a class="sidebar-link" href="pages-sign-in.html">
                    <i class="align-middle" data-feather="log-in"></i> 
                    <span class="align-middle">Madinah</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a class="sidebar-link" href="pages-sign-up.html">
                    <i class="align-middle" data-feather="user-plus"></i> 
                    <span class="align-middle">Peserta</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a class="sidebar-link" href="pages-blank.html">
                    <i class="align-middle" data-feather="book"></i> 
                    <span class="align-middle">Manasik</span>
                </a>
            </li>

            <li class="sidebar-header">Umroh</li>

            <li class="sidebar-item">
                <a class="sidebar-link" href="ui-buttons.html">
                    <i class="align-middle" data-feather="square"></i> 
                    <span class="align-middle">Buttons</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a class="sidebar-link" href="maps-google.html">
                    <i class="align-middle" data-feather="map"></i> 
                    <span class="align-middle">Maps</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
