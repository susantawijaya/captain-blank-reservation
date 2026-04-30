<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()
                ->where('role', 'admin')
                ->orderByDesc('is_master_admin')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User([
                'role' => 'admin',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'password' => $validated['password'],
            'role' => 'admin',
            'is_master_admin' => false,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('status', 'Akun admin berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $this->ensureAdminAccount($user);
        $this->ensureEditable($user);

        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdminAccount($user);
        $this->ensureEditable($user);

        $validated = $this->validateUser($request, $user);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'role' => 'admin',
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);

        return redirect()->route('admin.users.index')
            ->with('status', 'Akun admin berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureAdminAccount($user);
        $this->ensureEditable($user);

        if ($user->is(auth()->user())) {
            return redirect()->route('admin.users.index')
                ->with('auth_error', 'Akun yang sedang digunakan tidak bisa dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('status', 'Akun admin berhasil dihapus.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $passwordRules = $user
            ? ['nullable', 'confirmed', Password::min(8)]
            : ['required', 'confirmed', Password::min(8)];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'password' => $passwordRules,
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Password yang Anda masukkan tidak sesuai.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);
    }

    private function ensureAdminAccount(User $user): void
    {
        abort_unless($user->isAdmin(), 404);
    }

    private function ensureEditable(User $user): void
    {
        abort_if($user->isMasterAdmin(), 403, 'Master admin tidak dapat diubah dari halaman ini.');
    }
}
