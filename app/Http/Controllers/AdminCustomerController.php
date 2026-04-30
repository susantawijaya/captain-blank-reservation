<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $name = trim($validated['name'] ?? '');

        return view('admin.customers.index', [
            'customers' => User::query()
                ->where('role', 'customer')
                ->when($name !== '', fn ($query) => $query->where('name', 'like', "%{$name}%"))
                ->withCount(['reservations', 'reviews'])
                ->orderBy('name')
                ->get(),
            'filters' => [
                'name' => $name,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.customers.create', [
            'customer' => new User([
                'role' => 'customer',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCustomer($request);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'password' => $validated['password'],
            'role' => 'customer',
            'is_master_admin' => false,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.customers.index')
            ->with('status', 'Akun pelanggan berhasil ditambahkan.');
    }

    public function show(User $user): View
    {
        $this->ensureCustomerAccount($user);

        return view('admin.customers.show', [
            'customer' => $user->load([
                'reservations.package',
                'reviews.package',
                'complaints',
            ]),
        ]);
    }

    public function edit(User $user): View
    {
        $this->ensureCustomerAccount($user);

        return view('admin.customers.edit', ['customer' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureCustomerAccount($user);

        $validated = $this->validateCustomer($request, $user);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'role' => 'customer',
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);

        return redirect()->route('admin.customers.index')
            ->with('status', 'Akun pelanggan berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureCustomerAccount($user);
        $user->loadCount(['reservations', 'reviews', 'complaints']);

        if ($user->reservations_count > 0 || $user->reviews_count > 0 || $user->complaints_count > 0) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Pelanggan tidak bisa dihapus karena sudah memiliki riwayat aktivitas di sistem.');
        }

        $user->delete();

        return redirect()->route('admin.customers.index')
            ->with('status', 'Akun pelanggan berhasil dihapus.');
    }

    private function validateCustomer(Request $request, ?User $user = null): array
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

    private function ensureCustomerAccount(User $user): void
    {
        abort_unless($user->isCustomer(), 404);
    }
}
