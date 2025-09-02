@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">車両・重機マスター編集</h2>
        <a href="{{ route('admin.vehicles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            一覧へ戻る
        </a>
    </div>

    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white p-6 rounded-lg shadow">
        <form action="{{ route('admin.vehicles.update', $vehicle) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.vehicles._form')

            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    変更内容を保存
                </button>
            </div>
        </form>
    </div>
</div>
@endsection