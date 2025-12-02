<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Team;
use App\Mail\TeamCreated;
use App\Mail\TeamJoined;
use App\Mail\SoloRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $teamCode = $request->query('team_code');
        return view('registration.index', compact('teamCode'));
    }

    public function register(Request $request)
    {
        // Compute email hash for blind indexing
        $emailHash = hash_hmac('sha256', $request->email, config('app.key'));
        $request->merge(['email_hash' => $emailHash]);

        $validated = $request->validate([
            'registration_type' => 'required|in:create_team,join_team,solo',
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:18',
            'email' => 'required|email',
            'email_hash' => 'unique:participants,email_hash',
            'phone' => 'required|string|max:20',
            'gender' => 'required|string',
            'company_name' => 'nullable|string|max:255',
            'portfolio_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'role' => 'required|string',
            'years_of_experience' => 'required|integer|min:0',
            'background' => 'nullable|string',
            'tshirt_size' => 'required|string',
            'dietary_restrictions' => 'nullable|string',
            'mandatory_attendance_confirmed' => 'accepted',
            'looking_for_job' => 'boolean',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',

            // Team Creation
            'team_name' => 'required_if:registration_type,create_team|nullable|string|max:255',
            'team_status' => 'required_if:registration_type,create_team|nullable|in:looking_for_teammates,not_looking_for_teammates',
            'looking_for_description' => 'required_if:team_status,looking_for_teammates|nullable|string',

            // Join Team
            'team_code' => 'required_if:registration_type,join_team|nullable|exists:teams,code',
        ]);

        if ($request->looking_for_job && !$request->hasFile('resume')) {
            return back()->withErrors(['resume' => 'Resume is required when looking for a job.'])->withInput();
        }

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'public');
        }

        $teamId = null;
        $team = null;

        if ($request->registration_type === 'create_team') {
            $team = Team::create([
                'name' => $request->team_name,
                'code' => strtoupper(Str::random(6)),
                'team_status' => $request->team_status,
                'looking_for_description' => $request->looking_for_description,
            ]);
            $teamId = $team->id;
        } elseif ($request->registration_type === 'join_team') {
            $team = Team::where('code', $request->team_code)->firstOrFail();

            if ($team->participants()->count() >= 5) {
                return back()->withErrors(['team_code' => 'This team is already full (max 5 members).'])->withInput();
            }

            $teamId = $team->id;
        }

        $participant = Participant::create([
            'team_id' => $teamId,
            'name' => $request->name,
            'age' => $request->age,
            'email' => $request->email,
            'email_hash' => $emailHash,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'company_name' => $request->company_name,
            'portfolio_url' => $request->portfolio_url,
            'linkedin_url' => $request->linkedin_url,
            'role' => $request->role,
            'years_of_experience' => $request->years_of_experience,
            'background' => $request->background,
            'tshirt_size' => $request->tshirt_size,
            'dietary_restrictions' => $request->dietary_restrictions,
            'mandatory_attendance_confirmed' => $request->has('mandatory_attendance_confirmed'),
            'looking_for_job' => $request->boolean('looking_for_job'),
            'resume_path' => $resumePath,
        ]);

        // Send Emails
        try {
            if ($request->registration_type === 'create_team') {
                Mail::to($participant->email)->send(new TeamCreated($team, $participant));
            } elseif ($request->registration_type === 'join_team') {
                Mail::to($participant->email)->send(new TeamJoined($team, $participant));
            } else {
                Mail::to($participant->email)->send(new SoloRegistered($participant));
            }
        } catch (\Exception $e) {
            // Log the error or handle it gracefully
            // \Log::error('Email sending failed: ' . $e->getMessage());
        }

        $message = 'Registration successful! Please check your spam folder for the confirmation email.';
        if ($team) {
            $message .= ' Your Team Code is: ' . $team->code;
            return redirect()->route('registration.index')->with('success', $message)->with('team_created_code', $team->code);
        }

        return redirect()->route('registration.index')->with('success', $message);
    }
}
