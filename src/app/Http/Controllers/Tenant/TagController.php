<?php

// src/app/Http/Controllers/Tenant/TagController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpsertTagRequest;
use App\Models\Tenant\Tag;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Tag::class, 'tag');
    }

    public function index(): Response
    {
        $tags = Tag::query()
            ->select(['id', 'nome_tag', 'cor', 'created_at'])
            ->orderBy('nome_tag')
            ->paginate(10);

        return Inertia::render('Tenant/Tags/Index', [
            'tags' => $tags,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Tenant/Tags/Form');
    }

    public function store(UpsertTagRequest $request): RedirectResponse
    {
        Tag::create($request->validated());

        return redirect()->route('admin.tags.index') // Corrigido para a rota de admin, se necessário
            ->with('success', 'Tag criada com sucesso.');
    }

    public function edit(Tag $tag): Response
    {
        return Inertia::render('Tenant/Tags/Form', [
            'tag' => $tag,
        ]);
    }

    public function update(UpsertTagRequest $request, Tag $tag): RedirectResponse
    {
        $tag->update($request->validated());

        return redirect()->route('admin.tags.index') // Corrigido para a rota de admin, se necessário
            ->with('success', 'Tag atualizada com sucesso.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('admin.tags.index') // Corrigido para a rota de admin, se necessário
            ->with('success', 'Tag excluída com sucesso.');
    }
}
