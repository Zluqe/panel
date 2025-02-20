@extends('templates/wrapper', [
    'css' => ['body' => 'bg-neutral-800'],
])

@section('container')
    <script>
        window.shouldShowDiscordPopup = {!! json_encode($shouldShowPopup) !!};
    </script>

    <div id="modal-portal">
        @include('partials.discord_popup')
    </div>
    <div id="app"></div>
@endsection
