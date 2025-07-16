<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Mail\Central\CampaignMail; // Importe a nova classe
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage; // Importe o Storage
use Inertia\Inertia;

class CampaignController extends Controller {

    public function create()
    {
        return Inertia::render('Central/Campaigns/Create', [
            'leads' => Lead::all()
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'recipients' => 'required|array|min:1',
            'subject' => 'required|string',
            'body' => 'required|string',
            // Validação para o anexo (opcional, até 5MB)
            'attachment' => 'nullable|file|max:5120',
        ]);

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            // Salva o arquivo em 'storage/app/attachments'
            $attachmentPath = $file->store('attachments');
        }

        $leads = Lead::find($request->recipients);

        foreach ($leads as $lead) {
            $personalizedBody = str_replace('{{ nome }}', $lead->nome, $request->body);

            $fullAttachmentPath = $attachmentPath ? storage_path('app/' . $attachmentPath) : null;

            Mail::to($lead->email)
                ->send(new CampaignMail($personalizedBody, $fullAttachmentPath, $attachmentName));
        }

        // Apaga o arquivo temporário após o envio
        if ($attachmentPath) {
            Storage::delete($attachmentPath);
        }

        return redirect()->back()->with('success', 'Campanha enviada com sucesso!');
    }
}
