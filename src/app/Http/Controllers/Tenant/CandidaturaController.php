<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Mail\Tenant\NovaCandidaturaMail;
use App\Models\Tenant\Candidatura;
use App\Models\Tenant\Vaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CandidaturaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Vaga $vaga): Response
    {
        $this->authorize('viewAny', Candidatura::class);

        $candidaturas = $vaga->candidaturas()->with('user')->latest()->paginate(10);
        $vaga->load('empresa');

        return Inertia::render('Tenant/Candidaturas/Index', [
            'vaga' => $vaga,
            'candidaturas' => $candidaturas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Vaga $vaga): Response
    {
        if ($vaga->status !== 'aberta' || ($vaga->data_expiracao && $vaga->data_expiracao < now())) {
            abort(404);
        }

        $this->authorize('create', [Candidatura::class, $vaga]);

        $vaga->load('empresa');

        return Inertia::render('Tenant/Candidaturas/Create', compact('vaga'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Vaga $vaga)
    {
        $this->authorize('create', [Candidatura::class, $vaga]);

        $validated = $request->validate([
            'curriculo' => 'required|file|mimes:pdf,doc,docx|max:2048',
            'mensagem_apresentacao' => 'nullable|string|max:2000',
        ]);

        if ($vaga->status !== 'aberta' || ($vaga->data_expiracao && $vaga->data_expiracao < now())) {
            return redirect()->back()->with('error', 'Esta vaga não está mais aceitando candidaturas.');
        }

        $candidato = $request->user();

        // O currículo agora é guardado no disco privado 'local'
        $path = $request->file('curriculo')->store('curriculos', 'local');

        $candidatura = Candidatura::create([
            'vaga_id' => $vaga->id,
            'user_id' => $candidato->id,
            'curriculo_path' => $path,
            'mensagem_apresentacao' => $validated['mensagem_apresentacao'],
            'status' => 'enviada',
        ]);

        // ATUALIZAÇÃO: Adicionados logs mais detalhados para depuração
        try {
            $empresa = $vaga->empresa;
            if ($empresa && $empresa->email) {
                Log::info("Tentando enviar e-mail para {$empresa->email} com o currículo em {$path}");
                Mail::to($empresa->email)->send(new NovaCandidaturaMail($vaga, $candidato, $candidatura));
                Log::info("E-mail para a vaga ID {$vaga->id} colocado na fila com sucesso.");
            } else {
                Log::warning("Não foi possível enviar e-mail para a vaga ID {$vaga->id}: a empresa não tem um e-mail cadastrado.");
            }
        } catch (\Exception $e) {
            Log::error("Falha ao enviar e-mail de nova candidatura para a vaga ID {$vaga->id}: ".$e->getMessage());
        }

        return redirect()->route('portal.vagas.index')->with('success', 'Candidatura enviada com sucesso!');
    }

    /**
     * Download the curriculum for the specified candidatura.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadCurriculo(Candidatura $candidatura)
    {
        $this->authorize('view', $candidatura);

        $filename = 'curriculo_'.Str::slug($candidatura->user->name).'_'.$candidatura->id.'.'.pathinfo($candidatura->curriculo_path, PATHINFO_EXTENSION);

        // O download agora é feito a partir do disco privado 'local'
        return Storage::disk('local')->download($candidatura->curriculo_path, $filename);
    }
}
