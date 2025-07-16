<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class FuncionarioController extends Controller
{
    public function index()
    {
        // Busca usuários que têm o papel 'Funcionario' ou 'Admin Tenant'
        $funcionarios = User::role(['Funcionario', 'Admin Tenant'])->latest()->get();

        return Inertia::render('Tenant/Funcionarios/Index', [
            'funcionarios' => $funcionarios,
        ]);
    }

    public function create()
    {
        return Inertia::render('Tenant/Funcionarios/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('Funcionario');

        return redirect()->route('funcionarios.index')->with('success', 'Funcionário criado com sucesso.');
    }

    public function edit(User $funcionario)
    {
        return Inertia::render('Tenant/Funcionarios/Edit', [
            'funcionario' => $funcionario,
        ]);
    }

    public function update(Request $request, User $funcionario)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($funcionario->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $funcionario->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $funcionario->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->route('funcionarios.index')->with('success', 'Funcionário atualizado com sucesso.');
    }

    public function destroy(User $funcionario)
    {
        // Impede que um usuário exclua a si mesmo
        if ($funcionario->id === auth()->id()) {
            return redirect()->route('funcionarios.index')->withErrors(['general' => 'Você não pode excluir seu próprio usuário.']);
        }
        $funcionario->delete();
        return redirect()->route('funcionarios.index')->with('success', 'Funcionário excluído com sucesso.');
    }
}
