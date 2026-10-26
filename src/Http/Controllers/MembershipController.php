<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Membership;

class MembershipController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_membership.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_membership.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_membership.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_membership.delete')->only('destroy');
    }

    public function index()
    {
        $memberships = Membership::latest()->paginate(get_setting('pagination', 10));
        return view('retail::memberships.index', compact('memberships'));
    }

    public function create()
    {
        return view('retail::memberships.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            Membership::create($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.memberships.index')->with('error', __('Failed to create membership'));
        }

        return redirect()->route('retail.memberships.index')->with('success', __('Membership created successfully'));
    }

    public function show(Membership $membership)
    {
        return view('retail::memberships.show', compact('membership'));
    }

    public function edit(Membership $membership)
    {
        return view('retail::memberships.edit', compact('membership'));
    }

    public function update(Request $request, Membership $membership)
    {
        $data = $this->validateData($request, $membership->id);

        DB::beginTransaction();
        try {
            $membership->update($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.memberships.index')->with('error', __('Failed to update membership'));
        }

        return redirect()->route('retail.memberships.index')->with('success', __('Membership updated successfully'));
    }

    public function destroy(Membership $membership)
    {
        DB::beginTransaction();
        try {
            $membership->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.memberships.index')->with('error', __('Failed to delete membership'));
        }

        return redirect()->route('retail.memberships.index')->with('success', __('Membership deleted successfully'));
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'code'              => 'required|string|max:50|unique:retail_memberships,code,' . $id,
            'fee'               => 'nullable|numeric|min:0',
            'duration_days'     => 'nullable|integer|min:1',
            'discount_percent'  => 'nullable|numeric|min:0|max:100',
            'point_multiplier'  => 'nullable|numeric|min:0',
            'status'            => 'nullable|boolean',
        ]);

        return [
            'name'             => $request->name,
            'code'             => $request->code,
            'fee'              => $request->fee ?: 0,
            'duration_days'    => $request->duration_days ?: null,
            'discount_percent' => $request->discount_percent ?: 0,
            'point_multiplier' => $request->point_multiplier ?: 1,
            'status'           => $request->has('status') ? 1 : 0,
        ];
    }
}
