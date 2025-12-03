@extends('layouts.app')

@section('content')
    @php
        $inputClass = "mt-2 w-full rounded-xl border border-black/10 bg-white px-4 py-2 text-sm shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0064C8]";
        $labelClass = "flex flex-col text-sm text-neutral-700 font-medium";
        $helperClass = "text-xs text-neutral-500 font-normal";
    @endphp

    <div class="mt-15 flex w-full items-center justify-center p-4">
        <div class="shadow-input w-full max-w-[70%] rounded-2xl border border-black/5 bg-white p-4 text-left md:p-6 shadow-lg">
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight">
                    <span class="text-[#0064C8]">Hackathon</span> Registration
                </h1>
                <p class="mt-2 text-neutral-600">
                    Join us for an amazing event. Create a team, join one, or go solo.
                </p>
            </div>

            <form action="{{ route('registration.store') }}" method="POST" enctype="multipart/form-data" id="registrationForm" class="space-y-5">
                @csrf

                <div>
                    <label class="{{ $labelClass }}">
                        How would you like to register?
                        <span class="{{ $helperClass }}">Choose your participation style.</span>
                    </label>
                    <div class="mt-3 flex flex-col sm:flex-row gap-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" class="form-radio text-[#0064C8] focus:ring-[#0064C8]" name="registration_type" value="create_team"
                                onchange="toggleForm()" {{ old('registration_type') == 'create_team' ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-neutral-700">Create a Team</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" class="form-radio text-[#0064C8] focus:ring-[#0064C8]" name="registration_type" value="join_team"
                                onchange="toggleForm()" {{ old('registration_type') == 'join_team' || $teamCode ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-neutral-700">Join a Team</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" class="form-radio text-[#0064C8] focus:ring-[#0064C8]" name="registration_type" value="solo" onchange="toggleForm()"
                                {{ old('registration_type') == 'solo' ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-neutral-700">Register Solo</span>
                        </label>
                    </div>
                </div>

                <!-- Team Creation Fields -->
                <div id="createTeamFields" class="hidden space-y-5 border-t border-black/5 pt-5">
                    <h3 class="text-lg font-semibold text-neutral-800">Team Details</h3>
                    <div class="flex flex-col gap-4 md:flex-row">
                        <label class="{{ $labelClass }} w-full" for="team_name">
                            Team Name
                            <input class="{{ $inputClass }}" id="team_name" type="text" name="team_name" value="{{ old('team_name') }}" placeholder="e.g. The Hackers">
                        </label>
                        <label class="{{ $labelClass }} w-full" for="team_status">
                            Team Status
                            <select class="{{ $inputClass }}" id="team_status" name="team_status" onchange="toggleLookingFor()">
                                <option value="not_looking_for_teammates" {{ old('team_status') == 'not_looking_for_teammates' ? 'selected' : '' }}>Not looking for teammates</option>
                                <option value="looking_for_teammates" {{ old('team_status') == 'looking_for_teammates' ? 'selected' : '' }}>Looking for teammates</option>
                            </select>
                        </label>
                    </div>
                    <div class="hidden" id="lookingForDesc">
                        <label class="{{ $labelClass }}" for="looking_for_description">
                            What are you looking for?
                            <textarea class="{{ $inputClass }} resize-none" id="looking_for_description" name="looking_for_description" rows="3" placeholder="Describe the skills you need...">{{ old('looking_for_description') }}</textarea>
                        </label>
                    </div>
                </div>

                <!-- Join Team Fields -->
                <div id="joinTeamFields" class="hidden space-y-5 border-t border-black/5 pt-5">
                    <h3 class="text-lg font-semibold text-neutral-800">Join Team</h3>
                    <div>
                        <label class="{{ $labelClass }}" for="team_code">
                            Team Code
                            <input class="{{ $inputClass }}" id="team_code" type="text" name="team_code" value="{{ old('team_code', $teamCode) }}" placeholder="Enter the code shared by your team lead">
                        </label>
                    </div>
                </div>

                <!-- Participant Fields -->
                <div id="participantFields" class="hidden space-y-5 border-t border-black/5 pt-5">
                    <h3 class="text-lg font-semibold text-neutral-800">Participant Details</h3>

                    <div class="flex flex-col gap-4 md:flex-row">
                        <label class="{{ $labelClass }} w-full" for="name">
                            Name
                            <input class="{{ $inputClass }}" id="name" type="text" name="name" value="{{ old('name') }}" required placeholder="Jane Doe">
                        </label>
                        <label class="{{ $labelClass }} w-full" for="age">
                            Age
                            <input class="{{ $inputClass }}" id="age" type="number" name="age" value="{{ old('age') }}" required placeholder="25">
                        </label>
                    </div>

                    <div class="flex flex-col gap-4 md:flex-row">
                        <label class="{{ $labelClass }} w-full" for="email">
                            Email
                            <input class="{{ $inputClass }}" id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com (Work email preferred)">
                        </label>
                        <label class="{{ $labelClass }} w-full" for="phone">
                            Phone
                            <input class="{{ $inputClass }}" id="phone" type="text" name="phone" value="{{ old('phone') }}" required placeholder="0123456789">
                        </label>
                    </div>

                    <div class="flex flex-col gap-4 md:flex-row">
                        <label class="{{ $labelClass }} w-full" for="gender">
                            Gender
                            <select class="{{ $inputClass }}" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </label>
                        <label class="{{ $labelClass }} w-full" for="company_name">
                            Company Name
                            <input class="{{ $inputClass }}" id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Acme Inc.">
                        </label>
                    </div>

                    <div class="flex flex-col gap-4 md:flex-row">
                        <label class="{{ $labelClass }} w-full" for="portfolio_url">
                            Portfolio URL
                            <input class="{{ $inputClass }}" id="portfolio_url" type="url" name="portfolio_url" value="{{ old('portfolio_url') }}" placeholder="https://portfolio.com">
                        </label>
                        <label class="{{ $labelClass }} w-full" for="linkedin_url">
                            LinkedIn URL (Optional)
                            <input class="{{ $inputClass }}" id="linkedin_url" type="url" name="linkedin_url" value="{{ old('linkedin_url') }}" placeholder="https://linkedin.com/in/jane">
                        </label>
                    </div>

                    <div class="flex flex-col gap-4 md:flex-row">
                        <label class="{{ $labelClass }} w-full" for="role">
                            Role
                            <select class="{{ $inputClass }}" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="Developer" {{ old('role') == 'Developer' ? 'selected' : '' }}>Developer</option>
                                <option value="Product Manager" {{ old('role') == 'Product Manager' ? 'selected' : '' }}>Product Manager</option>
                                <option value="Designer" {{ old('role') == 'Designer' ? 'selected' : '' }}>Designer</option>
                                <option value="Data Scientist" {{ old('role') == 'Data Scientist' ? 'selected' : '' }}>Data Scientist</option>
                                <option value="DevOps" {{ old('role') == 'DevOps' ? 'selected' : '' }}>DevOps</option>
                            </select>
                        </label>
                        <label class="{{ $labelClass }} w-full" for="years_of_experience">
                            Years of Experience
                            <input class="{{ $inputClass }}" id="years_of_experience" type="number" name="years_of_experience" value="{{ old('years_of_experience') }}" required placeholder="3">
                        </label>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}" for="background">
                            Development Background
                            <textarea class="{{ $inputClass }} resize-none" id="background" name="background" rows="3" placeholder="Tell us something you built that you are proud of">{{ old('background') }}</textarea>
                        </label>
                    </div>

                    <div class="flex flex-col gap-4 md:flex-row">
                        <label class="{{ $labelClass }} w-full" for="tshirt_size">
                            T-Shirt Size
                            <select class="{{ $inputClass }}" id="tshirt_size" name="tshirt_size" required>
                                <option value="">Select Size</option>
                                <option value="S" {{ old('tshirt_size') == 'S' ? 'selected' : '' }}>S</option>
                                <option value="M" {{ old('tshirt_size') == 'M' ? 'selected' : '' }}>M</option>
                                <option value="L" {{ old('tshirt_size') == 'L' ? 'selected' : '' }}>L</option>
                                <option value="XL" {{ old('tshirt_size') == 'XL' ? 'selected' : '' }}>XL</option>
                                <option value="XXL" {{ old('tshirt_size') == 'XXL' ? 'selected' : '' }}>XXL</option>
                            </select>
                        </label>
                        <label class="{{ $labelClass }} w-full" for="dietary_restrictions">
                            Dietary Restrictions
                            <input class="{{ $inputClass }}" id="dietary_restrictions" type="text" name="dietary_restrictions" value="{{ old('dietary_restrictions') }}" placeholder="None">
                        </label>
                    </div>

                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="form-checkbox text-[#0064C8] focus:ring-[#0064C8] rounded" name="mandatory_attendance_confirmed" required {{ old('mandatory_attendance_confirmed') ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-neutral-700">I confirm mandatory attendance on 7–8 Feb 2026</span>
                        </label>
                    </div>

                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="hidden" name="looking_for_job" value="0">
                            <input type="checkbox" class="form-checkbox text-[#0064C8] focus:ring-[#0064C8] rounded" name="looking_for_job" id="looking_for_job" value="1"
                                onchange="toggleResume()" {{ old('looking_for_job') ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-neutral-700">Looking for job?</span>
                        </label>
                    </div>

                    <div class="hidden" id="resumeField">
                        <label class="{{ $labelClass }}" for="resume">
                            Upload Resume <span class="font-normal text-neutral-500">(Required if looking for job)</span>
                            <input class="{{ $inputClass }} file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#003064]/10 file:text-[#003064] hover:file:bg-[#003064]/20" id="resume" type="file" name="resume">
                        </label>
                    </div>

                    <button
                        class="mt-6 w-full rounded-xl bg-[#003064] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#FFAF00] hover:text-[#003064] disabled:opacity-50"
                        type="submit">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Team Code Modal -->
    @if(session('team_created_code'))
        <div id="teamCodeModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-center">
                <div class="mb-4 flex justify-center">
                    <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Team Created Successfully!</h3>
                <p class="text-gray-600 mb-6">Share this code with your teammates so they can join your team.</p>
                
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 mb-6 flex items-center justify-between">
                    <span class="font-mono text-xl font-bold text-gray-900 tracking-wider" id="teamCodeDisplay">{{ session('team_created_code') }}</span>
                    <button onclick="copyTeamCode()" class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        Copy
                    </button>
                </div>

                <button onclick="document.getElementById('teamCodeModal').remove()" class="w-full bg-[#0064C8] text-white font-semibold py-3 px-4 rounded-xl hover:bg-[#FFAF00] hover:text-[#003064] transition">
                    Done
                </button>
            </div>
        </div>
        <script>
            function copyTeamCode() {
                const code = document.getElementById('teamCodeDisplay').innerText;
                navigator.clipboard.writeText(code);
                alert('Team code copied to clipboard!');
            }
        </script>
    @endif

    <script>
        function toggleForm() {
            const type = document.querySelector('input[name="registration_type"]:checked')?.value;
            const createTeam = document.getElementById('createTeamFields');
            const joinTeam = document.getElementById('joinTeamFields');
            const participant = document.getElementById('participantFields');

            if (!type) return;

            participant.classList.remove('hidden');

            if (type === 'create_team') {
                createTeam.classList.remove('hidden');
                joinTeam.classList.add('hidden');
            } else if (type === 'join_team') {
                createTeam.classList.add('hidden');
                joinTeam.classList.remove('hidden');
            } else {
                createTeam.classList.add('hidden');
                joinTeam.classList.add('hidden');
            }
        }

        function toggleLookingFor() {
            const status = document.getElementById('team_status').value;
            const desc = document.getElementById('lookingForDesc');
            if (status === 'looking_for_teammates') {
                desc.classList.remove('hidden');
            } else {
                desc.classList.add('hidden');
            }
        }

        function toggleResume() {
            const looking = document.getElementById('looking_for_job').checked;
            const resume = document.getElementById('resumeField');
            if (looking) {
                resume.classList.remove('hidden');
            } else {
                resume.classList.add('hidden');
            }
        }

        // Run on load to set initial state
        document.addEventListener('DOMContentLoaded', () => {
            toggleForm();
            toggleLookingFor();
            toggleResume();
        });
    </script>
@endsection