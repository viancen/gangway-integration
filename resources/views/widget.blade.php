<div {{ ($attributes ?? new \Illuminate\View\ComponentAttributeBag())->merge(['data-gangway-token' => $token]) }}></div>
@once
    <script src="{{ $scriptUrl ?? \Gangway\Laravel\Facades\Gangway::widgetScriptUrl() }}" defer></script>
@endonce
