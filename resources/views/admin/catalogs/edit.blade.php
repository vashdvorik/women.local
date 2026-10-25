<x-layouts.admin :title="__('Каталог')">
    <x-admin.catalog-form :catalog="$catalog" :action="route('admin.catalogs.update', $catalog)" method="PUT" />
</x-layouts.admin>
