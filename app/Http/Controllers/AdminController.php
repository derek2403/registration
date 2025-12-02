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

        // Fetch Data
        $teams = Team::with('participants')->get();
        $solos = Participant::whereNull('team_id')->get();

        // Use hashes for status checks
        $approvalListHashes = ApprovalList::pluck('email_hash')->toArray();
        $rejectionListHashes = RejectionList::pluck('email_hash')->toArray();

        // Get decrypted emails for the UI (Email Actions)
        $approvalListEmails = ApprovalList::all()->pluck('email')->toArray();
        $rejectionListEmails = RejectionList::all()->pluck('email')->toArray();

        // Calculate Stats
        $totalParticipants = Participant::count();
        $totalTeams = $teams->count();
        $totalSolo = $solos->count();
        $acceptedCount = ApprovalList::count();
        $rejectedCount = RejectionList::count();

        // Mark participants as accepted/rejected for easier frontend logic
        $teams->each(function ($team) use ($approvalListHashes, $rejectionListHashes) {
            $acceptedMembers = 0;
            $rejectedMembers = 0;
            $totalMembers = $team->participants->count();

            $team->participants->each(function ($p) use ($approvalListHashes, $rejectionListHashes, &$acceptedMembers, &$rejectedMembers) {
                $p->status = in_array($p->email_hash, $approvalListHashes) ? 'accepted' : (in_array($p->email_hash, $rejectionListHashes) ? 'rejected' : 'pending');
                if ($p->status === 'accepted')
                    $acceptedMembers++;
                if ($p->status === 'rejected')
                    $rejectedMembers++;
            });

            if ($totalMembers > 0 && $acceptedMembers === $totalMembers) {
                $team->status = 'accepted';
            } elseif ($rejectedMembers > 0) {
                $team->status = 'rejected';
            } else {
                $team->status = 'pending';
            }
        });

        $solos->each(function ($p) use ($approvalListHashes, $rejectionListHashes) {
            $p->status = in_array($p->email_hash, $approvalListHashes) ? 'accepted' : (in_array($p->email_hash, $rejectionListHashes) ? 'rejected' : 'pending');
        });

        return view('admin.dashboard', compact(
            'teams',
            'solos',
            'totalParticipants',
            'totalTeams',
            'totalSolo',
            'acceptedCount',
            'rejectedCount',
            'approvalListEmails',
            'rejectionListEmails'
        ));
    }

    public function approve(Request $request)
    {
        if (!Session::get('admin_logged_in'))
            abort(403);

        $emails = $request->input('emails', []);
        foreach ($emails as $email) {
            $hash = hash_hmac('sha256', $email, config('app.key'));
            ApprovalList::firstOrCreate(['email_hash' => $hash], ['email' => $email]);
            RejectionList::where('email_hash', $hash)->delete();
        }

        return back()->with('success', 'Participants approved.');
    }

    public function reject(Request $request)
    {
        if (!Session::get('admin_logged_in'))
            abort(403);

        $emails = $request->input('emails', []);
        foreach ($emails as $email) {
            $hash = hash_hmac('sha256', $email, config('app.key'));
            RejectionList::firstOrCreate(['email_hash' => $hash], ['email' => $email]);
            ApprovalList::where('email_hash', $hash)->delete();
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
            'ID',
            'Team Code',
            'Team Name',
            'Name',
            'Email',
            'Phone',
            'Age',
            'Gender',
            'Role',
            'Company',
            'Portfolio',
            'LinkedIn',
            'Experience',
            'Background',
            'T-Shirt',
            'Dietary',
            'Looking for Job',
            'Resume Path',
            'Registered At'
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
