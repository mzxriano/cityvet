@extends('layouts.layout')

@section('content')
  <h1 class="title-style mb-6">Barangay</h1>

  <!-- Top Bar -->
  <div class="flex justify-end mb-4">
    <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">+ New Activity</button>
  </div>

  <!-- Activities Table -->
  <div class="bg-white p-6 rounded-xl shadow-md overflow-x-auto">
    <table class="table-auto w-full text-left border-collapse">
      <thead class="bg-gray-200 text-gray-800">
        <tr>
          <th class="px-4 py-2 rounded-tl-xl">No.</th>
          <th class="px-4 py-2">Barangay</th>
          <th class="px-4 py-2">Time</th>
          <th class="px-4 py-2">Date</th>
          <th class="px-4 py-2">Status</th>
          <th class="px-4 py-2 rounded-tr-xl">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($activities as $index => $activity)
          <tr class="border-t hover:bg-gray-50 text-gray-700">
            <td class="px-4 py-2">{{ $index + 1 }}</td>
            <td class="px-4 py-2">{{ $activity->barangay->name ?? 'N/A' }}</td>
            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($activity->time)->format('h:i A') }}</td>
            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($activity->date)->format('F j, Y') }}</td>
            <td class="px-4 py-2">{{ ucfirst($activity->status) }}</td>
            <td class="px-4 py-2 text-center">
              <button class="text-blue-600 hover:underline">Edit</button>
            </td>
          </tr>
        @endforeach

        @if($activities->isEmpty())
          <tr>
            <td colspan="6" class="px-4 py-4 text-center text-gray-500">No activities found.</td>
          </tr>
        @endif
      </tbody>
    </table>
  </div>
@endsection
