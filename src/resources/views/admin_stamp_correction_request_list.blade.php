@extends('layout.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_stamp_correction_list.css') }}">
@endsection

@section('content')

<form action="/stamp_correction_request/list" method="get"></form>



@endsection