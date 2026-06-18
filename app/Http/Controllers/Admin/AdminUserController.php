<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::withCount(['events'])
            ->orderByDesc('created_at');

        if ($search = $request->query('search')) {
            $query->where(fn ($q) =>
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
            );
        }

        return Inertia::render('Admin/Users', [
            'users'   => $query->paginate(50)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function role(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['admin', 'host'])],
        ]);

        if ($user->hasRole('admin') && $data['role'] !== 'admin') {
            abort_if(User::role('admin')->count() <= 1, 422, 'Không thể hạ quyền admin cuối cùng.');
        }

        $user->syncRoles([$data['role']]);

        return response()->json(['role' => $data['role']]);
    }

    public function ban(User $user): JsonResponse
    {
        abort_if($user->hasRole('admin'), 422, 'Không thể ban tài khoản admin.');

        $user->update(['banned_at' => now()]);

        return response()->json(['banned_at' => $user->banned_at]);
    }

    public function unban(User $user): JsonResponse
    {
        $user->update(['banned_at' => null]);

        return response()->json(['banned_at' => null]);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            abort(422, 'Không thể xóa admin cuối cùng.');
        }

        abort_if($user->id === auth()->id(), 422, 'Không thể tự xóa tài khoản của mình.');

        Log::info("Admin deleted user {$user->id} ({$user->email}) by " . auth()->id());

        $user->delete();

        return response()->json(['deleted' => true]);
    }
}
