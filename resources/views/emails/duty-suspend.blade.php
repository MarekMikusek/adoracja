<p>Szczęść Boże {{ $user->first_name }}</p>

<p>Zawiesiłaś/eś swoją posługę od {{ $user->suspend_from->format('Y-m-d') }} @if($user->suspend_to) do {{ $user->suspend_to->format('Y-m-d') }}. @else bezterminowo. @endif </p>
<br>
<p>Pozdrawiamy</p>
<p>Administratorzy</p>