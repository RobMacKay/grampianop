@php
    $data = json_decode($data??'') ?? [];
@endphp

<div {{$attributes->merge(['class' => 'hidden font-normal bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 relative z-50 transition-opacity font-semibold'])}}>
    <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdownLargeButton">
        @foreach($data as $key => $item)
        <li>
            <a href="/{{$key}}"
                class="block px-4 py-2 hover:bg-gray-100">{{$item}}</a>
        </li>
        @endforeach
    </ul>
</div>
