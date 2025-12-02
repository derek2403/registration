<!DOCTYPE html>
<html>

<head>
    <title>Hackathon Registration Confirmed</title>
</head>

<body>
    <h1>Registration Confirmed - Team Joined</h1>
    <p>Hi {{ $participant->name }},</p>
    <p>You have successfully registered and joined the team <strong>{{ $team->name }}</strong>.</p>
    <p>Team Code: {{ $team->code }}</p>
    <p>We will be reviewing your application and revert back to you shortly. Please ask your teammate to fill in as soon
        as possible to increase chances of shortlisting.</p>
</body>

</html>