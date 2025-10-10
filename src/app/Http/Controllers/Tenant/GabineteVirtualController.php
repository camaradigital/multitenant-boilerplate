<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\GabineteVirtualMensagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GabineteVirtualController extends Controller
{
    /**
     * Display a listing of the resource for the citizen.
     */
    public function citizenIndex()
    {
        $this->authorize('viewAny', GabineteVirtualMensagem::class);

        $mensagens = GabineteVirtualMensagem::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return Inertia::render('Tenant/GabineteVirtual/Index', [
            'mensagens' => $mensagens,
        ]);
    }

    /**
     * Display the specified resource for the citizen.
     */
    public function citizenShow(GabineteVirtualMensagem $mensagem)
    {
        // A Policy verifica se o usuário é o dono ou um admin.
        $this->authorize('view', $mensagem);

        $mensagem->load('user', 'respostas.user');

        return Inertia::render('Tenant/GabineteVirtual/Show', [
            'mensagem' => $mensagem,
        ]);
    }

    /**
     * Store a new message from the citizen.
     */
    public function storeMensagem(Request $request)
    {
        $this->authorize('create', GabineteVirtualMensagem::class);

        $request->validate([
            'assunto' => 'required|string|max:255',
            'mensagem' => 'required|string',
        ]);

        GabineteVirtualMensagem::create([
            'user_id' => Auth::id(),
            'assunto' => $request->assunto,
            'mensagem' => $request->mensagem,
        ]);

        return redirect()->route('portalcidadao.gabinete-virtual.index')->with('success', 'Mensagem enviada com sucesso!');
    }

    // Para o Admin (Presidente)
    public function adminIndex(Request $request)
    {
        // Autorização específica para a visão do admin
        $this->authorize('viewAnyAdmin', GabineteVirtualMensagem::class);

        $mensagens = GabineteVirtualMensagem::with('user')
            ->when($request->input('search'), function ($query, $search) {
                $query->where('assunto', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($request->input('status'), function ($query, $status) {
                if ($status !== 'todos') {
                    $query->where('status', $status);
                }
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Tenant/GabineteVirtual/Admin/Index', [
            'mensagens' => $mensagens,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function adminShow(GabineteVirtualMensagem $mensagem)
    {
        // A mesma policy 'view' funciona aqui, pois ela permite admins também.
        $this->authorize('view', $mensagem);

        $mensagem->load('user', 'respostas.user');

        return Inertia::render('Tenant/GabineteVirtual/Admin/Show', [
            'mensagem' => $mensagem,
        ]);
    }

    public function storeResposta(Request $request, GabineteVirtualMensagem $mensagem)
    {
        // Autorização para responder
        $this->authorize('reply', $mensagem);

        $request->validate([
            'resposta' => 'required|string',
        ]);

        $mensagem->respostas()->create([
            'user_id' => Auth::id(), // ID do admin/presidente
            'resposta' => $request->resposta,
        ]);

        return back()->with('success', 'Resposta enviada com sucesso!');
    }

    public function updateStatus(Request $request, GabineteVirtualMensagem $mensagem)
    {
        // Autorização para atualizar (status)
        $this->authorize('update', $mensagem);

        $request->validate([
            'status' => 'required|in:Pendente,Resolvido,Sem Solução',
        ]);

        $mensagem->update(['status' => $request->status]);

        return back()->with('success', 'Status da mensagem atualizado com sucesso!');
    }
}
