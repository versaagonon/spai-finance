<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::with(['projects' => function ($query) {
            $query->withSum('donations as total_income', 'amount')
                  ->withSum('donations as total_amil', 'amil_amount')
                  ->withSum('disbursements as total_expense', 'amount')
                  ->withSum('disbursements as total_admin_fee', 'admin_fee');
        }])->latest()->paginate(10);

        // Fetch Orphaned Projects (No Program)
        $noProgramProjects = \App\Models\Project::doesntHave('program')
            ->withSum('donations as total_income', 'amount')
            ->withSum('donations as total_amil', 'amil_amount')
            ->withSum('disbursements as total_expense', 'amount')
            ->withSum('disbursements as total_admin_fee', 'admin_fee')
            ->get();
        
        return view('finance.programs.index', compact('programs', 'noProgramProjects'));
    }

    public function create()
    {
        return view('finance.programs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Program::create($validated);

        return redirect()->route('finance.programs.index')->with('success', 'Program created successfully.');
    }

    public function edit(Program $program)
    {
        return view('finance.programs.edit', compact('program'));
    }

    public function update(Request $request, Program $program)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $program->update($validated);

        return redirect()->route('finance.programs.index')->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program)
    {
        // Check if program has projects? Migration says 'set null' on delete, so it's safe.
        $program->delete();
        return redirect()->route('finance.programs.index')->with('success', 'Program deleted successfully.');
    }
}
