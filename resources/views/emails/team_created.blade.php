<!DOCTYPE html>
<html>

<head>
    <title>Hackathon Registration Confirmed</title>
</head>

<body>
    <h1>Registration Confirmed - Team Created</h1>
    <p>Hi {{ $participant->name }},</p>
    <p>You have successfully registered and created the team <strong>{{ $team->name }}</strong>.</p>
    <p>Your Team Code is: <strong>{{ $team->code }}</strong></p>
    <p>Invite your teammates using this link:</p>
    <p><a
            href="{{ route('registration.join', ['team_code' => $team->code]) }}">{{ route('registration.join', ['team_code' => $team->code]) }}</a>
    </p>
    <p>We will be reviewing your application and revert back to you shortly. Please ask your teammate to fill in as soon
        as possible to increase chances of shortlisting.</p>
</body>

</html>