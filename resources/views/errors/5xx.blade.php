{{-- Остальные 5xx (502, 504…): как 500. --}}
@include('errors.layout', ['code' => (string) $exception->getStatusCode()])
