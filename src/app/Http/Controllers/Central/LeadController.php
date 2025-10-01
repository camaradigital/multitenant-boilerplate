<?php

// app/Http/Controllers/Central/LeadController.php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Lead;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeadController extends Controller
{
    public function index()
    {
        return Inertia::render('Central/Leads/Index', ['leads' => Lead::all()]);
    }

    public function store(Request $request)
    {
        $request->validate(['nome' => 'required', 'email' => 'required|email|unique:leads']);
        Lead::create($request->all());

        return redirect()->back()->with('success', 'Lead criado!');
    }

    public function update(Request $request, Lead $lead)
    {
        $request->validate(['nome' => 'required', 'email' => 'required|email|unique:leads,email,'.$lead->id]);
        $lead->update($request->all());

        return redirect()->back()->with('success', 'Lead atualizado!');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->back()->with('success', 'Lead excluído!');
    }
}
