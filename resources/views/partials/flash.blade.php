@if (session('status') || session('auth_error') || session('error'))
    <div class="flash-stack" aria-live="polite" aria-atomic="true">
        @if (session('status'))
            <div class="flash-toast flash-toast-success" role="status">
                <strong>Berhasil</strong>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        @if (session('auth_error') || session('error'))
            <div class="flash-toast flash-toast-error" role="alert">
                <strong>Perhatian</strong>
                <p>{{ session('auth_error') ?? session('error') }}</p>
            </div>
        @endif
    </div>
@endif
