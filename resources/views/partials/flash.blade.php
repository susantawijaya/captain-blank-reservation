@if (session('status') || session('auth_error') || session('error'))
    <div class="flash-stack" aria-live="polite" aria-atomic="true" data-flash-stack>
        @if (session('status'))
            <div class="flash-toast flash-toast-success" role="status" data-flash-toast data-flash-timeout="4500">
                <div class="flash-toast-copy">
                    <strong>Berhasil</strong>
                    <p>{{ session('status') }}</p>
                </div>
                <button class="flash-toast-close" type="button" aria-label="Tutup notifikasi" data-flash-close>×</button>
            </div>
        @endif

        @if (session('auth_error') || session('error'))
            <div class="flash-toast flash-toast-error" role="alert" data-flash-toast data-flash-timeout="6500">
                <div class="flash-toast-copy">
                    <strong>Perhatian</strong>
                    <p>{{ session('auth_error') ?? session('error') }}</p>
                </div>
                <button class="flash-toast-close" type="button" aria-label="Tutup notifikasi" data-flash-close>×</button>
            </div>
        @endif
    </div>
@endif
