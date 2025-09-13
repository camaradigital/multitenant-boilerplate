<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class CustomFieldController extends Controller
{
    public function index()
    {
        return inertia('Tenant/CustomFields/Index', [
            'customFields' => CustomField::latest()->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            // CORREÇÃO: Adicionada a regra 'unique' para o label
            'label' => 'required|string|max:255|unique:tenant.custom_fields,label',
            'type' => 'required|in:text,number,date,select',
            'options' => 'nullable|required_if:type,select|array',
            'is_required' => 'required|boolean',
        ]);

        CustomField::create([
            'label' => $request->label,
            'name' => Str::slug($request->label, '_'), // Gera um nome seguro para a chave JSON
            'type' => $request->type,
            'options' => $request->type === 'select' ? $request->options : null,
            'is_required' => $request->is_required,
        ]);

        return Redirect::route('admin.custom-fields.index')->with('success', 'Campo personalizado criado.');
    }

    public function update(Request $request, CustomField $customField)
    {
        $request->validate([
            // CORREÇÃO: Adicionada a regra 'unique', ignorando o ID do campo atual
            'label' => 'required|string|max:255|unique:tenant.custom_fields,label,' . $customField->id,
            'type' => 'required|in:text,number,date,select',
            'options' => 'nullable|required_if:type,select|array',
            'is_required' => 'required|boolean',
        ]);

        $customField->update([
            'label' => $request->label,
            'name' => Str::slug($request->label, '_'),
            'type' => $request->type,
            'options' => $request->type === 'select' ? $request->options : null,
            'is_required' => $request->is_required,
        ]);

        return Redirect::route('admin.custom-fields.index')->with('success', 'Campo personalizado atualizado.');
    }

    public function destroy(CustomField $customField)
    {
        // Aqui, futuramente, poderíamos adicionar uma lógica para limpar
        // este campo dos `profile_data` de todos os cidadãos.
        $customField->delete();
        return Redirect::route('admin.custom-fields.index')->with('success', 'Campo personalizado excluído.');
    }
}
