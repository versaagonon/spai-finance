<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Program;
use App\Models\Project;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $globalAmil = AppSetting::get('global_amil_percentage', 0);
        $programs = Program::all();
        $projects = Project::with('program')->get();

        $pillarsJson = AppSetting::get('project_pillars', json_encode(['Pendidikan', 'Kemanusiaan', 'Dakwah', 'Ekonomi', 'Kesehatan', 'Operasional', 'Lainnya']));
        $pillars = json_decode($pillarsJson, true);

        return view('finance.settings.index', compact('globalAmil', 'programs', 'projects', 'pillars'));
    }

    public function updateGlobal(Request $request)
    {
        $request->validate([
            'global_amil_percentage' => 'required|numeric|min:0|max:100',
        ]);

        AppSetting::set('global_amil_percentage', $request->global_amil_percentage);

        return redirect()->back()->with('success', 'Pengaturan global berhasil diperbarui.');
    }

    public function updateProgram(Request $request, Program $program)
    {
        $request->validate([
            'amil_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $program->update([
            'amil_percentage' => $request->amil_percentage
        ]);

        return redirect()->back()->with('success', 'Pengaturan program berhasil diperbarui.');
    }

    public function updateProject(Request $request, Project $project)
    {
        $request->validate([
            'amil_percentage' => 'required|numeric|min:0|max:100',
            'use_custom_amil' => 'boolean',
        ]);

        $project->update([
            'amil_percentage' => $request->amil_percentage,
            'use_custom_amil' => $request->has('use_custom_amil')
        ]);

        return redirect()->back()->with('success', 'Pengaturan proyek berhasil diperbarui.');
    }

    public function updatePillars(Request $request)
    {
        $request->validate([
            'pillars' => 'required|string',
        ]);

        $pillars = array_map('trim', explode(',', $request->pillars));
        $pillars = array_filter($pillars);

        \App\Models\AppSetting::set('project_pillars', json_encode(array_values($pillars)));

        return redirect()->back()->with('success', 'Daftar pilar berhasil diperbarui.');
    }
}
