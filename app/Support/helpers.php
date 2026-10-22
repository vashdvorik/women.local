<?php

use App\Support\Assets;

if (! function_exists('asset_v')) {
    /** asset() с меткой версии файла (?v=время изменения): после правки файла у посетителей не остаётся старая копия. */
    function asset_v(string $path): string
    {
        return Assets::url($path);
    }
}
