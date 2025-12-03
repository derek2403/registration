@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50 p-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                <p class="text-gray-500 mt-1">Manage hackathon registrations and teams.</p>
            </div>
            <div class="flex gap-3">
                <button onclick="openEmailModal()" class="bg-white border border-gray-300 text-gray-700 font-medium py-2 px-4 rounded-xl shadow-sm hover:bg-gray-50 transition">
                    Email Actions
                </button>
                <a href="{{ route('admin.export') }}" class="bg-[#003064] hover:bg-[#FFAF00] hover:text-[#003064] text-white font-medium py-2 px-4 rounded-xl shadow-sm transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-sm font-medium text-gray-500">Total Participants</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalParticipants }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-sm font-medium text-gray-500">Teams / Solo</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalTeams }} <span class="text-lg text-gray-400 font-normal">/ {{ $totalSolo }}</span></p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-sm font-medium text-gray-500">Accepted Participants</p>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ $acceptedCount }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-sm font-medium text-gray-500">Rejected Participants</p>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ $rejectedCount }}</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button onclick="switchTab('team-view')" id="tab-team-view" class="border-[#0064C8] text-[#0064C8] whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Team View
                </button>
                <button onclick="switchTab('individual-view')" id="tab-individual-view" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Individual View
                </button>
            </nav>
        </div>

        <!-- Team View Content -->
        <div id="team-view" class="tab-content">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Teams</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                @foreach($teams as $team)
                    <div onclick="openTeamModal({{ json_encode($team) }}, {{ json_encode($team->participants) }})" 
                         class="group bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition cursor-pointer relative overflow-hidden">
                        
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 group-hover:text-[#0064C8] transition-colors">{{ $team->name }}</h3>
                                <p class="text-xs text-gray-500 font-mono mt-1">{{ $team->code }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($team->status === 'accepted')
                                    <div class="h-3 w-3 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]" title="Team Accepted"></div>
                                @elseif($team->status === 'rejected')
                                    <div class="h-3 w-3 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.6)]" title="Team Rejected"></div>
                                @else
                                    <div class="h-3 w-3 rounded-full bg-gray-300" title="Pending"></div>
                                @endif
                            </div>
                        </div>

                        @if($team->team_status === 'looking_for_teammates')
                            <div class="mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Looking for teammates
                                </span>
                                @if($team->looking_for_description)
                                    <p class="mt-2 text-xs text-gray-600 line-clamp-2 group-hover:text-gray-900 transition-colors">
                                        {{ $team->looking_for_description }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <div class="flex items-center justify-between mt-auto pt-2">
                            <div class="flex -space-x-2 overflow-hidden">
                                @foreach($team->participants as $p)
                                    <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600" title="{{ $p->name }}">
                                        {{ substr($p->name, 0, 1) }}
                                    </div>
                                @endforeach
                            </div>
                            <span class="text-sm font-medium text-gray-500">{{ $team->participants->count() }}/5</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <h2 class="text-xl font-bold text-gray-900 mb-4">Solo Participants</h2>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($solos as $p)
                            <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="openParticipantModal({{ json_encode($p) }})">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                            {{ substr($p->name, 0, 1) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $p->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $p->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p->role }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($p->status === 'accepted')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Accepted</span>
                                    @elseif($p->status === 'rejected')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" onclick="event.stopPropagation()">
                                    <div class="flex justify-end gap-2">
                                        <form action="{{ route('admin.approve') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="emails[]" value="{{ $p->email }}">
                                            <button type="submit" class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-lg transition">Approve</button>
                                        </form>
                                        <form action="{{ route('admin.reject') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="emails[]" value="{{ $p->email }}">
                                            <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-lg transition">Reject</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Individual View Content -->
        <div id="individual-view" class="tab-content hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Experience</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($teams as $team)
                                @foreach($team->participants as $p)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $p->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $p->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $team->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p->role }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p->years_of_experience }} years</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($p->status === 'accepted')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Accepted</span>
                                            @elseif($p->status === 'rejected')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button onclick="openParticipantModal({{ json_encode($p) }})" class="text-indigo-600 hover:text-indigo-900">View</button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            @foreach($solos as $p)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $p->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $p->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 italic">Solo</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p->role }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p->years_of_experience }} years</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($p->status === 'accepted')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Accepted</span>
                                        @elseif($p->status === 'rejected')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button onclick="openParticipantModal({{ json_encode($p) }})" class="text-indigo-600 hover:text-indigo-900">View</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Participant Modal -->
    <div id="participantModal" class="fixed inset-0 bg-black/50 hidden z-[60] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900">Participant Details</h3>
                <button onclick="closeModal('participantModal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6" id="modalContent">
                <!-- Content populated by JS -->
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <button onclick="closeModal('participantModal')" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50">Close</button>
            </div>
        </div>
    </div>

    <!-- Team Modal -->
    <div id="teamModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900" id="teamModalTitle">Team Details</h3>
                <button onclick="closeModal('teamModal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6">
                <div id="teamModalContent" class="mb-6"></div>
                <h4 class="font-bold text-gray-900 mb-4">Team Members</h4>
                <div id="teamMembersList" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <form action="{{ route('admin.approve') }}" method="POST" id="approveTeamForm">
                    @csrf
                    <div id="approveTeamInputs"></div>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700">Approve Team</button>
                </form>
                <form action="{{ route('admin.reject') }}" method="POST" id="rejectTeamForm">
                    @csrf
                    <div id="rejectTeamInputs"></div>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700">Reject Team</button>
                </form>
                <button onclick="closeModal('teamModal')" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50">Close</button>
            </div>
        </div>
    </div>

    <!-- Email Actions Modal -->
    <div id="emailModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900">Email Actions</h3>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Accepted Emails ({{ count($approvalListEmails) }})</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="{{ implode(', ', $approvalListEmails) }}" class="flex-1 rounded-lg border-gray-300 bg-gray-50 text-sm" id="acceptedEmails">
                        <button onclick="copyToClipboard('acceptedEmails')" class="px-3 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-medium hover:bg-blue-100">Copy</button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rejected Emails ({{ count($rejectionListEmails) }})</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="{{ implode(', ', $rejectionListEmails) }}" class="flex-1 rounded-lg border-gray-300 bg-gray-50 text-sm" id="rejectedEmails">
                        <button onclick="copyToClipboard('rejectedEmails')" class="px-3 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-medium hover:bg-blue-100">Copy</button>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <form action="{{ route('admin.send_emails') }}" method="POST" onsubmit="return confirm('Are you sure you want to send emails to all accepted and rejected participants?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-[#003064] text-white rounded-xl hover:bg-[#FFAF00] hover:text-[#003064]">Send Emails</button>
                </form>
                <button onclick="closeModal('emailModal')" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50">Close</button>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.getElementById(tabId).classList.remove('hidden');
            
            // Update tab styles
            const tabs = ['team-view', 'individual-view'];
            tabs.forEach(id => {
                const btn = document.getElementById('tab-' + id);
                if (id === tabId) {
                    btn.classList.remove('border-transparent', 'text-gray-500');
                    btn.classList.add('border-[#0064C8]', 'text-[#0064C8]');
                } else {
                    btn.classList.add('border-transparent', 'text-gray-500');
                    btn.classList.remove('border-[#0064C8]', 'text-[#0064C8]');
                }
            });
        }

        function openParticipantModal(data) {
            const modal = document.getElementById('participantModal');
            const content = document.getElementById('modalContent');
            
            let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">';
            const fields = {
                'name': 'Name', 'email': 'Email', 'phone': 'Phone', 'age': 'Age', 
                'gender': 'Gender', 'role': 'Role', 'company_name': 'Company', 
                'years_of_experience': 'Experience', 'tshirt_size': 'T-Shirt', 
                'dietary_restrictions': 'Dietary', 'portfolio_url': 'Portfolio', 
                'linkedin_url': 'LinkedIn', 'background': 'Background'
            };

            for (const [key, label] of Object.entries(fields)) {
                if (data[key]) {
                    html += `<div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">${label}</p>
                        <p class="text-gray-900 font-medium break-words">${data[key]}</p>
                    </div>`;
                }
            }
            
            if (data.resume_path) {
                html += `<div class="col-span-full mt-4">
                    <a href="/storage/${data.resume_path}" target="_blank" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        View Resume
                    </a>
                </div>`;
            }
            html += '</div>';

            content.innerHTML = html;
            modal.classList.remove('hidden');
        }

        function openTeamModal(team, members) {
            const modal = document.getElementById('teamModal');
            document.getElementById('teamModalTitle').innerText = team.name;
            
            // Team Info
            let infoHtml = `<div class="grid grid-cols-2 gap-4 mb-4">
                <div><span class="text-gray-500 text-sm">Code:</span> <span class="font-mono font-bold">${team.code}</span></div>
                <div><span class="text-gray-500 text-sm">Status:</span> <span class="font-medium">${team.team_status}</span></div>
            </div>`;
            if (team.looking_for_description) {
                infoHtml += `<div class="bg-blue-50 p-4 rounded-xl text-blue-900 text-sm">
                    <span class="font-bold">Looking for:</span> ${team.looking_for_description}
                </div>`;
            }
            document.getElementById('teamModalContent').innerHTML = infoHtml;

            // Members
            let membersHtml = '';
            const approveInputs = document.getElementById('approveTeamInputs');
            const rejectInputs = document.getElementById('rejectTeamInputs');
            approveInputs.innerHTML = '';
            rejectInputs.innerHTML = '';

            members.forEach(p => {
                // Add hidden inputs for actions
                approveInputs.innerHTML += `<input type="hidden" name="emails[]" value="${p.email}">`;
                rejectInputs.innerHTML += `<input type="hidden" name="emails[]" value="${p.email}">`;

                membersHtml += `<div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-gray-900">${p.name}</p>
                            <p class="text-sm text-gray-500">${p.role}</p>
                        </div>
                        <button onclick='openParticipantModal(${JSON.stringify(p)})' class="text-xs bg-white border border-gray-200 px-2 py-1 rounded hover:bg-gray-50">Details</button>
                    </div>
                    <div class="mt-2 text-xs text-gray-400">${p.email}</div>
                </div>`;
            });
            document.getElementById('teamMembersList').innerHTML = membersHtml;

            modal.classList.remove('hidden');
        }

        function openEmailModal() {
            document.getElementById('emailModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function copyToClipboard(elementId) {
            const copyText = document.getElementById(elementId);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            alert("Copied emails to clipboard");
        }

        // Close modal on outside click
        window.onclick = function(event) {
            if (event.target.classList.contains('fixed')) {
                event.target.classList.add('hidden');
            }
        }
    </script>
@endsection