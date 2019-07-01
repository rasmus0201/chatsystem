@extends('chatsupport::layout')

@section('content')
    <chatsupport-user :rooms='@json($rooms)'></chatsupport-user>
@endsection
