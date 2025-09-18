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
    <?php $__currentLoopData = $sidebarMenu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      
         
      <!-- Menu Category -->
      <li class="nav-item nav-category">
        <span class="nav-link"><?php echo e($menu['title']); ?></span>
      </li>
      
      <!-- Menu Buttons -->
      <?php $__currentLoopData = $menu['tombol']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tombol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($tombol['show'] == 1): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo e($tombol['link']); ?>">
              <span class="menu-title"><?php echo e($tombol['nama']); ?></span>
              <i class="<?php echo e($tombol['icon']); ?>"></i>
            </a>
          </li>
        <?php endif; ?>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </ul>
</nav>
<?php /**PATH C:\xampp\htdocs\myproject\resources\views/components/sidebar.blade.php ENDPATH**/ ?>