@props(['viticulturist'])

<option value="{{ $viticulturist->id }}">
    {{ $viticulturist->name }}@if($viticulturist->id === auth()->id()) (Yo)@endif
</option>
