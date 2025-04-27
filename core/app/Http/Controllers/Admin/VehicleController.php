<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    public function index()
    {

        // dd("test");

        $pageTitle = "All Vehicle";
        $vehicles   = Vehicle::searchable(['model'])->orderBy('id', getOrderBy())->paginate(getPaginate());
        $services = Service::all();
        return view('admin.vehicle.index', compact('pageTitle', 'vehicles', 'services'));
    }

    public function store(Request $request, $id = 0)
    {

        // dd($request->all());
        
        $request->validate([
            'service_id'      => 'required',
            'model'        => 'required|string|max:40',
            'number'        => 'required|string|max:40|unique:vehicles,number,' . $id,
            'color'          => 'required|',
            'rc'           => 'nullable|file|mimes:pdf|max:10240',
            // 'image'        => 'nullable|file|mimes:JPG,jpg,png,jpeg|max:10240',
            'polution_certificate' => 'nullable|file|mimes:pdf|max:10240',
            'insurance'    => 'nullable|file|mimes:pdf|max:10240',
        ]);
        if ($id) {
            $vehicle       = Vehicle::findOrFail($id);
            $notification = 'Vehicle updated successfully';
        } else {
            $vehicle       = new Vehicle();
            $notification = 'Vehicle added successfully';
        }
        // dd( $request->all());

        $vehicle->service_id          = $request->service_id;
        $vehicle->model               = $request->model;
        $vehicle->number              = $request->number;
        $vehicle->color               = $request->color;
        $vehicle->status               = 1;
        // $vehicle->insurance             = $request->insurance;
        // $vehicle->rc     = $request->rc;
        // $vehicle->image      = $request->image;
        // $vehicle->polution_certificate             = $request->polution_certificate;

        // Handle file uploads
    if ($request->hasFile('rc')) {
        $vehicle->rc = $request->file('rc')->store('vehicles/rc', 'public');
    }

    if ($request->hasFile('image')) {
        $vehicle->image = $request->file('image')->store('vehicles/images', 'public');
    }

    if ($request->hasFile('polution_certificate')) {
        $vehicle->polution_certificate = $request->file('polution_certificate')->store('vehicles/pollution', 'public');
    }

    if ($request->hasFile('insurance')) {
        $vehicle->insurance = $request->file('insurance')->store('vehicles/insurance', 'public');
    }

        $vehicle->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }


    public function changeStatus($id)
    {
        return Vehicle::changeStatus($id);
    }

    public function detail()
    {
        $pageTitle = "All Vehicle";
        $vehicles   = Vehicle::searchable(['model'])->with('vehicleSession')->orderBy('id', getOrderBy())->paginate(getPaginate());
        // ->paginate(getPaginate());
        $services = Service::all();

            // dd( $vehicles, $vehicles->vehicleSession[0]->driver);
        return view('admin.vehicle.detail', compact('pageTitle', 'vehicles', 'services'));
    
    }
}
