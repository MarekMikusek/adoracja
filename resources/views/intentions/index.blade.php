@extends('layouts.app')

@section('styles')
@endsection

@section('content')
    <div class="container">

        <div class="card">
            <div class="card-header">
                Intencje modlitewne
            </div>
            <div class="card-body">
                <button id="add_intention-btn" class="btn btn-success">Dodaj intencję</button>
                <table class="table">
                    <thead>
                        <th>Intencja</th>
                        <th>Moje intencje</th>
                        <th>Ilość osób <br>modlących się</th>
                        @auth
                            <th>Modlę się</th>
                        @endauth
                    </thead>
                    <tbody>
                        @forelse ($intentions as $intention_id => $intention)
                            <tr>
                                <td>{{ $intention['intention'] }}</td>
                                <td><input type="checkbox" onclick="return false;" @if(isset($intention['user_id']) && $intention['user_id'] == $user_id) checked @endif></td>
                                <td>{{ $intention['users'] }}</td>
                                @auth
                                    <td><input type="checkbox" class="intention_pray" data-intention_id="{{ $intention_id }}"
                                            @if ($intention['isMyIntention']) checked="true" @endif></td>
                                @endauth
                            </tr>
                            @empty
                            <tr><td>Brak intencji</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add intention modal -->
        <div class="modal fade" id="add-intention" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Proszę o modlitwę w intencji:</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="add-intention-form">
                            <div class="mb-4">
                                <input type="text" class="form-control" id="intention-text" name="date"
                                    placeholder="Wpisz treść">
                            </div>
                            <button type="submit" class="btn btn-primary">Zapisz</button>
                        </form>
                        <small>Prosimy nie podawać w intencjach informacji umożliwiających identyfikację osób: nazwisk, zbyt
                            dokładnych opisów sytuacji, itp. Prosimy o możliwie krótkie intencje:
                            np. w intencji uzdrowienia z choroby serca dla Anny, o poprawę relacji z żoną dla
                            Zbigniewa. Intencje dodane przez osóby niezalogowane będą wyświetlane po zatwierdzeniu przez koorodynatora. </small>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('scripts')
        <script>
            $(document).ready(function() {

                $('#add_intention-btn').on('click', () => {
                    $('#add-intention').modal('show');
                });

                $('#add-intention-form').on('submit', function(event) {
                    event.preventDefault();
                    const url = "{{ route('intention.save') }}";
                    const intention = $('input#intention-text').val();

                    $.ajax({
                        method: "POST",
                        url: url,
                        data:{
                            _token: "{{ csrf_token() }}",
                            intention: intention
                        },
                        success: function(response){
                            location.reload();
                        },
                        error: function(xhr) {
                            alert('Błąd zapisu');
                        }
                    });
                });

                $('.intention_pray').on('change', function() {
                    const intention_id = $(this).data('intention_id');
                    const is_prayer = $(this).prop('checked') === true ? 1 : 0;
                    const url = "{{ route('intentions.is_prayer') }}"

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            intention_id: intention_id,
                            is_prayer: is_prayer
                        },
                        success: function(response) {
                            location.reload();
                        },
                        error: function(xhr) {
                            alert('Błąd');
                        }
                    });
                });
            });
        </script>
    @endsection
