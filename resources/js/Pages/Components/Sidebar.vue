<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const isOpen = ref(false);

const toggleSidebar = () => {
    isOpen.value = !isOpen.value;
};

const menuIcon = computed(() => (isOpen.value ? 'bx-menu-alt-right' : 'bx-menu'));

const navItems = [
    { name: 'Dashboard', icon: 'bx bxs-dashboard', link: '/admin/dashboard' },
    { name: 'Events', icon: 'bx bxs-calendar', link: '/admin/events' },
    { name: 'Users', icon: 'bx bxs-user-detail', link: '/admin/users' },
    { name: 'Orders', icon: 'bx bxs-receipt', link: '/admin/orders' },
    { name: 'Administrator', icon: 'bx bxs-cog', link: '/admin/administrator' },
];
</script>

<template>
    <nav id="dashboard">
        <div :class="['sidebar', { open: isOpen }]">
            <div class="logo-details">
                <div class="logo_name">Visit Haarlem</div>
                <i :class="['bx', menuIcon]" @click="toggleSidebar" id="btn"></i>
            </div>

            <!-- Sidebar Navigation List -->
            <ul :class="['nav-list', { scroll: isOpen }]">
                <li v-for="item in navItems" :key="item.name">
                    <Link :href="item.link">
                        <i :class="item.icon"></i>
                        <span class="links_name">{{ item.name }}</span>
                    </Link>
                    <span class="tooltip">{{ item.name }}</span>
                </li>
            </ul>

            <!-- Logout Button -->
            <a href="/logout" class="logout">
                <div class="logout">
                    <i class="bx bx-log-out" id="logout"></i>
                </div>
            </a>
        </div>
    </nav>
</template>
<style scoped>
/* Importing Google Fonts and Boxicons */
@import "https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap";
@import "https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css";


/* Sidebar container styling */
.sidebar {
  width: 106px; /* Default sidebar width */
  background: #2565c7;
  padding: 6px 14px;
  transition: width 0.5s ease; /* Smooth transition for width change */
  height: 100vh; /* Full viewport height */
}

.sidebar.open {
  width: 220px;
}

/* Logo details within the sidebar */
.sidebar .logo-details {
  height: 60px;
  display: flex;
  align-items: center;
  position: relative;
}

.sidebar .logo-details .icon {
  opacity: 0;
  transition: opacity 0.5s ease;
}

.sidebar.open .logo-details .icon {
  opacity: 1; /* Shows icon in expanded state */
}

.sidebar .logo-details .logo_name {
  color: #fff;
  font-size: 20px;
  font-weight: 600;
  opacity: 0; /* Hidden by default, shows when sidebar is expanded */
  transition: opacity 0.3s ease;
  margin-left: 30px;
  margin-top: 5px;
}

.sidebar.open .logo-details .logo_name {
  opacity: 1; /* Shows logo name in expanded state */
}

.sidebar .logo-details #btn {
  position: absolute;
  top: 50%;
  right: 20%;
  transform: translateY(-50%);
  font-size: 23px;
  text-align: center;
  cursor: pointer;
  transition: all 0.5s ease;
}

.sidebar.open .logo-details #btn {
  right: 0;
}

/* General icon styling */
.sidebar i {
  color: #fff;
  height: 60px;
  min-width: 50px;
  font-size: 28px;
  text-align: center;
  line-height: 60px; /* Centers icon vertically */
}

/* Sidebar navigation list styling */
.sidebar .nav-list {
  list-style: none;
  padding: 0;
  margin-top: 20px;
}

.sidebar .nav-list li {
  position: relative;
  margin: 8px 8px 8px 0;
}

.sidebar .nav-list li .tooltip {
  position: absolute;
  top: 50%;
  left: 100%;
  transform: translateY(-50%);
  z-index: 3;
  background: #fff;
  box-shadow: 0 5px 10px rgba(0, 0, 0, 0.3);
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 15px;
  font-weight: 400;
  opacity: 0; /* Hidden by default */
  pointer-events: none;
  transition: opacity 0.4s ease;
}

.sidebar .nav-list li:hover .tooltip {
  opacity: 1; /* Shows tooltip on hover */
  pointer-events: auto;
}

.sidebar .nav-list li a {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: #fff;
  padding: 10px;
  border-radius: 12px;
  transition: background 0.4s ease;
}

.sidebar .nav-list li a:hover {
  background: #004fc7; /* Lighter background on hover */
}

.sidebar .nav-list li a .links_name {
  color: #fff;
  font-size: 15px;
  font-weight: 400;
  white-space: nowrap;
  opacity: 0; /* Hidden by default */
  pointer-events: none;
  transition: opacity 0.4s ease, margin-left 0.4s ease;
}

.sidebar.open .nav-list li a .links_name {
  opacity: 1; /* Shows link names when expanded */
  pointer-events: auto;
  margin-left: 10px;
}

.sidebar .nav-list li i {
  height: 50px;
  line-height: 50px;
  font-size: 18px;
  border-radius: 12px;
}

.sidebar .nav-list li input {
  font-size: 15px;
  color: #FFF;
  font-weight: 400;
  outline: none;
  height: 40px;
  width: 40px;
  border: none;
  border-radius: 12px;
  transition: all 0.5s ease;
  background: #2565c7;
  padding-left: 10px;
}

.sidebar.open .nav-list li input {
  width: 200px;
}

.sidebar .nav-list li input::placeholder {
  color: #ddd;
}

.logout {
  position: fixed;
  margin-left: -10px;

  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #004fc7;

  width: inherit;
  bottom: 0;

  font-family: 'Poppins', sans-serif;
  font-weight: bold;
}
</style>
