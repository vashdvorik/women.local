{{-- Остальные 4xx (405, 408…): общий текст «не получилось открыть страницу». --}}
@include('errors.layout', ['code' => (string) $exception->getStatusCode()])
