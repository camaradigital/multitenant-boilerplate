@props(['level' => 'h1'])

<h1 {{ $attributes->class(['header']) }}>
{{ $slot }}
</h1>
