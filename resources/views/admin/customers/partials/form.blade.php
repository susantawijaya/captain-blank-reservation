<form class="form-grid mt-6" method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="field">
        <label for="name">Nama</label>
        <input id="name" name="name" type="text" value="{{ old('name', $customer->name) }}" required>
        @error('name')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-grid two">
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" required>
            @error('email')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="phone">No WhatsApp</label>
            <input id="phone" name="phone" type="text" value="{{ old('phone', $customer->phone) }}">
            @error('phone')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="field">
        <label for="address">Alamat</label>
        <textarea id="address" name="address" placeholder="Alamat lengkap">{{ old('address', $customer->address) }}</textarea>
        @error('address')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-grid two" data-password-group>
        <div class="field">
            <label for="password">Password</label>
            <div class="relative" data-password-field>
                <input id="password" name="password" type="password" class="pr-14" autocomplete="new-password" data-password-input @if ($method === 'POST') required @endif>
                <button
                    class="absolute inset-y-0 right-3 flex items-center text-slate-400 transition hover:text-sky-700"
                    type="button"
                    data-password-toggle
                    data-password-target="password"
                    aria-controls="password"
                    aria-label="Tampilkan password"
                    aria-pressed="false"
                >
                    <svg data-password-icon="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                    </svg>
                    <svg data-password-icon="hide" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.585 10.587A2 2 0 0 0 12 16a2 2 0 0 0 1.414-.586" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A10.94 10.94 0 0 1 12 5c4.477 0 8.268 2.943 9.542 7a11.05 11.05 0 0 1-4.152 5.243" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.228 6.228A11.04 11.04 0 0 0 2.458 12c1.274 4.057 5.065 7 9.542 7 1.561 0 3.045-.358 4.367-.997" />
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="password_confirmation">Konfirmasi Password</label>
            <div class="relative" data-password-field>
                <input id="password_confirmation" name="password_confirmation" type="password" class="pr-14" autocomplete="new-password" data-password-confirmation @if ($method === 'POST') required @endif>
                <button
                    class="absolute inset-y-0 right-3 flex items-center text-slate-400 transition hover:text-sky-700"
                    type="button"
                    data-password-toggle
                    data-password-target="password_confirmation"
                    aria-controls="password_confirmation"
                    aria-label="Tampilkan konfirmasi password"
                    aria-pressed="false"
                >
                    <svg data-password-icon="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                    </svg>
                    <svg data-password-icon="hide" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.585 10.587A2 2 0 0 0 12 16a2 2 0 0 0 1.414-.586" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A10.94 10.94 0 0 1 12 5c4.477 0 8.268 2.943 9.542 7a11.05 11.05 0 0 1-4.152 5.243" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.228 6.228A11.04 11.04 0 0 0 2.458 12c1.274 4.057 5.065 7 9.542 7 1.561 0 3.045-.358 4.367-.997" />
                    </svg>
                </button>
            </div>
            <p class="mt-2 hidden text-sm font-medium text-red-600" data-password-message>Password yang Anda masukkan tidak sesuai.</p>
        </div>
    </div>

    <button class="button primary" type="submit">{{ $submitLabel }}</button>
</form>
