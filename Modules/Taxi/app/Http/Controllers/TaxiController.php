<?php

namespace Modules\Taxi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaxiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('taxi::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('taxi::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('taxi::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('taxi::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
