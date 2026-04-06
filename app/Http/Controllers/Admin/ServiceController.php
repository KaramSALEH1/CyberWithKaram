<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Models\Service;
use App\Services\Service\ServiceManagementService;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::query()->latest()->paginate(15);
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(StoreServiceRequest $request, ServiceManagementService $serviceManagementService)
    {
        $service = $serviceManagementService->create($request->validated());
        return redirect()->route('admin.services.show', $service)->with('success', 'Service created successfully.');
    }

    public function show(Service $service)
    {
        return view('admin.services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(UpdateServiceRequest $request, Service $service, ServiceManagementService $serviceManagementService)
    {
        $serviceManagementService->update($service, $request->validated());
        return redirect()->route('admin.services.show', $service)->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully.');
    }
}
