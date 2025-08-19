<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vaccination Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>Animal Vaccination Report</h2>
    <p>Generated on: {{ now()->format('M d, Y h:i A') }}</p>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Owner Name</th>
                <th>Animal Name</th>
                <th>Species</th>
                <th>Breed</th>
                <th>Vaccine</th>
                <th>Dose</th>
                <th>Date Given</th>
                <th>Administered by</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vaccinationReports as $index => $report)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $report->owner_name }}</td>
                    <td>{{ $report->animal_name }}</td>
                    <td>{{ ucfirst($report->animal_type) }}</td>
                    <td>{{ $report->breed }}</td>
                    <td>{{ $report->vaccine_name }}</td>
                    <td>{{ $report->dose }}</td>
                    <td>{{ \Carbon\Carbon::parse($report->date_given)->format('M d, Y') }}</td>
                    <td>{{ $report->administrator }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
