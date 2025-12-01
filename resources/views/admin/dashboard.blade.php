@extends('layouts.app')

@section('content')
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Admin Dashboard</h1>
            <a href="{{ route('admin.export') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Export to CSV
            </a>
        </div>

        @foreach($teamsBySize as $size => $teams)
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4 border-b pb-2">Teams of {{ $size }}</h2>
                @if(empty($teams))
                    <p class="text-gray-500 italic">No teams of this size.</p>
                @else
                    @foreach($teams as $team)
                        <div class="border rounded p-4 mb-4 bg-gray-50">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold">{{ $team->name }} <span
                                            class="text-sm font-normal text-gray-500">({{ $team->code }})</span></h3>
                                    <p class="text-sm text-gray-600">Status: {{ $team->team_status }}</p>
                                    @if($team->looking_for_description)
                                        <p class="text-sm text-gray-600">Looking for: {{ $team->looking_for_description }}</p>
                                    @endif
                                </div>
                                <div class="flex gap-2">
                                    <form action="{{ route('admin.approve') }}" method="POST">
                                        @csrf
                                        @foreach($team->participants as $p)
                                            <input type="hidden" name="emails[]" value="{{ $p->email }}">
                                        @endforeach
                                        <button type="submit"
                                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">Approve
                                            Team</button>
                                    </form>
                                    <form action="{{ route('admin.reject') }}" method="POST">
                                        @csrf
                                        @foreach($team->participants as $p)
                                            <input type="hidden" name="emails[]" value="{{ $p->email }}">
                                        @endforeach
                                        <button type="submit"
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm">Reject
                                            Team</button>
                                    </form>
                                </div>
                            </div>

                            <table class="min-w-full bg-white border">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b text-left">Name</th>
                                        <th class="py-2 px-4 border-b text-left">Email</th>
                                        <th class="py-2 px-4 border-b text-left">Role</th>
                                        <th class="py-2 px-4 border-b text-left">Resume</th>
                                        <th class="py-2 px-4 border-b text-left">Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($team->participants as $participant)
                                        <tr>
                                            <td class="py-2 px-4 border-b">{{ $participant->name }}</td>
                                            <td class="py-2 px-4 border-b">{{ $participant->email }}</td>
                                            <td class="py-2 px-4 border-b">{{ $participant->role }}</td>
                                            <td class="py-2 px-4 border-b">
                                                @if($participant->resume_path)
                                                    <a href="{{ Storage::url($participant->resume_path) }}" target="_blank"
                                                        class="text-blue-500 hover:underline">View</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                <button onclick="openModal({{ json_encode($participant) }})"
                                                    class="text-gray-600 hover:text-gray-900 font-bold text-xl">...</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach

        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4 border-b pb-2">Solo Participants</h2>
            @if($solos->isEmpty())
                <p class="text-gray-500 italic">No solo participants.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b text-left">Name</th>
                                <th class="py-2 px-4 border-b text-left">Email</th>
                                <th class="py-2 px-4 border-b text-left">Role</th>
                                <th class="py-2 px-4 border-b text-left">Resume</th>
                                <th class="py-2 px-4 border-b text-left">Details</th>
                                <th class="py-2 px-4 border-b text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($solos as $participant)
                                <tr>
                                    <td class="py-2 px-4 border-b">{{ $participant->name }}</td>
                                    <td class="py-2 px-4 border-b">{{ $participant->email }}</td>
                                    <td class="py-2 px-4 border-b">{{ $participant->role }}</td>
                                    <td class="py-2 px-4 border-b">
                                        @if($participant->resume_path)
                                            <a href="{{ Storage::url($participant->resume_path) }}" target="_blank"
                                                class="text-blue-500 hover:underline">View</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2 px-4 border-b">
                                        <button onclick="openModal({{ json_encode($participant) }})"
                                            class="text-gray-600 hover:text-gray-900 font-bold text-xl">...</button>
                                    </td>
                                    <td class="py-2 px-4 border-b">
                                        <div class="flex gap-2">
                                            <form action="{{ route('admin.approve') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="emails[]" value="{{ $participant->email }}">
                                                <button type="submit"
                                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded text-xs">Approve</button>
                                            </form>
                                            <form action="{{ route('admin.reject') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="emails[]" value="{{ $participant->email }}">
                                                <button type="submit"
                                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs">Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal -->
    <div id="participantModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modalTitle">Participant Details</h3>
                <div class="mt-2 text-left" id="modalContent">
                    <!-- Content will be populated by JS -->
                </div>
                <div class="items-center px-4 py-3">
                    <button id="ok-btn" onclick="closeModal()"
                        class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(data) {
            const modal = document.getElementById('participantModal');
            const content = document.getElementById('modalContent');

            let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
            for (const [key, value] of Object.entries(data)) {
                if (['created_at', 'updated_at', 'id', 'team_id', 'resume_path'].includes(key)) continue;
                html += `<div class="mb-2">
                        <span class="font-bold capitalize">${key.replace(/_/g, ' ')}:</span>
                        <span class="block text-gray-700">${value || '-'}</span>
                    </div>`;
            }
            html += '</div>';

            content.innerHTML = html;
            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('participantModal').classList.add('hidden');
        }
    </script>
@endsection