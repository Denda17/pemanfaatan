<style>
    /* =================================================================
       CLEAN MODERN GOVTECH & FULL-VIEWPORT TV/MIRRORING FIT
       Self-contained CSS (independent of Vite arbitrary classes)
       ================================================================= */

    /* 1. App Canvas & Overflow Lock */
    html,
    body.fi-body {
        background-color: #f8fafc !important;
        color: #0f172a !important;
        max-width: 100vw !important;
        overflow-x: hidden !important;
    }

    .fi-layout,
    .fi-main-ctn,
    main.fi-main {
        min-width: 0 !important;
        max-width: 100% !important;
    }

    @media (min-width: 1024px) {
        main.fi-main {
            padding: 1rem 1.35rem 1.5rem !important;
        }
    }

    /* 2. Topbar & Custom Topbar Buttons */
    .fi-topbar > nav {
        background-color: rgba(255, 255, 255, 0.96) !important;
        backdrop-filter: blur(10px) !important;
        border-bottom: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
        min-height: 4rem !important;
    }

    .sip-topbar-actions {
        display: none;
        align-items: center;
        gap: 0.5rem;
        margin-right: 0.5rem;
    }

    @media (min-width: 640px) {
        .sip-topbar-actions {
            display: flex;
        }
    }

    .sip-topbar-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.4rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.2;
        color: #475569;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .sip-topbar-btn:hover {
        background-color: #ecfdf5;
        color: #047857;
        border-color: #a7f3d0;
    }

    .sip-topbar-btn-active {
        background-color: #059669 !important;
        color: #ffffff !important;
        border-color: #047857 !important;
    }

    .sip-topbar-icon {
        width: 0.875rem;
        height: 0.875rem;
        flex-shrink: 0;
    }

    /* 3. Sidebar Container & Brand Header (No Clipping / No Stacking!) */
    aside.fi-sidebar {
        background: #ffffff !important;
        border-right: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
    }

    header.fi-sidebar-header {
        background: #ffffff !important;
        border-bottom: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
        min-height: 4rem !important;
        height: auto !important;
        padding: 0.65rem 1rem !important;
        display: flex !important;
        align-items: center !important;
    }

    header.fi-sidebar-header .fi-logo {
        height: auto !important;
        display: flex !important;
        align-items: center !important;
        min-width: 0 !important;
    }

    .sip-brand {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        min-width: 0;
    }

    .sip-brand-mark {
        width: 2.25rem;
        height: 2.25rem;
        padding: 0.25rem;
        border-radius: 0.625rem;
        background-color: #f0fdf4;
        border: 1px solid #bbf7d0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sip-brand-mark img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .sip-brand-copy {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
    }

    @media (min-width: 1024px) {
        aside.fi-sidebar:not(.fi-sidebar-open) .sip-brand-copy {
            display: none !important;
        }
    }

    .sip-brand-title-row {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        white-space: nowrap;
    }

    .sip-brand-title {
        font-size: 0.875rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .sip-brand-pill {
        font-size: 0.6rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        padding: 0.12rem 0.4rem;
        border-radius: 9999px;
        background-color: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        line-height: 1.1;
    }

    .sip-brand-subtitle {
        font-size: 0.7rem;
        font-weight: 600;
        color: #64748b;
        line-height: 1.25;
        margin-top: 0.12rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* 4. Clean Uniform Navigation Group Labels & Items */
    .fi-sidebar-nav {
        padding-top: 0.75rem !important;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
        row-gap: 0.75rem !important;
    }

    .fi-sidebar-group-button {
        padding: 0.25rem 0.5rem !important;
    }

    .fi-sidebar-group-label {
        font-size: 0.68rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.06em !important;
        text-transform: uppercase !important;
        color: #64748b !important;
        white-space: nowrap !important;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
    }

    .fi-sidebar-item-button {
        border-radius: 0.5rem !important;
        padding: 0.45rem 0.65rem !important;
        transition: background-color 0.15s ease, color 0.15s ease !important;
    }

    .fi-sidebar-item:not(.fi-sidebar-item-active):not(.fi-active) .fi-sidebar-item-icon {
        color: #64748b !important;
    }

    .fi-sidebar-item:not(.fi-sidebar-item-active):not(.fi-active) .fi-sidebar-item-label {
        color: #334155 !important;
        font-weight: 500 !important;
        font-size: 0.85rem !important;
        white-space: nowrap !important;
    }

    .fi-sidebar-item:not(.fi-sidebar-item-active):not(.fi-active) .fi-sidebar-item-button:hover {
        background-color: #f1f5f9 !important;
    }

    .fi-sidebar-item:not(.fi-sidebar-item-active):not(.fi-active) .fi-sidebar-item-button:hover .fi-sidebar-item-icon,
    .fi-sidebar-item:not(.fi-sidebar-item-active):not(.fi-active) .fi-sidebar-item-button:hover .fi-sidebar-item-label {
        color: #0f172a !important;
    }

    .fi-sidebar-item.fi-sidebar-item-active .fi-sidebar-item-button,
    .fi-sidebar-item.fi-active .fi-sidebar-item-button {
        background-color: #ecfdf5 !important;
        box-shadow: inset 3px 0 0 #059669 !important;
    }

    .fi-sidebar-item.fi-sidebar-item-active .fi-sidebar-item-button .fi-sidebar-item-icon,
    .fi-sidebar-item.fi-active .fi-sidebar-item-button .fi-sidebar-item-icon {
        color: #059669 !important;
    }

    .fi-sidebar-item.fi-sidebar-item-active .fi-sidebar-item-button .fi-sidebar-item-label,
    .fi-sidebar-item.fi-active .fi-sidebar-item-button .fi-sidebar-item-label {
        color: #065f46 !important;
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        white-space: nowrap !important;
    }

    /* 5. Compact Page Header & Vertical Section Rhythm */
    .fi-header {
        padding-top: 0 !important;
        gap: 0.25rem !important;
    }

    .fi-header-heading {
        font-size: 1.35rem !important;
        line-height: 1.3 !important;
    }

    .fi-page > section,
    .fi-dashboard-page > section {
        row-gap: 0.9rem !important;
    }

    /* 6. Stats Overview Cards — Auto-Fit Single Row on Desktop & Mirrored TV */
    .fi-wi-stats-overview {
        row-gap: 0.45rem !important;
    }

    .fi-wi-stats-overview-header-heading {
        font-size: 0.92rem !important;
    }

    .fi-wi-stats-overview-header-description {
        font-size: 0.76rem !important;
    }

    .fi-wi-stats-overview-stats-ctn {
        gap: 0.65rem !important;
    }

    @media (min-width: 1024px) {
        .fi-wi-stats-overview-stats-ctn {
            grid-template-columns: repeat(auto-fit, minmax(145px, 1fr)) !important;
        }
    }

    .fi-wi-stats-overview-stat {
        padding: 0.75rem 0.9rem !important;
        border-radius: 0.75rem !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 2px 0 rgba(15, 23, 42, 0.03) !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease !important;
    }

    .fi-wi-stats-overview-stat-value {
        font-size: 1.3rem !important;
        line-height: 1.2 !important;
    }

    .fi-wi-stats-overview-stat-label {
        font-size: 0.76rem !important;
    }

    .fi-wi-stats-overview-stat-description {
        font-size: 0.7rem !important;
    }

    a.fi-wi-stats-overview-stat:hover {
        border-color: #cbd5e1 !important;
        box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.06) !important;
        transform: translateY(-1px) !important;
    }

    /* 7. Tables & Tabs — Zero Horizontal Scrollbar on Desktop / TV Mirroring */
    .fi-section,
    .fi-ta-ctn,
    .fi-fo-tabs {
        border-radius: 0.75rem !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 2px 0 rgba(15, 23, 42, 0.03) !important;
    }

    .fi-tabs {
        display: flex !important;
        flex-wrap: wrap !important;
        overflow-x: visible !important;
        row-gap: 0.25rem !important;
        border-radius: 0.75rem !important;
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        padding: 0.25rem !important;
    }

    .fi-tabs-item {
        padding: 0.35rem 0.65rem !important;
        font-size: 0.78rem !important;
    }

    @media (min-width: 1024px) {
        .fi-ta-content {
            overflow-x: visible !important;
        }

        .fi-ta-table {
            width: 100% !important;
            table-layout: auto !important;
        }

        .fi-ta-header-cell,
        .fi-ta-cell {
            padding-left: 0.6rem !important;
            padding-right: 0.6rem !important;
            white-space: normal !important;
            word-break: break-word !important;
        }

        .fi-ta-cell .fi-badge,
        .fi-ta-actions {
            white-space: nowrap !important;
        }
    }

    .fi-ta-header-cell {
        background-color: #f8fafc !important;
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
        font-size: 0.68rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        color: #475569 !important;
    }

    .fi-ta-text {
        padding-top: 0.45rem !important;
        padding-bottom: 0.45rem !important;
    }

    .fi-ta-header {
        padding: 0.65rem 1rem !important;
    }

    .fi-ta-row:hover {
        background-color: #f8fafc !important;
    }

    /* 8. Mode Layar TV / Presentation Full-Bleed Mode */
    body.tv-presentation-mode aside.fi-sidebar {
        display: none !important;
    }

    body.tv-presentation-mode main.fi-main {
        padding: 0.75rem 1.5rem 1rem !important;
        max-width: 100vw !important;
    }

    /* 9. Subtle Scrollbar */
    .fi-sidebar-nav::-webkit-scrollbar {
        width: 4px;
    }
    .fi-sidebar-nav::-webkit-scrollbar-track {
        background: transparent;
    }
    .fi-sidebar-nav::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
</style>
