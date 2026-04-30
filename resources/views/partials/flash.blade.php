@if (session('status'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
        {{ session('status') }}
    </div>
@endif

@if (session('auth_error') || session('error'))
    <div class="@if(session('status')) mt-3 @endif rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
        {{ session('auth_error') ?? session('error') }}
    </div>
@endif
