<div class="sip-topbar-actions" x-data="{
    tvMode: false,
    toggleTvMode() {
        this.tvMode = !this.tvMode;
        document.body.classList.toggle('tv-presentation-mode', this.tvMode);
        if (this.tvMode && !document.fullscreenElement && document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen().catch(() => {});
        } else if (!this.tvMode && document.fullscreenElement && document.exitFullscreen) {
            document.exitFullscreen().catch(() => {});
        }
    }
}" x-init="document.addEventListener('fullscreenchange', () => { if (!document.fullscreenElement && tvMode) { tvMode = false; document.body.classList.remove('tv-presentation-mode'); } })">
    <button
        type="button"
        @click="toggleTvMode()"
        :class="tvMode ? 'sip-topbar-btn sip-topbar-btn-active' : 'sip-topbar-btn'"
        title="Mode Layar Penuh / Presentasi TV (Tanpa Sidebar & Tanpa Scrollbar)"
    >
        <svg class="sip-topbar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <span x-text="tvMode ? 'Keluar Mode TV' : 'Mode Layar TV'">Mode Layar TV</span>
    </button>

    <a
        href="{{ url('/') }}"
        target="_blank"
        rel="noopener noreferrer"
        class="sip-topbar-btn"
        title="Buka Portal Publik SIP-BMD di tab baru"
    >
        <svg class="sip-topbar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
        </svg>
        <span>Lihat Portal Publik</span>
    </a>
</div>
