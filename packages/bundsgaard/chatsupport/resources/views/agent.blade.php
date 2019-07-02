@extends('chatsupport::layout')

@section('content')
    <chatsupport-agent :rooms='@json($rooms)'></chatsupport-agent>
@endsection
