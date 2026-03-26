@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ rtrim(config('app.url'), '/') }}/images/logo.png" class="logo" alt="Agro365" style="max-height: 60px; width: auto;">
</a>
</td>
</tr>
