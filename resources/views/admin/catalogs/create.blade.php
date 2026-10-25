<x-layouts.admin :title="__('Новый каталог')">
    <x-admin.catalog-form :catalog="$catalog" :action="route('admin.catalogs.store')" method="POST" />
</x-layouts.admin>
