<style>
    /* ===================================================
       CUSTOM SIDEBAR THEMING - UPTD PEMANFAATAN BMD JABAR
       =================================================== */

    /* 1. Sidebar Container: Modern Slate-to-Emerald Subtle Gradient */
    aside.fi-sidebar {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 60%, #ecfdf5 100%) !important;
        border-right: 1px solid rgba(16, 185, 129, 0.18) !important;
        box-shadow: 4px 0 24px -4px rgba(0, 0, 0, 0.03) !important;
    }

    /* 2. Brand Header: Clean Emerald Identity */
    header.fi-sidebar-header {
        background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%) !important;
        border-bottom: 2px solid #059669 !important;
        box-shadow: 0 2px 8px -2px rgba(5, 150, 105, 0.12) !important;
    }

    header.fi-sidebar-header .fi-logo {
        font-weight: 800 !important;
        color: #065f46 !important;
        font-size: 0.92rem !important;
        line-height: 1.3 !important;
        letter-spacing: -0.02em !important;
    }

    /* 3. Navigation Group Headers with Colored Accents */
    .fi-sidebar-group {
        margin-bottom: 0.5rem !important;
    }

    .fi-sidebar-group-label {
        font-size: 0.68rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.08em !important;
        text-transform: uppercase !important;
        padding: 0.4rem 0.65rem !important;
        border-radius: 0.5rem !important;
        margin-bottom: 0.4rem !important;
        transition: all 0.15s ease !important;
    }

    /* Group: Pelayanan Pemanfaatan (Emerald Theme) */
    .fi-sidebar-group[data-group-label*="Pelayanan"] .fi-sidebar-group-label {
        background: rgba(16, 185, 129, 0.1) !important;
        color: #047857 !important;
        border-left: 3px solid #10b981 !important;
    }

    /* Group: Manajemen BMD (Blue Theme) */
    .fi-sidebar-group[data-group-label*="Manajemen"] .fi-sidebar-group-label {
        background: rgba(59, 130, 246, 0.1) !important;
        color: #1d4ed8 !important;
        border-left: 3px solid #3b82f6 !important;
    }

    /* Group: Pengaturan (Violet Theme) */
    .fi-sidebar-group[data-group-label*="Pengaturan"] .fi-sidebar-group-label {
        background: rgba(139, 92, 246, 0.1) !important;
        color: #6d28d9 !important;
        border-left: 3px solid #8b5cf6 !important;
    }

    /* 4. Inactive Navigation Items: Color-Coded Icons & Smooth Hover */
    .fi-sidebar-item {
        margin-bottom: 0.15rem !important;
    }

    .fi-sidebar-item-button {
        border-radius: 0.625rem !important;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1) !important;
        border: 1px solid transparent !important;
    }

    .fi-sidebar-item:not(.fi-sidebar-item-active):not(.fi-active) .fi-sidebar-item-button:hover {
        background: #ffffff !important;
        border-color: rgba(16, 185, 129, 0.2) !important;
        box-shadow: 0 2px 6px -1px rgba(0, 0, 0, 0.06) !important;
        transform: translateX(4px) !important;
    }

    /* Distinct Vibrant Icon Colors for Inactive Items */
    /* Dasbor - Emerald */
    .fi-sidebar-item a[href$="/admin"] .fi-sidebar-item-icon {
        color: #059669 !important;
    }

    /* Booking Aset - Amber/Orange */
    .fi-sidebar-item a[href*="/admin/bookings"] .fi-sidebar-item-icon {
        color: #ea580c !important;
    }

    /* Katalog Aset - Blue */
    .fi-sidebar-item a[href*="/admin/assets"]:not([href*="asset-pemanfaatans"]) .fi-sidebar-item-icon {
        color: #2563eb !important;
    }

    /* Data Pemanfaatan / Sewa - Violet */
    .fi-sidebar-item a[href*="/admin/asset-pemanfaatans"] .fi-sidebar-item-icon {
        color: #7c3aed !important;
    }

    /* Master Persyaratan - Teal */
    .fi-sidebar-item a[href*="/admin/booking-requirements"] .fi-sidebar-item-icon {
        color: #0d9488 !important;
    }

    /* 5. Active Navigation Item: Premium Emerald Gradient Pill */
    .fi-sidebar-item.fi-sidebar-item-active .fi-sidebar-item-button,
    .fi-sidebar-item.fi-active .fi-sidebar-item-button {
        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px -1px rgba(5, 150, 105, 0.42) !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
    }

    .fi-sidebar-item.fi-sidebar-item-active .fi-sidebar-item-button .fi-sidebar-item-icon,
    .fi-sidebar-item.fi-active .fi-sidebar-item-button .fi-sidebar-item-icon {
        color: #ffffff !important;
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.2)) !important;
    }

    .fi-sidebar-item.fi-sidebar-item-active .fi-sidebar-item-button .fi-sidebar-item-label,
    .fi-sidebar-item.fi-active .fi-sidebar-item-button .fi-sidebar-item-label {
        color: #ffffff !important;
        font-weight: 700 !important;
        letter-spacing: -0.01em !important;
    }

    /* Active Item Badge (White Pill with Emerald Text) */
    .fi-sidebar-item.fi-sidebar-item-active .fi-sidebar-item-button .fi-badge,
    .fi-sidebar-item.fi-active .fi-sidebar-item-button .fi-badge {
        background: #ffffff !important;
        color: #047857 !important;
        font-weight: 800 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15) !important;
        border: none !important;
    }

    /* 6. Inactive Badges: High Contrast & Vibrant */
    /* Booking Badge (Amber/Orange) */
    .fi-sidebar-item a[href*="/admin/bookings"] .fi-badge {
        background: #fff7ed !important;
        color: #c2410c !important;
        border: 1px solid #fed7aa !important;
        font-weight: 700 !important;
    }

    /* Sewa BMD Warning Badge (Amber) */
    .fi-sidebar-item a[href*="/admin/asset-pemanfaatans"] .fi-badge {
        background: #fefce8 !important;
        color: #a16207 !important;
        border: 1px solid #fef08a !important;
        font-weight: 700 !important;
    }

    /* 7. Refined Scrollbar on Nav */
    .fi-sidebar-nav::-webkit-scrollbar {
        width: 4px;
    }
    .fi-sidebar-nav::-webkit-scrollbar-track {
        background: transparent;
    }
    .fi-sidebar-nav::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, 0.2);
        border-radius: 4px;
    }
    .fi-sidebar-nav::-webkit-scrollbar-thumb:hover {
        background: rgba(16, 185, 129, 0.4);
    }
</style>