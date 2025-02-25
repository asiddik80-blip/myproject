<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <!-- Profil Pengguna -->
    <li class="nav-item nav-profile">
      <a href="#" class="nav-link">
        <div class="profile-image">
          <img class="img-xs rounded-circle" 
               src="stellar/dist/assets/images/faces/face8.jpg" 
               alt="profile image">
          <div class="dot-indicator bg-success"></div>
        </div>
        <div class="text-wrapper">
          <p class="profile-name">Raouda Moufida</p>
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
        <li class="nav-item">
          <a class="nav-link" href="#">
            <span class="menu-title">{{ $tombol['nama'] }}</span>
            <i class="icon-screen-desktop menu-icon"></i>
          </a>
        </li>
      @endforeach
    @endforeach
  </ul>
</nav>
