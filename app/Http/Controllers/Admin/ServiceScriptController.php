<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\EditServiceScriptRequest;
use App\Models\Service;
use App\Services\Service\ServiceScriptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceScriptController extends Controller
{
    public function __construct(private readonly ServiceScriptService $scriptService)
    {
    }

    /**
     * Show the dedicated Python script editor for a service.
     */
    public function edit(Service $service): View
    {
        $content = $service->getScriptContent() ?? '';

        return view('admin.services.script_edit', compact('service', 'content'));
    }

    /**
     * Update the Python script for a service.
     *
     * The script is stored as a file in storage/app/service_scripts/{id}/script.py
     * (private, not publicly accessible). The services.script_path column tracks
     * the path. Falls back to script_code column if no file path is set.
     */
    public function update(EditServiceScriptRequest $request, Service $service): RedirectResponse
    {
        $file = $request->getScriptFile();
        $content = $request->getScriptContent();

        if (! $file && ! $content) {
            return back()->withErrors([
                'script' => 'Provide either a Python file (.py) or paste script content directly.',
            ]);
        }

        $path = $this->scriptService->store($service->id, $file, $content);

        $service->update(['script_path' => $path]);

        return redirect()
            ->route('admin.services.show', $service)
            ->with('success', 'Python script saved successfully.');
    }
}
