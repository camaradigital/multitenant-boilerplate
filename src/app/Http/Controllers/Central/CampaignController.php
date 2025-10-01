<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Mail\Central\CampaignMail;
use App\Models\Central\Lead; // Importe a classe Mailable correta
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL; // Importe a facade URL para o link de descadastro
use Inertia\Inertia;

class CampaignController extends Controller
{
    public function create()
    {
        return Inertia::render('Central/Campaigns/Create', [
            'leads' => Lead::all(),
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'recipients' => 'required|array|min:1',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'cta_text' => 'required|string|max:50',
            'cta_url' => 'required|url',
            'attachment' => 'nullable|file|max:5120',
        ]);

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            // A variável $attachmentPath já contém o caminho relativo correto, ex: 'attachments/arquivo.pdf'
            $attachmentPath = $file->store('attachments');
        }

        $leads = Lead::find($request->recipients);

        foreach ($leads as $lead) {
            $personalizedBody = str_replace('{{ nome }}', $lead->nome, $request->body);

            $unsubscribeUrl = URL::temporarySignedRoute(
                'unsubscribe', now()->addDays(365), ['lead' => $lead->id]
            );

            $mailData = [
                'leadName' => $lead->nome,
                'body' => $personalizedBody,
                'ctaUrl' => $request->cta_url,
                'ctaText' => $request->cta_text,
                'unsubscribeUrl' => $unsubscribeUrl,
            ];

            // A linha que usava storage_path() foi removida.
            // Agora passamos o caminho relativo ($attachmentPath) diretamente para o Mailable.
            Mail::to($lead->email)->send(
                new CampaignMail($mailData, $attachmentPath, $attachmentName)
            );
        }

        if ($attachmentPath) {
            Storage::delete($attachmentPath);
        }

        return redirect()->back()->with('success', 'Campanha enviada com sucesso!');
    }
}
