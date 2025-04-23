<?php
// Header untuk area admin (bagian atas)
$adminUser = $user ?? null;
?>
<header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none lg:hidden">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
    </button>

    <div class="relative flex-1 max-w-xs mr-4 hidden sm:block">
       <?php /*
        <label for="search" class="sr-only">Search</label>
        <input class="w-full border border-gray-300 rounded-md pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
               id="search" name="search" type="search" placeholder="Search...">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
            </svg>
        </div>
         */ ?>
    </div>

    <div class="flex items-center">
        <?php /* Notifikasi (Opsional)
        <button class="relative mx-4 text-gray-600 hover:text-gray-700 focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
             <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">3</span>
        </button>
        */ ?>

        <div x-data="{ dropdownOpen: false }" class="relative">
            <button @click="dropdownOpen = !dropdownOpen" class="relative z-10 block h-8 w-8 rounded-full overflow-hidden shadow focus:outline-none border-2 border-gray-400 focus:border-indigo-500">
                <svg class="h-full w-full object-cover text-gray-500" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </button>

            <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95"
                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20" style="display: none;">
                <div class="px-4 py-3 text-sm text-gray-700">
                    <div><?= htmlspecialchars($adminUser->name ?? 'Admin') ?></div>
                    <div class="font-medium truncate text-gray-500"><?= htmlspecialchars($adminUser->username ?? '') ?></div>
                </div>
                <a href="<?= url_for('/admin/profile/edit') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-500 hover:text-white">Profil</a>
                <a href="<?= url_for('/admin/settings') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-500 hover:text-white">Pengaturan</a>
                 <form action="<?= url_for('/admin/auth/logout') ?>" method="POST" class="block w-full">
                     <?php // Tambahkan CSRF token di sini jika ada ?>
                     <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-red-500 hover:text-white">
                         Logout
                     </button>
                 </form>
            </div>
        </div>
    </div>
    <script src="//unpkg.com/alpinejs" defer></script>
</header>