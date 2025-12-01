@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h1 class="text-2xl font-bold mb-6 text-center">Hackathon Registration</h1>

        <form action="{{ route('registration.store') }}" method="POST" enctype="multipart/form-data" id="registrationForm">
            @csrf

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">How would you like to register?</label>
                <div class="flex gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" class="form-radio" name="registration_type" value="create_team"
                            onchange="toggleForm()" {{ old('registration_type') == 'create_team' ? 'checked' : '' }}>
                        <span class="ml-2">Create a Team</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" class="form-radio" name="registration_type" value="join_team"
                            onchange="toggleForm()" {{ old('registration_type') == 'join_team' || $teamCode ? 'checked' : '' }}>
                        <span class="ml-2">Join a Team</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" class="form-radio" name="registration_type" value="solo" onchange="toggleForm()"
                            {{ old('registration_type') == 'solo' ? 'checked' : '' }}>
                        <span class="ml-2">Register Solo</span>
                    </label>
                </div>
            </div>

            <!-- Team Creation Fields -->
            <div id="createTeamFields" class="hidden border-b pb-4 mb-4">
                <h3 class="text-lg font-semibold mb-4">Team Details</h3>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="team_name">Team Name</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="team_name" type="text" name="team_name" value="{{ old('team_name') }}">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="team_status">Team Status</label>
                    <select
                        class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="team_status" name="team_status" onchange="toggleLookingFor()">
                        <option value="not_looking_for_teammates" {{ old('team_status') == 'not_looking_for_teammates' ? 'selected' : '' }}>Not looking for teammates</option>
                        <option value="looking_for_teammates" {{ old('team_status') == 'looking_for_teammates' ? 'selected' : '' }}>Looking for teammates</option>
                    </select>
                </div>
                <div class="mb-4 hidden" id="lookingForDesc">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="looking_for_description">What are you
                        looking for?</label>
                    <textarea
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="looking_for_description"
                        name="looking_for_description">{{ old('looking_for_description') }}</textarea>
                </div>
            </div>

            <!-- Join Team Fields -->
            <div id="joinTeamFields" class="hidden border-b pb-4 mb-4">
                <h3 class="text-lg font-semibold mb-4">Join Team</h3>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="team_code">Team Code</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="team_code" type="text" name="team_code" value="{{ old('team_code', $teamCode) }}">
                </div>
            </div>

            <!-- Participant Fields -->
            <div id="participantFields" class="hidden">
                <h3 class="text-lg font-semibold mb-4">Participant Details</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Name</label>
                        <input
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="name" type="text" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="age">Age</label>
                        <input
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="age" type="number" name="age" value="{{ old('age') }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                        <input
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="email" type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone</label>
                        <input
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="phone" type="text" name="phone" value="{{ old('phone') }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="gender">Gender</label>
                        <select
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="company_name">Company Name</label>
                        <input
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="company_name" type="text" name="company_name" value="{{ old('company_name') }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="portfolio_url">Portfolio URL</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="portfolio_url" type="url" name="portfolio_url" value="{{ old('portfolio_url') }}">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="linkedin_url">LinkedIn URL
                        (Optional)</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="linkedin_url" type="url" name="linkedin_url" value="{{ old('linkedin_url') }}">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="role">Role</label>
                        <select
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="Developer" {{ old('role') == 'Developer' ? 'selected' : '' }}>Developer</option>
                            <option value="Product Manager" {{ old('role') == 'Product Manager' ? 'selected' : '' }}>Product
                                Manager</option>
                            <option value="Designer" {{ old('role') == 'Designer' ? 'selected' : '' }}>Designer</option>
                            <option value="Data Scientist" {{ old('role') == 'Data Scientist' ? 'selected' : '' }}>Data
                                Scientist</option>
                            <option value="DevOps" {{ old('role') == 'DevOps' ? 'selected' : '' }}>DevOps</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="years_of_experience">Years of
                            Experience</label>
                        <input
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="years_of_experience" type="number" name="years_of_experience"
                            value="{{ old('years_of_experience') }}" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="background">Development
                        Background</label>
                    <textarea
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="background" name="background"
                        placeholder="Tell us something you built that you are proud of">{{ old('background') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="tshirt_size">T-Shirt Size</label>
                        <select
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="tshirt_size" name="tshirt_size" required>
                            <option value="">Select Size</option>
                            <option value="S" {{ old('tshirt_size') == 'S' ? 'selected' : '' }}>S</option>
                            <option value="M" {{ old('tshirt_size') == 'M' ? 'selected' : '' }}>M</option>
                            <option value="L" {{ old('tshirt_size') == 'L' ? 'selected' : '' }}>L</option>
                            <option value="XL" {{ old('tshirt_size') == 'XL' ? 'selected' : '' }}>XL</option>
                            <option value="XXL" {{ old('tshirt_size') == 'XXL' ? 'selected' : '' }}>XXL</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="dietary_restrictions">Dietary
                            Restrictions</label>
                        <input
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="dietary_restrictions" type="text" name="dietary_restrictions"
                            value="{{ old('dietary_restrictions') }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox" name="mandatory_attendance_confirmed" required {{ old('mandatory_attendance_confirmed') ? 'checked' : '' }}>
                        <span class="ml-2">I confirm mandatory attendance on 7–8 Feb 2026</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="hidden" name="looking_for_job" value="0">
                        <input type="checkbox" class="form-checkbox" name="looking_for_job" id="looking_for_job" value="1"
                            onchange="toggleResume()" {{ old('looking_for_job') ? 'checked' : '' }}>
                        <span class="ml-2">Looking for job?</span>
                    </label>
                </div>

                <div class="mb-6 hidden" id="resumeField">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="resume">Upload Resume (Required if
                        looking for job)</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="resume" type="file" name="resume">
                </div>

                <div class="flex items-center justify-between">
                    <button
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="submit">
                        Register
                    </button>
                </div>
            </div>
        </form>
    </div>

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