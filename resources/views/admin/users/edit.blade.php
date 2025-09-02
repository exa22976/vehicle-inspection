<!-- resources/views/admin/users/edit.blade.php -->
@extends('layouts.app')

@section('title', '担当者 編集')

@section('content')
<div class="p-6 bg-white rounded-xl shadow-lg">
    <h2 class="text-xl font-bold text-gray-800 mb-6">担当者 編集</h2>

    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.users._form', ['user' => $user, 'submitButtonText' => '更新する'])
    </form>
</div>
@endsection