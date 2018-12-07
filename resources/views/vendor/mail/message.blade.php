@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => url('/')])
            <img src="{{ url('/') }}/images/background/splash_logo.png" style="max-width: 150px">
        @endcomponent
    @endslot

    {{-- Body --}}
    @if (strtolower($country) === 'sweden')
        <b>Hej, {{ $name }}!</b>
    @else
        <b>Hello, {{ $name }}!</b>
    @endif
    
    @foreach ($contents as $contents)
{{ $contents }}
    @endforeach
    
    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    @if (strtolower($country) === 'sweden')
Med vänlig hälsning,
    @else
Best Regards,
    @endif    
    {{ config('app.name') }}
    
    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        @endcomponent
    @endslot
@endcomponent
