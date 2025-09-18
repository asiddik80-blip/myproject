<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <!-- Profil Pengguna -->
    <li class="nav-item nav-profile">
      <a href="#" class="nav-link">
        <div class="text-wrapper">
          <p class="profile-name">Mr. Fuadi</p>
          <p class="designation">Administrator</p>
        </div>
      </a>
    </li>

    <!-- Sidebar dari Array Statis -->
    @foreach($sidebarMenu as $menu)
      
         
      <!-- Menu Category -->
      <li class="nav-item nav-category">
        <span class="nav-link">{{ $menu['title'] }}</span>
      </li>
      
      <!-- Menu Buttons -->
      @foreach($menu['tombol'] as $tombol)
        @if($tombol['show'] == 1)
          <li class="nav-item">
            <a class="nav-link" href="{{ $tombol['link'] }}">
              <span class="menu-title">{{ $tombol['nama'] }}</span>
              <i class="{{ $tombol['icon'] }}"></i>
            </a>
          </li>
        @endif
      @endforeach
    @endforeach
  </ul>
</nav>
