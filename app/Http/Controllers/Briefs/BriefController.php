<?php

namespace App\Http\Controllers\Briefs;

use App\DTO\BriefDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Briefs\CreateRequest;
use App\Models\Brief;
use App\Services\Briefs\BriefService;
use Illuminate\Http\Request;

class BriefController extends Controller
{
    public function __construct(
        private readonly BriefService $briefService,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRequest $request)
    {
        $this->briefService->updateOrCreate(BriefDTO::fromCreateRequest($request));
    }

    /**
     * Display the specified resource.
     */
    public function show(Brief $brief)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brief $brief)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brief $brief)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brief $brief)
    {
        //
    }
}
