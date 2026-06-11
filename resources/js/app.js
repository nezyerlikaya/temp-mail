import Alpine from 'alpinejs';
import { createIcons, Inbox, LayoutDashboard, LogOut, Menu } from 'lucide';

window.Alpine = Alpine;

Alpine.start();

createIcons({
    icons: {
        Inbox,
        LayoutDashboard,
        LogOut,
        Menu,
    },
});
