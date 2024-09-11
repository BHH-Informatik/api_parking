Hallo {{ $user->first_name }} {{ $user->last_name }},<br>
<br>
für Ihr Konto wurde ein Zurücksetzungsantrag gestellt. Klicken Sie auf den folgenden Link, um Ihr Passwort zurückzusetzen:<br>
<a href="{{ $link }}">{{ $link }}</a><br>
<br>
Wenn Sie diesen Antrag nicht gestellt haben, können Sie diese E-Mail ignorieren.<br>
