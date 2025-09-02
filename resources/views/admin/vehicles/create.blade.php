<!-- resources/views/admin/vehicles/create.blade.php -->
@extends('layouts.app')

@section('title', '車両・重機 新規登録')

@section('content')
<div class="p-6 bg-white rounded-xl shadow-lg">
    <h2 class="text-xl font-bold text-gray-800 mb-6">車両・重機 新規登録</h2>

    <form action="{{ route('admin.vehicles.store') }}" method="POST">
        @csrf
        @include('admin.vehicles._form', ['submitButtonText' => '登録する'])
    </form>
</div>
@endsection