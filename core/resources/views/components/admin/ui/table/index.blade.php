@props(['tableClass' => 'table  table--responsive--lg table-hover'])
<div class="table-body">
    <table {{ $attributes->merge(['class' => $tableClass]) }} >
        {{ $slot }}
    </table>
</div>
