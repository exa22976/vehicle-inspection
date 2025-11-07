@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="flex justify-center px-6 my-12">
        <div class="w-full xl:w-3/4 lg:w-11/12 flex justify-center">
            <div class="w-full lg:w-7/12 bg-white p-5 rounded-lg lg:rounded-l-none shadow-xl">
                <h3 class="pt-4 text-2xl text-center font-bold">管理者ログイン</h3>
                <form class="px-8 pt-6 pb-8 mb-4 bg-white rounded" method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-bold text-gray-700" for="email">
                            メールアドレス
                        </label>
                        <input
                            class="w-full px-3 py-2 text-sm leading-tight text-gray-700 border rounded shadow appearance-none focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror"
                            id="email"
                            type="email"
                            placeholder="email@csyam.com"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus />
                        @error('email')
                        <p class="text-xs italic text-red-500 mt-2">{{ $message }}</p>
                        @enderror
                    </div>


                    <div class="mb-6 text-center">
                        <button class="w-full px-4 py-2 font-bold text-white bg-blue-600 rounded-full hover:bg-blue-700 focus:outline-none focus:shadow-outline" type="submit">
                            ログイン
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection