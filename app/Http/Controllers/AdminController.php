<?php

namespace App\Http\Controllers;

use App\Models\ApprovalList;
use App\Models\Participant;
use App\Models\RejectionList;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    public function loginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        if ($request->password === env('ADMIN_PASSWORD', 'secret')) {
            Session::put('admin_logged_in', true);
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['password' => 'Invalid password']);
    }

    public function dashboard()
    {
        if (!Session::get('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        // Group teams by size
        $teams = Team::with('participants')->get();
        $teamsBySize = [
            5 => [],
            4 => [],
            3 => [],
            2 => [],
            1 => [],
        ];

        foreach ($teams as $team) {
            $count = $team->participants->count();
            if (isset($teamsBySize[$count])) {
                $teamsBySize[$count][] = $team;
            }
        }

        $solos = Participant::whereNull('team_id')->get();

        return view('admin.dashboard', compact('teamsBySize', 'solos'));
    }

    public function approve(Request $request)
    {
        if (!Session::get('admin_logged_in'))
            abort(403);

        $emails = $request->input('emails', []);
        foreach ($emails as $email) {
            ApprovalList::firstOrCreate(['email' => $email]);
            RejectionList::where('email', $email)->delete();
        }

        return back()->with('success', 'Participants approved.');
    }

    public function reject(Request $request)
    {
        if (!Session::get('admin_logged_in'))
            abort(403);

        $emails = $request->input('emails', []);
        foreach ($emails as $email) {
            RejectionList::firstOrCreate(['email' => $email]);
            ApprovalList::where('email', $email)->delete();
        }

        return back()->with('success', 'Participants rejected.');
    }

    public function export()
    {
        if (!Session::get('admin_logged_in'))
            abort(403);

        $participants = Participant::with('team')->get();
        $csvFileName = 'participants_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            'ID', 'Team Code', 'Team Name', 'Name', 'Email', 'Phone', 'Age', 'Gender', 
            'Role', 'Company', 'Portfolio', 'LinkedIn', 'Experience', 'Background', 
            'T-Shirt', 'Dietary', 'Looking for Job', 'Resume Path', 'Registered At'
        ];

        $callback = function () use ($participants, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($participants as $p) {
                $row = [
                    $p->id,
                    $p->team ? $p->team->code : 'SOLO',
                    $p->team ? $p->team->name : '-',
                    $p->name,
                    $p->email,
                    $p->phone,
                    $p->age,
                    $p->gender,
                    $p->role,
                    $p->company_name,
                    $p->portfolio_url,
                    $p->linkedin_url,
                    $p->years_of_experience,
                    $p->background,
                    $p->tshirt_size,
                    $p->dietary_restrictions,
                    $p->looking_for_job ? 'Yes' : 'No',
                    $p->resume_path,
                    $p->created_at,
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
