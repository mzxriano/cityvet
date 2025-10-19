<div style="font-family: Arial, sans-serif;">
    <h2 style="color: #d32f2f;">Role Request Rejected</h2>
    <p>Dear {{ $user->first_name }},</p>
    <p>Your request for the role <strong>{{ ucwords(str_replace('_', ' ', $role->name)) }}</strong> has been rejected by the admin.</p>
    <p><strong>Reason:</strong></p>
    <blockquote style="background: #f8d7da; padding: 10px; border-radius: 5px;">{{ $messageText }}</blockquote>
    <p>If you have questions, please contact support.</p>
    <p>Thank you,<br>CityVet Admin Team</p>
</div>
